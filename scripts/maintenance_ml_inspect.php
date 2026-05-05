<?php

declare(strict_types=1);

$mysqli = new mysqli('localhost', 'root', '', 'slams_db', 3306);

if ($mysqli->connect_errno) {
    fwrite(STDERR, 'CONNECT_ERROR: ' . $mysqli->connect_error . PHP_EOL);
    exit(1);
}

$queries = [
    'assets' => 'SELECT COUNT(*) AS c FROM assets',
    'labs' => 'SELECT COUNT(*) AS c FROM laboratories',
    'maintenance_records' => 'SELECT COUNT(*) AS c FROM maintenance_records',
    'completed_preventive' => "SELECT COUNT(*) AS c FROM maintenance_records WHERE status='completed' AND issue_type IN ('preventive','inspection','calibration')",
    'corrective' => "SELECT COUNT(*) AS c FROM maintenance_records WHERE issue_type='corrective'",
    'users' => 'SELECT COUNT(*) AS c FROM users',
    'bookings' => 'SELECT COUNT(*) AS c FROM bookings',
];

foreach ($queries as $label => $sql) {
    $result = $mysqli->query($sql);
    if (! $result) {
        echo $label . ': ERROR ' . $mysqli->error . PHP_EOL;
        continue;
    }

    $row = $result->fetch_assoc();
    echo $label . ': ' . ($row['c'] ?? '0') . PHP_EOL;
}

echo PHP_EOL . 'ASSET_SAMPLE' . PHP_EOL;
$result = $mysqli->query(
    "SELECT a.id, a.name, a.category, a.brand, a.model, a.purchase_date, a.quantity, a.total_quantity, a.status, l.name AS lab_name
     FROM assets a
     LEFT JOIN laboratories l ON l.id = a.lab_id
     ORDER BY a.id ASC
     LIMIT 15"
);

while ($row = $result?->fetch_assoc()) {
    echo json_encode($row, JSON_UNESCAPED_SLASHES) . PHP_EOL;
}

echo PHP_EOL . 'MAINTENANCE_SAMPLE' . PHP_EOL;
$result = $mysqli->query(
    "SELECT id, asset_id, issue_type, priority, status, quantity_affected, created_at, scheduled_for, completed_at
     FROM maintenance_records
     ORDER BY id DESC
     LIMIT 20"
);

while ($row = $result?->fetch_assoc()) {
    echo json_encode($row, JSON_UNESCAPED_SLASHES) . PHP_EOL;
}
