<?php

declare(strict_types=1);

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
define('ENVIRONMENT', 'development');

require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/Boot.php';

CodeIgniter\Boot::bootConsole($paths);

$service = new App\Libraries\MaintenanceForecastService();
$forecasts = $service->getUpcomingForecasts(90);

foreach ($forecasts as $forecast) {
    echo json_encode([
        'asset_id' => $forecast['asset_id'] ?? null,
        'name' => $forecast['name'] ?? null,
        'risk_percent' => $forecast['risk_percent'] ?? null,
        'decision' => $forecast['decision_label'] ?? null,
        'priority' => $forecast['decision_priority'] ?? null,
        'next_due_at' => $forecast['next_due_at'] ?? null,
        'last_completed_at' => $forecast['last_completed_at'] ?? null,
        'reasons' => $forecast['reasons'] ?? [],
    ], JSON_UNESCAPED_SLASHES) . PHP_EOL;
}
