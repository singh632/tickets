<?php
session_start();
require_once 'classes/DB.php';
require_once 'classes/Auth.php';

$db = DB::getInstance()->getConnection();
$auth = new Auth($db);

if (!$auth->isLoggedIn()) {
    header('Location: login.html');
    exit;
}

$user = $auth->currentUser();

if (!in_array($user['role'], ['admin', 'support'])) {
    die("Keine Berechtigung.");
}

$ticketId = $_POST['ticket_id'] ?? 0;
$status = $_POST['status'] ?? 'neu';

$stmt = $db->prepare("UPDATE tickets SET status = ? WHERE id = ?");
$stmt->execute([$status, $ticketId]);

header("Location: ticket_detail.php?id=$ticketId");
