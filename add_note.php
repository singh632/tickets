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

$ticketId = $_POST['ticket_id'];
$note = trim($_POST['note']);

if ($note !== '') {
    $stmt = $db->prepare("INSERT INTO ticket_notes (ticket_id, user_id, note, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$ticketId, $user['id'], $note]);
}

header("Location: ticket_detail.php?id=$ticketId");
