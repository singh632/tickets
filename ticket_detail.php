<?php
session_start();
require_once 'classes/DB.php';
require_once 'classes/Auth.php';

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$ticketId = intval($_GET['id']);
$db = DB::getInstance()->getConnection();
$auth = new Auth($db);

if (!$auth->isLoggedIn()) {
    header('Location: login.html');
    exit;
}
$user = $auth->currentUser();

// Ticket holen (nur eigenes oder bei höherer Rolle alle)
if (in_array($user['role'], ['admin', 'support'])) {
    $stmt = $db->prepare("SELECT t.*, u.username AS ersteller FROM tickets t JOIN users u ON t.created_by = u.id WHERE t.id = ?");
    $stmt->execute([$ticketId]);
} else {
    $stmt = $db->prepare("SELECT t.*, u.username AS ersteller FROM tickets t JOIN users u ON t.created_by = u.id WHERE t.id = ? AND t.created_by = ?");
    $stmt->execute([$ticketId, $user['id']]);
}

$ticket = $stmt->fetch();
if (!$ticket) {
    echo "Kein Zugriff oder Ticket nicht gefunden.";
    exit;
}

// Notizen abrufen
$notesStmt = $db->prepare("
    SELECT n.note, n.created_at, u.username 
    FROM ticket_notes n 
    JOIN users u ON n.user_id = u.id 
    WHERE n.ticket_id = ? 
    ORDER BY n.created_at DESC
");
$notesStmt->execute([$ticketId]);
$notes = $notesStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Ticket #<?= $ticket['id'] ?> – Details</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="topbar">
    <div class="logo">BFW TICKET SYSTEM</div>
    <nav>
      <a href="dashboard.php">Dashboard</a>
      <a href="#" id="logoutBtn">Abmelden</a>
    </nav>
  </header>

  <main class="dashboard">
    <h2>Ticket #<?= $ticket['id'] ?> – <?= htmlspecialchars($ticket['title']) ?></h2>
    <p><strong>Erstellt von:</strong> <?= htmlspecialchars($ticket['ersteller']) ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($ticket['status']) ?></p>
    <p><strong>Priorität:</strong> <?= htmlspecialchars($ticket['priority']) ?></p>
    <p><strong>Beschreibung:</strong><br><?= nl2br(htmlspecialchars($ticket['description'])) ?></p>

    <?php if (in_array($user['role'], ['admin', 'support'])): ?>
      <h3>Status ändern</h3>
      <form method="post" action="update_ticket.php">
        <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
        <select name="status">
          <option value="neu" <?= $ticket['status'] === 'neu' ? 'selected' : '' ?>>Neu</option>
          <option value="in Bearbeitung" <?= $ticket['status'] === 'in Bearbeitung' ? 'selected' : '' ?>>In Bearbeitung</option>
          <option value="fertig" <?= $ticket['status'] === 'fertig' ? 'selected' : '' ?>>Fertig</option>
        </select>
        <button type="submit">Speichern</button>
      </form>

      <h3>Support-Notiz hinzufügen</h3>
      <form method="post" action="add_note.php">
        <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
        <textarea name="note" required></textarea>
        <button type="submit">Notiz speichern</button>
      </form>
    <?php endif; ?>

    <h
