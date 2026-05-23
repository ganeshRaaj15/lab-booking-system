<?php

declare(strict_types=1);

namespace App\Libraries;

class MaintenanceModelTrainer
{
    public const MINIMUM_SAMPLES = 8;
    protected const MINIMUM_SEGMENT_SAMPLES = 8;

    public function minimumSamples(): int
    {
        return self::MINIMUM_SAMPLES;
    }

    public function train(array $samples, int $iterations = 2500, float $learningRate = 0.08, float $l2 = 0.01): array
    {
        if (count($samples) < self::MINIMUM_SAMPLES) {
            throw new \RuntimeException('At least ' . self::MINIMUM_SAMPLES . ' training samples are required to train the maintenance model.');
        }

        usort($samples, static fn(array $a, array $b): int => strcmp((string) $a['anchor_date'], (string) $b['anchor_date']));

        $featureNames = array_keys($samples[0]['features'] ?? []);
        if ($featureNames === []) {
            throw new \RuntimeException('Training samples do not contain feature columns.');
        }

        [$fitSamples, $calibrationSamples, $validationSamples, $testSamples] = $this->splitSamples($samples);

        [$means, $stds] = $this->fitScaler($fitSamples, $featureNames);
        $weights = array_fill_keys($featureNames, 0.0);
        $bias = 0.0;

        [$positiveWeight, $negativeWeight] = $this->classWeights($fitSamples);

        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            $weightGradients = array_fill_keys($featureNames, 0.0);
            $biasGradient = 0.0;

            foreach ($fitSamples as $sample) {
                $vector = $this->standardizeVector($sample['features'], $featureNames, $means, $stds);
                $label = (float) ($sample['label'] ?? 0);
                $probability = $this->sigmoid($this->linearScore($vector, $weights, $bias));
                $sampleWeight = $label >= 0.5 ? $positiveWeight : $negativeWeight;
                $error = ($probability - $label) * $sampleWeight;

                foreach ($featureNames as $name) {
                    $weightGradients[$name] += $error * $vector[$name];
                }

                $biasGradient += $error;
            }

            $count = max(count($fitSamples), 1);
            foreach ($featureNames as $name) {
                $gradient = ($weightGradients[$name] / $count) + ($l2 * $weights[$name]);
                $weights[$name] -= $learningRate * $gradient;
            }

            $bias -= $learningRate * ($biasGradient / $count);
        }

        $calibrationRawProbabilities = $this->predictProbabilities($calibrationSamples, $featureNames, $weights, $bias, $means, $stds);
        $calibration = $this->fitCalibration($calibrationRawProbabilities, array_column($calibrationSamples, 'label'));
        $validationRawProbabilities = $this->predictProbabilities($validationSamples, $featureNames, $weights, $bias, $means, $stds);
        $validationProbabilities = $this->applyCalibrationToProbabilities($validationRawProbabilities, $calibration);
        $thresholdPolicy = $this->learnThresholdPolicy($validationSamples, $validationProbabilities);
        $threshold = (float) ($thresholdPolicy['default_threshold'] ?? 0.5);

        $fitProbabilities = $this->applyCalibrationToProbabilities(
            $this->predictProbabilities($fitSamples, $featureNames, $weights, $bias, $means, $stds),
            $calibration
        );
        $calibratedCalibrationProbabilities = $this->applyCalibrationToProbabilities($calibrationRawProbabilities, $calibration);
        $testProbabilities = $this->applyCalibrationToProbabilities(
            $this->predictProbabilities($testSamples, $featureNames, $weights, $bias, $means, $stds),
            $calibration
        );

