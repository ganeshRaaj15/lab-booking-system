<?php

declare(strict_types=1);

namespace App\Libraries;

use Config\Database;

class LabWorkRecommendationService
{
    protected \CodeIgniter\Database\BaseConnection $db;

    public function __construct(?\CodeIgniter\Database\BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    public function recommend(string $workDocumentText, ?string $sdsText, bool $chemicalsInvolved, int $limit = 3): array
    {
        $analysis = $this->analyzeDocuments($workDocumentText, $sdsText, $chemicalsInvolved);
        $catalog = $this->loadCatalog();
        $recommendations = [];

        foreach ($catalog as $lab) {
            $scored = $this->scoreLab($lab, $analysis, $chemicalsInvolved);
            if (($scored['score'] ?? 0) > 0) {
                $recommendations[] = $scored;
            }
        }

        usort($recommendations, static function (array $a, array $b): int {
            $scoreSort = (int) (($b['score'] ?? 0) <=> ($a['score'] ?? 0));
            if ($scoreSort !== 0) {
                return $scoreSort;
            }

            return strcmp((string) ($a['lab_name'] ?? ''), (string) ($b['lab_name'] ?? ''));
        });

        $recommendations = array_slice($recommendations, 0, max($limit, 1));

        if ($recommendations !== []) {
            $topScore = max((int) ($recommendations[0]['score'] ?? 0), 1);
            foreach ($recommendations as &$recommendation) {
                $recommendation['fit_score'] = $this->normalizeFitScore(
                    (int) ($recommendation['score'] ?? 0),
                    $topScore,
                    $chemicalsInvolved,
                    (bool) ($recommendation['chemical_ready'] ?? false)
                );
            }
            unset($recommendation);
        }

        return [
            'analysis' => $analysis,
            'recommendations' => $recommendations,
            'manual_review_required' => $recommendations === []
                || ((int) ($recommendations[0]['fit_score'] ?? 0) < 60)
                || ($chemicalsInvolved && ! (bool) ($recommendations[0]['chemical_ready'] ?? false)),
        ];
    }

    protected function analyzeDocuments(string $workDocumentText, ?string $sdsText, bool $chemicalsInvolved): array
    {
        $sections = $this->splitNumberedSections($workDocumentText);
        $equipmentText = $this->findSectionText($sections, ['equipment', 'materials', 'chemicals']);
        $hazardText = $this->findSectionText($sections, ['hazards', 'controls', 'ppe', 'emergency', 'waste']);
        $activityText = $this->findSectionText($sections, ['purpose', 'scope', 'procedure', 'activity', 'related booking']);

        $generalKeywords = $this->topTokens($workDocumentText, 20);
        $activityKeywords = $this->topTokens($activityText !== '' ? $activityText : $workDocumentText, 18);
        $equipmentKeywords = $this->topTokens($equipmentText, 18);
        $hazardKeywords = $this->topTokens(trim($hazardText . "\n" . ($sdsText ?? '')), 18);
        $chemicalKeywords = $chemicalsInvolved
            ? $this->topTokens(trim($equipmentText . "\n" . ($sdsText ?? '')), 18)
            : [];

        $hazardFlags = $this->detectHazardFlags(trim($workDocumentText . "\n" . ($sdsText ?? '')));
        $controlNeeds = $this->deriveControlNeeds($hazardFlags, $chemicalsInvolved);
        $parserNotes = [];

        if (mb_strlen($workDocumentText) < 200) {
            $parserNotes[] = 'The uploaded SOP/SWP contains limited extractable text. Add more procedure detail for stronger recommendations.';
        }

        if ($chemicalsInvolved && trim((string) $sdsText) === '') {
            $parserNotes[] = 'Chemicals were marked as involved, but the SDS upload produced little or no extractable text.';
        }

        if ($equipmentKeywords === []) {
            $parserNotes[] = 'No explicit equipment list was detected. Matching will rely more on the general activity description.';
        }

        return [
            'general_keywords' => $generalKeywords,
            'activity_keywords' => $activityKeywords,
            'equipment_keywords' => $equipmentKeywords,
            'hazard_keywords' => $hazardKeywords,
            'chemical_keywords' => $chemicalKeywords,
            'hazard_flags' => array_keys(array_filter($hazardFlags)),
            'control_needs' => $controlNeeds,
            'parser_notes' => $parserNotes,
            'section_titles' => array_keys($sections),
        ];
    }

    protected function loadCatalog(): array
    {
        $labs = $this->db->table('laboratories')
            ->select('id, name, room, description, capacity, availability_note, safety_note, image')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();

        $services = [];
        if ($this->db->tableExists('lab_services')) {
            $services = $this->db->table('lab_services ls')
                ->select("
                    ls.id,
                    ls.laboratory_id,
                    ls.field_name,
                    ls.service_name,
                    ls.acceptance_criteria,
                    ls.calibration_status,
                    ls.service_notes,
                    GROUP_CONCAT(
                        DISTINCT NULLIF(TRIM(sem.equipment_model), '')
                        ORDER BY sem.sort_order ASC
                        SEPARATOR ' | '
                    ) AS equipment_models
                ", false)
                ->join('service_equipment_models sem', 'sem.lab_service_id = ls.id', 'left')
                ->where('ls.is_active', 1)
                ->groupBy('ls.id')
                ->get()
                ->getResultArray();
        }

        $assets = $this->db->table('assets')
            ->select('id, lab_id, lab_service_id, name, category, brand, model, specifications, status, quantity, total_quantity')
            ->where('status !=', 'decommissioned')
            ->get()
            ->getResultArray();

        $catalog = [];
        foreach ($labs as $lab) {
            $catalog[(int) $lab['id']] = $lab + [
                'services' => [],
                'assets' => [],
            ];
        }

        foreach ($services as $service) {
            $labId = (int) ($service['laboratory_id'] ?? 0);
            if (isset($catalog[$labId])) {
                $catalog[$labId]['services'][] = $service;
            }
        }

        foreach ($assets as $asset) {
            $labId = (int) ($asset['lab_id'] ?? 0);
            if (isset($catalog[$labId])) {
                $catalog[$labId]['assets'][] = $asset;
            }
        }

        return array_values($catalog);
    }

    protected function scoreLab(array $lab, array $analysis, bool $chemicalsInvolved): array
    {
        $services = $lab['services'] ?? [];
        $assets = $lab['assets'] ?? [];

        $serviceText = [];
        $matchedServices = [];
        foreach ($services as $service) {
            $serviceBlob = implode(' ', array_filter([
                $service['service_name'] ?? '',
                $service['field_name'] ?? '',
                $service['acceptance_criteria'] ?? '',
                $service['service_notes'] ?? '',
                str_replace('|', ' ', (string) ($service['equipment_models'] ?? '')),
            ]));
            $serviceText[] = $serviceBlob;

            $serviceMatch = $this->collectMatches(
                array_merge($analysis['activity_keywords'], $analysis['equipment_keywords']),
                $serviceBlob,
                4,
                12
            );

            if (($serviceMatch['score'] ?? 0) > 0) {
                $matchedServices[] = [
                    'name' => (string) ($service['service_name'] ?? ''),
                    'matches' => $serviceMatch['matched'],
                    'score' => $serviceMatch['score'],
                ];
            }
        }

        usort($matchedServices, static fn(array $a, array $b): int => (int) (($b['score'] ?? 0) <=> ($a['score'] ?? 0)));
        $matchedServices = array_slice($matchedServices, 0, 3);

        $assetText = [];
        $matchedAssets = [];
        $availableAssets = 0;
        foreach ($assets as $asset) {
            $assetBlob = implode(' ', array_filter([
                $asset['name'] ?? '',
                $asset['category'] ?? '',
                $asset['brand'] ?? '',
                $asset['model'] ?? '',
                $asset['specifications'] ?? '',
            ]));
            $assetText[] = $assetBlob;

            if (($asset['status'] ?? '') === 'available' && (int) ($asset['quantity'] ?? 0) > 0) {
                $availableAssets++;
            }

            $assetMatch = $this->collectMatches(
                array_merge($analysis['equipment_keywords'], $analysis['activity_keywords']),
                $assetBlob,
                3,
                9
            );

            if (($assetMatch['score'] ?? 0) > 0) {
                $matchedAssets[] = [
                    'name' => (string) ($asset['name'] ?? ''),
                    'status' => (string) ($asset['status'] ?? 'unknown'),
                    'matches' => $assetMatch['matched'],
                    'score' => $assetMatch['score'],
                ];
            }
        }

        usort($matchedAssets, static fn(array $a, array $b): int => (int) (($b['score'] ?? 0) <=> ($a['score'] ?? 0)));
        $matchedAssets = array_slice($matchedAssets, 0, 5);

        $serviceCorpus = implode(' ', $serviceText);
        $assetCorpus = implode(' ', $assetText);
        $labCorpus = implode(' ', array_filter([
            $lab['name'] ?? '',
            $lab['room'] ?? '',
            $lab['description'] ?? '',
            $lab['availability_note'] ?? '',
            $lab['safety_note'] ?? '',
        ]));
        $safetyCorpus = implode(' ', array_filter([
            $lab['safety_note'] ?? '',
            $lab['availability_note'] ?? '',
            $serviceCorpus,
        ]));

        $serviceScore = $this->collectMatches($analysis['activity_keywords'], $serviceCorpus, 3, 28);
        $equipmentScore = $this->collectMatches($analysis['equipment_keywords'], $assetCorpus . ' ' . $serviceCorpus, 3, 24);
        $generalScore = $this->collectMatches($analysis['general_keywords'], $labCorpus . ' ' . $serviceCorpus . ' ' . $assetCorpus, 1, 18);
        $safetyScore = $chemicalsInvolved
            ? $this->collectMatches(
                array_merge($analysis['control_needs'], $analysis['chemical_keywords'], $analysis['hazard_keywords']),
                $safetyCorpus,
                2,
                18
            )
            : ['score' => 0, 'matched' => []];

        $readinessScore = min(12, ($availableAssets * 2) + count($services));
        $chemicalReady = ! $chemicalsInvolved
            || $this->looksChemicalReady($lab, $services, $assets, $safetyScore['matched']);

        $penalty = 0;
        if ($chemicalsInvolved && ! $chemicalReady) {
            $penalty -= 10;
        }
        if ($matchedServices === [] && $matchedAssets === []) {
            $penalty -= 6;
        }

        $score = max(
            0,
            (int) $serviceScore['score']
            + (int) $equipmentScore['score']
            + (int) $generalScore['score']
            + (int) $safetyScore['score']
            + $readinessScore
            + $penalty
        );

        $reasons = [];
        if ($matchedServices !== []) {
            $reasons[] = 'Matched service areas: ' . implode(', ', array_column($matchedServices, 'name'));
        }
        if ($matchedAssets !== []) {
            $reasons[] = 'Relevant equipment found: ' . implode(', ', array_column($matchedAssets, 'name'));
        }
        if ($chemicalsInvolved && $safetyScore['matched'] !== []) {
            $reasons[] = 'Safety and chemical-control terms matched: ' . implode(', ', array_slice($safetyScore['matched'], 0, 6));
        }
        if ($availableAssets > 0) {
            $reasons[] = $availableAssets . ' currently available asset record(s) support this lab.';
        }
        if ($chemicalsInvolved && ! $chemicalReady) {
            $reasons[] = 'Chemical controls were not strongly evidenced in the current lab metadata, so manual review is recommended.';
        }

        return [
            'lab_id' => (int) ($lab['id'] ?? 0),
            'lab_name' => (string) ($lab['name'] ?? ''),
            'room' => (string) ($lab['room'] ?? ''),
            'image' => (string) ($lab['image'] ?? ''),
            'score' => $score,
            'chemical_ready' => $chemicalReady,
            'matched_services' => array_map(static fn(array $row): string => (string) ($row['name'] ?? ''), $matchedServices),
            'matched_assets' => array_map(static fn(array $row): string => (string) ($row['name'] ?? ''), $matchedAssets),
            'available_assets' => $availableAssets,
            'total_assets' => count($assets),
            'service_count' => count($services),
            'reasons' => $reasons,
            'safety_excerpt' => $this->excerpt((string) ($lab['safety_note'] ?? '')),
            'description_excerpt' => $this->excerpt((string) ($lab['description'] ?? '')),
        ];
    }

    protected function splitNumberedSections(string $text): array
    {
        $sections = [];
        $current = '__body';

        foreach (preg_split('/\R/u', $text) ?: [] as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            if (preg_match('/^\d{1,2}\.\s+(.+)$/u', $trimmed, $matches) === 1) {
                $current = strtolower(trim($matches[1]));
                $sections[$current] = [];
                continue;
            }

            $sections[$current][] = $trimmed;
        }

        foreach ($sections as $title => $lines) {
            $sections[$title] = trim(implode("\n", $lines));
        }

        return $sections;
    }

    protected function findSectionText(array $sections, array $needles): string
    {
        $collected = [];
        foreach ($sections as $title => $text) {
            foreach ($needles as $needle) {
                if (str_contains($title, $needle)) {
                    $collected[] = $text;
                    break;
                }
            }
        }

        return trim(implode("\n", $collected));
    }

    protected function topTokens(string $text, int $limit): array
    {
        $tokens = $this->tokenize($text);
        if ($tokens === []) {
            return [];
        }

        $counts = array_count_values($tokens);
        arsort($counts);

        return array_slice(array_keys($counts), 0, $limit);
    }

    protected function tokenize(string $text): array
    {
        $text = strtolower($text);
        preg_match_all('/[a-z0-9][a-z0-9+\-\/.#]{1,}/u', $text, $matches);

        $stopwords = [
            'the', 'and', 'for', 'with', 'that', 'this', 'from', 'into', 'your', 'work', 'will', 'shall',
            'must', 'have', 'has', 'using', 'used', 'user', 'users', 'laboratory', 'procedure', 'document',
            'required', 'please', 'before', 'after', 'only', 'when', 'where', 'than', 'need', 'needs',
            'line', 'name', 'date', 'type', 'item', 'list', 'note', 'notes', 'step', 'steps', 'safe',
            'safety', 'standard', 'operating', 'workplace', 'equipment', 'materials', 'chemical', 'chemicals',
            'section', 'purpose', 'scope', 'related', 'booking', 'activity', 'control', 'controls',
        ];
        $stopwordMap = array_fill_keys($stopwords, true);

        $tokens = [];
        foreach ($matches[0] ?? [] as $token) {
            $token = trim((string) $token, ".-/#");
            if ($token === '' || strlen($token) < 2 || isset($stopwordMap[$token])) {
                continue;
            }

            $tokens[] = $token;
        }

        return $tokens;
    }

    protected function detectHazardFlags(string $text): array
    {
        $text = strtolower($text);

        $flags = [
            'flammable' => ['flammable', 'solvent', 'ethanol', 'methanol', 'acetone', 'toluene', 'hexane', 'ignition'],
            'corrosive' => ['corrosive', 'acid', 'alkali', 'caustic', 'naoh', 'hcl', 'h2so4', 'nitric'],
            'toxic' => ['toxic', 'poison', 'benzene', 'chloroform', 'formaldehyde', 'cyanide', 'hazardous vapor'],
            'gas' => ['compressed gas', 'gas cylinder', 'argon', 'nitrogen', 'co2', 'hydrogen', 'oxygen'],
            'heat' => ['furnace', 'oven', 'hot plate', 'high temperature', 'thermal', 'heat'],
            'mechanical' => ['tensile', 'compression', 'impact', 'hardness', 'machine', 'rotating', 'cutting'],
            'electrical' => ['electrical', 'voltage', 'power supply', 'circuit', 'pcb', 'oscilloscope'],
            'imaging' => ['microscope', 'imaging', 'camera', 'optical'],
        ];

        $results = [];
        foreach ($flags as $flag => $needles) {
            $results[$flag] = false;
            foreach ($needles as $needle) {
                if (str_contains($text, $needle)) {
                    $results[$flag] = true;
                    break;
                }
            }
        }

        return $results;
    }

    protected function deriveControlNeeds(array $hazardFlags, bool $chemicalsInvolved): array
    {
        $needs = [];

        if ($chemicalsInvolved || ! empty($hazardFlags['flammable']) || ! empty($hazardFlags['corrosive']) || ! empty($hazardFlags['toxic'])) {
            $needs = array_merge($needs, ['chemical', 'ventilation', 'fume hood', 'spill', 'eyewash', 'ppe']);
        }

        if (! empty($hazardFlags['gas'])) {
            $needs = array_merge($needs, ['gas', 'cylinder', 'ventilation', 'regulator']);
        }

        if (! empty($hazardFlags['heat'])) {
            $needs = array_merge($needs, ['heat', 'thermal', 'oven', 'furnace', 'gloves']);
        }

        if (! empty($hazardFlags['mechanical'])) {
            $needs = array_merge($needs, ['testing', 'guard', 'fixture', 'machine']);
        }

        if (! empty($hazardFlags['electrical'])) {
            $needs = array_merge($needs, ['electrical', 'power', 'bench']);
        }

        if (! empty($hazardFlags['imaging'])) {
            $needs = array_merge($needs, ['microscope', 'optical', 'imaging']);
        }

        return array_values(array_unique($needs));
    }

    protected function collectMatches(array $needles, string $haystack, int $perMatch, int $cap): array
    {
        $haystack = strtolower($haystack);
        $score = 0;
        $matched = [];

        foreach (array_values(array_unique($needles)) as $needle) {
            $needle = strtolower(trim((string) $needle));
            if ($needle === '' || strlen($needle) < 2) {
                continue;
            }

            if (str_contains($haystack, $needle)) {
                $matched[] = $needle;
                $score += $perMatch;
                if ($score >= $cap) {
                    $score = $cap;
                    break;
                }
            }
        }

        return [
            'score' => $score,
            'matched' => $matched,
        ];
    }

    protected function looksChemicalReady(array $lab, array $services, array $assets, array $matchedSafetyTerms): bool
    {
        if ($matchedSafetyTerms !== []) {
            return true;
        }

        $safetyText = strtolower(trim((string) ($lab['safety_note'] ?? '')));
        foreach (['chemical', 'fume hood', 'ventilation', 'spill', 'sds', 'eyewash', 'corrosive', 'flammable'] as $needle) {
            if ($needle !== '' && str_contains($safetyText, $needle)) {
                return true;
            }
        }

        foreach ($services as $service) {
            $blob = strtolower(implode(' ', array_filter([
                $service['service_name'] ?? '',
                $service['field_name'] ?? '',
                $service['acceptance_criteria'] ?? '',
                $service['service_notes'] ?? '',
            ])));

            foreach (['chemical', 'analysis', 'testing', 'ventilation', 'fume hood'] as $needle) {
                if ($needle !== '' && str_contains($blob, $needle)) {
                    return true;
                }
            }
        }

        foreach ($assets as $asset) {
            $blob = strtolower(implode(' ', array_filter([
                $asset['name'] ?? '',
                $asset['category'] ?? '',
                $asset['model'] ?? '',
                $asset['specifications'] ?? '',
            ])));

            foreach (['hood', 'ventilation', 'gas', 'furnace', 'microscope', 'analyzer', 'testing'] as $needle) {
                if ($needle !== '' && str_contains($blob, $needle)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function normalizeFitScore(int $score, int $topScore, bool $chemicalsInvolved, bool $chemicalReady): int
    {
        if ($score <= 0) {
            return 0;
        }

        $relative = $topScore > 0 ? ($score / $topScore) : 0.0;
        $fit = (int) round(($score * 0.65) + ($relative * 35));
        $fit = max(25, min(97, $fit));

        if ($chemicalsInvolved && ! $chemicalReady) {
            $fit = max(20, $fit - 12);
        }

        return $fit;
    }

    protected function excerpt(string $text, int $length = 160): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
        if ($text === '' || mb_strlen($text) <= $length) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, $length - 1)) . '…';
    }
}
