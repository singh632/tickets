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

// Kategorien laden (optional)
$db = DB::getInstance()->getConnection();
$categories = $db->query("SELECT id, name FROM categories")->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Neues Ticket erstellen</title>
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
    <h2>Neues Ticket erstellen</h2>
    <form id="ticketForm" class="ticket-form">
      <label for="title">Titel *</label>
      <input type="text" name="title" id="title" required>

      <label for="description">Problembeschreibung *</label>
      <textarea name="description" id="description" required></textarea>

      <label for="priority">Priorität *</label>
      <select name="priority" id="priority" required>
        <option value="1">1 (Hoch)</option>
        <option value="2">2 (Mittel)</option>
        <option value="3">3 (Niedrig)</option>
      </select>

      <label for="category_id">Kategorie</label>
      <select name="category_id" id="category_id">
        <option value="">-- Keine --</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>

      <button type="submit">Ticket absenden</button>
    </form>
    <div id="successMessage" class="success-msg"></div>
  </main>

  <script>
    document.getElementById('ticketForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const form = new FormData(e.target);

      const response = await fetch('api/tickets.php', {
        method: 'POST',
        body: JSON.stringify(Object.fromEntries(form)),
        headers: {'Content-Type': 'application/json'}
      });

      const result = await response.json();
      if (result.status === 'success') {
        document.getElementById('successMessage').textContent = 'Ticket erfolgreich erstellt!';
        e.target.reset();
      } else {
        alert(result.message || 'Fehler beim Erstellen des Tickets.');
      }
    });

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
