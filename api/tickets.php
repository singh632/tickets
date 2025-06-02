<?php
// Analog zu auth.php anpassen
require_once __DIR__ . '/../classes/DB.php';
require_once __DIR__ . '/../classes/Auth.php';

session_start();
header('Content-Type: application/json');

$db = DB::getInstance()->getConnection();
$auth = new Auth($db);

if (!$auth->isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Nicht eingeloggt']);
    exit;
}

$user = $auth->currentUser();
$input = json_decode(file_get_contents("php://input"), true);

// Validierung
if (empty($input['title']) || empty($input['description']) || empty($input['priority'])) {
    echo json_encode(['status' => 'error', 'message' => 'Pflichtfelder fehlen']);
    exit;
}

$stmt = $db->prepare("
    INSERT INTO tickets (title, description, priority, created_by, category_id, created_at, status)
    VALUES (?, ?, ?, ?, ?, NOW(), 'neu')
");

$stmt->execute([
    $input['title'],
    $input['description'],
    $input['priority'],
    $user['id'],
    $input['category_id'] ?? null
]);

echo json_encode(['status' => 'success']);
