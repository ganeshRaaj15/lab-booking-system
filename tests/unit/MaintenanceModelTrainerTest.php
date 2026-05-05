<?php

use App\Libraries\MaintenanceModelTrainer;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class MaintenanceModelTrainerTest extends CIUnitTestCase
{
    public function testTrainerLearnsSimpleSeparableMaintenancePattern(): void
    {
        $samples = [];

        for ($i = 0; $i < 20; $i++) {
            $highRisk = $i >= 10;
            $samples[] = [
                'anchor_date' => date('Y-m-d', strtotime('2025-01-01 +' . $i . ' days')),
                'label' => $highRisk ? 1 : 0,
                'features' => [
                    'days_since_last_event' => $highRisk ? 8.0 : 120.0,
                    'corrective_last_120d' => $highRisk ? 3.0 : 0.0,
                    'planned_gap_delta' => $highRisk ? 40.0 : 0.0,
                ],
            ];
        }

        $trainer = new MaintenanceModelTrainer();
        $model = $trainer->train($samples, 1800, 0.1, 0.0);

        $highProbability = $trainer->predictProbability([
            'days_since_last_event' => 7.0,
            'corrective_last_120d' => 2.0,
            'planned_gap_delta' => 45.0,
        ], $model);

        $lowProbability = $trainer->predictProbability([
            'days_since_last_event' => 160.0,
            'corrective_last_120d' => 0.0,
            'planned_gap_delta' => 0.0,
        ], $model);

        $this->assertGreaterThan(0.80, $highProbability);
        $this->assertLessThan(0.20, $lowProbability);
        $this->assertGreaterThan(0.80, $model['metrics']['test']['accuracy']);
    }
}
