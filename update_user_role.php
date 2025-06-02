<?php
session_start();
require_once 'classes/DB.php';
require_once 'classes/Auth.php';

$db = DB::getInstance()->getConnection();
$auth = new Auth($db);

if (!$auth->isLoggedIn() || $auth->currentUser()['role'] !== 'admin') {
    die("Zugriff verweigert.");
}

$userId = intval($_POST['user_id']);
$newRole = $_POST['role'];

$validRoles = ['user', 'support', 'admin'];
if (!in_array($newRole, $validRoles)) {
    die("Ungültige Rolle.");
}

// Eigene Rolle darf nicht geändert werden
if ($userId === $auth->currentUser()['id']) {
    die("Du kannst deine eigene Rolle nicht ändern.");
}

$stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
$stmt->execute([$newRole, $userId]);

header('Location: admin_users.php');