        return [
            'version' => 2,
            'trained_at' => date('c'),
            'feature_names' => $featureNames,
            'means' => $means,
            'stds' => $stds,
            'weights' => $weights,
            'bias' => $bias,
            'threshold' => $threshold,
            'calibration' => $calibration,
            'threshold_policy' => $thresholdPolicy,
            'metrics' => [
                'fit' => $this->evaluateWithPolicy($fitSamples, $fitProbabilities, $thresholdPolicy),
                'calibration' => $this->evaluateWithPolicy($calibrationSamples, $calibratedCalibrationProbabilities, $thresholdPolicy),
                'validation' => $this->evaluateWithPolicy($validationSamples, $validationProbabilities, $thresholdPolicy),
                'test' => $this->evaluateWithPolicy($testSamples, $testProbabilities, $thresholdPolicy),
                'test_default_threshold' => $this->evaluate($testProbabilities, array_column($testSamples, 'label'), $threshold),
            ],
            'dataset' => [
                'samples_total' => count($samples),
                'samples_train' => count($fitSamples),
                'samples_calibration' => count($calibrationSamples),
                'samples_validation' => count($validationSamples),
                'samples_test' => count($testSamples),
                'positive_train' => array_sum(array_map(static fn(array $sample): int => (int) ($sample['label'] ?? 0), $fitSamples)),
                'positive_calibration' => array_sum(array_map(static fn(array $sample): int => (int) ($sample['label'] ?? 0), $calibrationSamples)),
                'positive_validation' => array_sum(array_map(static fn(array $sample): int => (int) ($sample['label'] ?? 0), $validationSamples)),
                'positive_test' => array_sum(array_map(static fn(array $sample): int => (int) ($sample['label'] ?? 0), $testSamples)),
            ],
        ];
    }

    public function predictRawProbability(array $features, array $model): float
    {
        $featureNames = $model['feature_names'] ?? [];
        $weights = $model['weights'] ?? [];
        $means = $model['means'] ?? [];
        $stds = $model['stds'] ?? [];
        $bias = (float) ($model['bias'] ?? 0.0);
        $vector = $this->standardizeVector($features, $featureNames, $means, $stds);

        return $this->sigmoid($this->linearScore($vector, $weights, $bias));
    }

    public function predictProbability(array $features, array $model): float
    {
        return $this->applyCalibration(
            $this->predictRawProbability($features, $model),
            $model['calibration'] ?? []
        );
    }

    public function resolveThreshold(array $features, array $model): float
    {
        $policy = $model['threshold_policy'] ?? [];
        if (! is_array($policy) || $policy === []) {
            return (float) ($model['threshold'] ?? 0.5);
        }

        $segment = $this->thresholdSegment($features, $policy);

        return (float) (($policy['segments'][$segment]['threshold'] ?? null)
            ?? ($policy['default_threshold'] ?? $model['threshold'] ?? 0.5));
    }

    public function thresholdSegment(array $features, array $policyOrModel): string
    {
        $policy = $policyOrModel['segments'] ?? null;
        if ($policy === null && isset($policyOrModel['threshold_policy'])) {
            $policy = $policyOrModel['threshold_policy']['segments'] ?? null;
            $policyOrModel = $policyOrModel['threshold_policy'] ?? [];
        }

        $cuts = $policyOrModel['segment_cuts'] ?? [];
        $depthCut = (float) ($cuts['history_depth_score'] ?? 0.35);
        $bookingCut = (float) ($cuts['booking_count_90d'] ?? 2.0);
        $eventCut = (float) ($cuts['events_last_365d'] ?? 2.0);

        $categoryBucket = ((float) ($features['is_high_maintenance_category'] ?? 0.0)) >= 0.5 ? 'high_maint' : 'standard';
        $depthBucket = ((float) ($features['history_depth_score'] ?? 0.0)) >= $depthCut ? 'established' : 'sparse';
        $usageBucket = (
            (float) ($features['booking_count_90d'] ?? 0.0) >= $bookingCut
            || (float) ($features['events_last_365d'] ?? 0.0) >= $eventCut
        ) ? 'active' : 'steady';

        return implode('|', [$categoryBucket, $depthBucket, $usageBucket]);
    }

    public function featureContributions(array $features, array $model): array
    {
        $featureNames = $model['feature_names'] ?? [];
        $weights = $model['weights'] ?? [];
        $means = $model['means'] ?? [];
        $stds = $model['stds'] ?? [];
        $vector = $this->standardizeVector($features, $featureNames, $means, $stds);
        $contributions = [];

        foreach ($featureNames as $name) {
            $contributions[$name] = ($vector[$name] ?? 0.0) * (float) ($weights[$name] ?? 0.0);
        }

        arsort($contributions);

        return $contributions;
    }

    protected function splitSamples(array $samples): array
    {
        $total = count($samples);
        $fitEnd = max((int) floor($total * 0.6), 1);
        $calibrationEnd = max((int) floor($total * 0.75), $fitEnd + 1);
        $validationEnd = max((int) floor($total * 0.85), $calibrationEnd + 1);

        if ($validationEnd >= $total) {
            $validationEnd = $total - 1;
        }
        if ($calibrationEnd >= $validationEnd) {
            $calibrationEnd = max($fitEnd + 1, $validationEnd - 1);
        }
        if ($fitEnd >= $calibrationEnd) {
            $fitEnd = max(1, $calibrationEnd - 1);
        }

        return [
            array_slice($samples, 0, $fitEnd),
            array_slice($samples, $fitEnd, $calibrationEnd - $fitEnd),
            array_slice($samples, $calibrationEnd, $validationEnd - $calibrationEnd),
            array_slice($samples, $validationEnd),
        ];
    }

    protected function fitScaler(array $samples, array $featureNames): array
    {
        $means = array_fill_keys($featureNames, 0.0);
        $stds = array_fill_keys($featureNames, 1.0);
        $count = max(count($samples), 1);

        foreach ($featureNames as $name) {
            $sum = 0.0;
            foreach ($samples as $sample) {
                $sum += (float) ($sample['features'][$name] ?? 0.0);
            }
            $means[$name] = $sum / $count;
        }

        foreach ($featureNames as $name) {
            $variance = 0.0;
            foreach ($samples as $sample) {
                $value = (float) ($sample['features'][$name] ?? 0.0);
                $variance += ($value - $means[$name]) ** 2;
            }
            $std = sqrt($variance / $count);
            $stds[$name] = $std > 1.0e-6 ? $std : 1.0;
        }

        return [$means, $stds];
    }

    protected function classWeights(array $samples): array
    {
        $positive = 0;
        foreach ($samples as $sample) {
            $positive += (int) ($sample['label'] ?? 0);
        }

        $total = max(count($samples), 1);
        $negative = max($total - $positive, 1);
        $positive = max($positive, 1);

        return [
            $total / (2 * $positive),
            $total / (2 * $negative),
        ];
    }

    protected function standardizeVector(array $features, array $featureNames, array $means, array $stds): array
    {
        $vector = [];

        foreach ($featureNames as $name) {
            $value = (float) ($features[$name] ?? 0.0);
            $vector[$name] = ($value - (float) ($means[$name] ?? 0.0)) / (float) ($stds[$name] ?? 1.0);
        }

        return $vector;
    }

    protected function linearScore(array $vector, array $weights, float $bias): float
    {
        $score = $bias;

        foreach ($vector as $name => $value) {
            $score += $value * (float) ($weights[$name] ?? 0.0);
        }

        return $score;
    }

    protected function predictProbabilities(array $samples, array $featureNames, array $weights, float $bias, array $means, array $stds): array
    {
        $probabilities = [];

        foreach ($samples as $sample) {
            $vector = $this->standardizeVector($sample['features'], $featureNames, $means, $stds);
            $probabilities[] = $this->sigmoid($this->linearScore($vector, $weights, $bias));
        }

        return $probabilities;
    }

    protected function fitCalibration(array $probabilities, array $labels, int $iterations = 1200, float $learningRate = 0.25): array
    {
        if ($probabilities === [] || count(array_unique($labels)) < 2) {
            return ['enabled' => false, 'scale' => 1.0, 'bias' => 0.0];
        }

        $scale = 1.0;
        $bias = 0.0;
        $count = max(count($probabilities), 1);

        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            $scaleGradient = 0.0;
            $biasGradient = 0.0;

            foreach ($probabilities as $index => $probability) {
                $feature = $this->safeLogit((float) $probability);
                $predicted = $this->sigmoid(($feature * $scale) + $bias);
                $error = $predicted - (float) ($labels[$index] ?? 0);
                $scaleGradient += $error * $feature;
                $biasGradient += $error;
            }

            $scale -= $learningRate * ($scaleGradient / $count);
            $bias -= $learningRate * ($biasGradient / $count);
        }

        return ['enabled' => true, 'scale' => $scale, 'bias' => $bias];
    }

    protected function applyCalibration(float $probability, array $calibration): float
    {
        if (($calibration['enabled'] ?? false) !== true) {
            return $probability;
        }

        $feature = $this->safeLogit($probability);

        return $this->sigmoid(($feature * (float) ($calibration['scale'] ?? 1.0)) + (float) ($calibration['bias'] ?? 0.0));
    }

    protected function applyCalibrationToProbabilities(array $probabilities, array $calibration): array
    {
        return array_map(fn(float $probability): float => $this->applyCalibration($probability, $calibration), $probabilities);
    }

    protected function learnThresholdPolicy(array $samples, array $probabilities): array
    {
        $defaultThreshold = $this->bestThreshold($probabilities, array_column($samples, 'label'));
        $cuts = $this->segmentCuts($samples);
        $segments = [];
        $grouped = [];

        foreach ($samples as $index => $sample) {
            $segment = $this->thresholdSegment($sample['features'] ?? [], ['segment_cuts' => $cuts]);
            $grouped[$segment][] = [
                'probability' => (float) ($probabilities[$index] ?? 0.0),
                'label' => (int) ($sample['label'] ?? 0),
            ];
        }

        foreach ($grouped as $segment => $rows) {
            $labels = array_column($rows, 'label');
            if (count($rows) < self::MINIMUM_SEGMENT_SAMPLES || count(array_unique($labels)) < 2) {
                continue;
            }

            $segmentThreshold = $this->bestThreshold(array_column($rows, 'probability'), $labels);
            $segments[$segment] = [
                'threshold' => $segmentThreshold,
                'samples' => count($rows),
                'positive_rate' => array_sum($labels) / max(count($labels), 1),
            ];
        }

        return [
            'default_threshold' => $defaultThreshold,
            'segment_cuts' => $cuts,
            'segments' => $segments,
        ];
    }

    protected function segmentCuts(array $samples): array
    {
        return [
            'history_depth_score' => $this->quantile(array_map(
                static fn(array $sample): float => (float) ($sample['features']['history_depth_score'] ?? 0.0),
                $samples
            ), 0.45, 0.35),
            'booking_count_90d' => max(1.0, $this->quantile(array_map(
                static fn(array $sample): float => (float) ($sample['features']['booking_count_90d'] ?? 0.0),
                $samples
            ), 0.65, 2.0)),
            'events_last_365d' => max(1.0, $this->quantile(array_map(
                static fn(array $sample): float => (float) ($sample['features']['events_last_365d'] ?? 0.0),
                $samples
            ), 0.65, 2.0)),
        ];
    }

    protected function bestThreshold(array $probabilities, array $labels): float
    {
        $bestThreshold = 0.5;
        $bestF1 = -1.0;

        for ($threshold = 0.35; $threshold <= 0.85; $threshold += 0.02) {
            $metrics = $this->evaluate($probabilities, $labels, $threshold);
            if (($metrics['f1'] ?? 0.0) > $bestF1) {
                $bestF1 = (float) ($metrics['f1'] ?? 0.0);
                $bestThreshold = round($threshold, 2);
            }
        }

        return $bestThreshold;
    }

    protected function evaluateWithPolicy(array $samples, array $probabilities, array $policy): array
    {
        $tp = 0;
        $tn = 0;
        $fp = 0;
        $fn = 0;

        foreach ($samples as $index => $sample) {
            $threshold = $this->resolveThreshold($sample['features'] ?? [], ['threshold_policy' => $policy]);
            $probability = (float) ($probabilities[$index] ?? 0.0);
            $actual = (int) ($sample['label'] ?? 0);
            $predicted = $probability >= $threshold ? 1 : 0;

            if ($predicted === 1 && $actual === 1) {
                $tp++;
            } elseif ($predicted === 0 && $actual === 0) {
                $tn++;
            } elseif ($predicted === 1) {
                $fp++;
            } else {
                $fn++;
            }
        }

        $total = max($tp + $tn + $fp + $fn, 1);
        $precision = $tp + $fp > 0 ? $tp / ($tp + $fp) : 0.0;
        $recall = $tp + $fn > 0 ? $tp / ($tp + $fn) : 0.0;
        $f1 = ($precision + $recall) > 0 ? (2 * $precision * $recall) / ($precision + $recall) : 0.0;

        return [
            'accuracy' => ($tp + $tn) / $total,
            'precision' => $precision,
            'recall' => $recall,
            'f1' => $f1,
            'threshold' => (float) ($policy['default_threshold'] ?? 0.5),
            'confusion' => ['tp' => $tp, 'tn' => $tn, 'fp' => $fp, 'fn' => $fn],
        ];
    }

    protected function evaluate(array $probabilities, array $labels, float $threshold): array
    {
        $tp = 0;
        $tn = 0;
        $fp = 0;
        $fn = 0;

        foreach ($probabilities as $index => $probability) {
            $actual = (int) ($labels[$index] ?? 0);
            $predicted = $probability >= $threshold ? 1 : 0;

            if ($predicted === 1 && $actual === 1) {
                $tp++;
            } elseif ($predicted === 0 && $actual === 0) {
                $tn++;
            } elseif ($predicted === 1) {
                $fp++;
            } else {
                $fn++;
            }
        }

        $total = max($tp + $tn + $fp + $fn, 1);
        $precision = $tp + $fp > 0 ? $tp / ($tp + $fp) : 0.0;
        $recall = $tp + $fn > 0 ? $tp / ($tp + $fn) : 0.0;
        $f1 = ($precision + $recall) > 0 ? (2 * $precision * $recall) / ($precision + $recall) : 0.0;

        return [
            'accuracy' => ($tp + $tn) / $total,
            'precision' => $precision,
            'recall' => $recall,
            'f1' => $f1,
            'threshold' => $threshold,
            'confusion' => ['tp' => $tp, 'tn' => $tn, 'fp' => $fp, 'fn' => $fn],
        ];
    }

    protected function sigmoid(float $value): float
    {
        if ($value < -35.0) {
            return 0.0;
        }
        if ($value > 35.0) {
            return 1.0;
        }

        return 1.0 / (1.0 + exp(-1.0 * $value));
    }

    protected function safeLogit(float $probability): float
    {
        $probability = min(max($probability, 1.0e-6), 1.0 - 1.0e-6);

        return log($probability / (1.0 - $probability));
    }

    protected function quantile(array $values, float $percentile, float $default): float
    {
        $values = array_values(array_filter($values, static fn($value): bool => is_numeric($value)));
        if ($values === []) {
            return $default;
        }

        sort($values, SORT_NUMERIC);
        $index = (count($values) - 1) * min(max($percentile, 0.0), 1.0);
        $lower = (int) floor($index);
        $upper = (int) ceil($index);

        if ($lower === $upper) {
            return (float) $values[$lower];
        }

        $weight = $index - $lower;

        return ((float) $values[$lower] * (1.0 - $weight)) + ((float) $values[$upper] * $weight);
    }
}
