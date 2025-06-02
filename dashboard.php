<?php
session_start();
require_once 'classes/DB.php';
require_once 'classes/Auth.php';

$auth = new Auth(DB::getInstance()->getConnection());
if (!$auth->isLoggedIn()) {
    header('Location: login.html');
    exit;
}

$user = $auth->currentUser();
$db = DB::getInstance()->getConnection();

// Rollenbasierte Auswahl
if (in_array($user['role'], ['admin', 'support'])) {
    $stmt = $db->query("
        SELECT t.*, u.username AS ersteller, c.name AS kategorie 
        FROM tickets t
        JOIN users u ON t.created_by = u.id
        LEFT JOIN categories c ON t.category_id = c.id
        ORDER BY t.created_at DESC
    ");
} else {
    $stmt = $db->prepare("
        SELECT t.*, u.username AS ersteller, c.name AS kategorie 
        FROM tickets t
        JOIN users u ON t.created_by = u.id
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE t.created_by = ?
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([$user['id']]);
}

$tickets = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Dashboard – BFW TICKET SYSTEM</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="topbar">
    <div class="logo">BFW TICKET SYSTEM</div>
    <nav>
      <a href="creat_ticket.php">Tickets</a>
      <?php if ($user['role'] === 'admin'): ?>
        <a href="admin_user.php">Benutzerverwaltung</a>
        <a href="#">Statistik</a>
      <?php endif; ?>
      <a href="#" id="logoutBtn">Abmelden</a>
    </nav>
  </header>

  <main class="dashboard">
    <h1>Hallo, <?= htmlspecialchars($user['username']) ?>!</h1>
    <h2>Tickets Übersicht</h2>
    <table class="ticket-table">
      <thead>
        <tr>
          <th>No.</th>
          <th>Erstellt am</th>
          <th>Ersteller</th>
          <th>Kategorie</th>
          <th>Titel</th>
          <th>Status</th>
          <th>Priorität</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tickets as $index => $ticket): ?>
          <tr>
            <td><?= $index + 1 ?></td>
            <td><?= date('d.m.Y H:i', strtotime($ticket['created_at'])) ?></td>
            <td><?= htmlspecialchars($ticket['ersteller']) ?></td>
            <td><?= htmlspecialchars($ticket['kategorie'] ?? '-') ?></td>
            <td><?= htmlspecialchars($ticket['title']) ?></td>
            <td><?= htmlspecialchars($ticket['status']) ?></td>
            <td>
              <span class="prio prio-<?= $ticket['priority'] ?>">
                <?= $ticket['priority'] ?>
              </span>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </main>

  <script>
    document.getElementById('logoutBtn').addEventListener('click', async () => {
      await fetch('api/auth.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'logout'})
      });
      window.location.href = 'login.html';
    });
  </script>
</body>
</html>
