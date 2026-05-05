<?php

declare(strict_types=1);

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
define('ENVIRONMENT', 'development');

require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/Boot.php';

CodeIgniter\Boot::bootConsole($paths);

$db = db_connect();

$labRows = $db->table('laboratories')
    ->select('id, name, room, pic_name, pic_email')
    ->orderBy('id', 'ASC')
    ->get()
    ->getResultArray();

$assetRows = $db->table('assets')
    ->select('id, lab_id, name, asset_code, category, model, quantity, total_quantity, status')
    ->orderBy('id', 'ASC')
    ->get()
    ->getResultArray();

$identityRows = $db->table('auth_identities')
    ->select('user_id, secret')
    ->where('type', 'email_password')
    ->get()
    ->getResultArray();

$emailByUser = [];
foreach ($identityRows as $row) {
    $emailByUser[(int) $row['user_id']] = strtolower(trim((string) ($row['secret'] ?? '')));
}

$groupColumn = $db->fieldExists('group', 'auth_groups_users') ? 'group' : null;
$groupsByUser = [];

if ($groupColumn !== null) {
    $groupRows = $db->table('auth_groups_users')
        ->select('user_id, `group`')
        ->orderBy('user_id', 'ASC')
        ->get()
        ->getResultArray();

    foreach ($groupRows as $row) {
        $groupsByUser[(int) $row['user_id']][] = (string) $row['group'];
    }
} elseif ($db->fieldExists('group_id', 'auth_groups_users') && $db->tableExists('auth_groups')) {
    $groupRows = $db->table('auth_groups_users agu')
        ->select('agu.user_id, ag.name AS group_name')
        ->join('auth_groups ag', 'ag.id = agu.group_id', 'inner')
        ->orderBy('agu.user_id', 'ASC')
        ->get()
        ->getResultArray();

    foreach ($groupRows as $row) {
        $groupsByUser[(int) $row['user_id']][] = (string) $row['group_name'];
    }
}

$users = $db->table('users')
    ->select('id, username, full_name, active, deleted_at')
    ->where('deleted_at', null)
    ->orderBy('id', 'ASC')
    ->get()
    ->getResultArray();

$userRows = [];
foreach ($users as $user) {
    $userId = (int) $user['id'];
    $userRows[] = [
        'id' => $userId,
        'username' => $user['username'] ?? '',
        'full_name' => $user['full_name'] ?? '',
        'email' => $emailByUser[$userId] ?? '',
        'roles' => $groupsByUser[$userId] ?? [],
        'active' => (int) ($user['active'] ?? 0),
    ];
}

echo json_encode([
    'labs' => $labRows,
    'assets' => $assetRows,
    'users' => $userRows,
    'counts' => [
        'bookings' => $db->table('bookings')->countAllResults(),
        'maintenance_records' => $db->table('maintenance_records')->countAllResults(),
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
