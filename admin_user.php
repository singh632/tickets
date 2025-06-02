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
if ($user['role'] !== 'admin') {
    echo "Zugriff verweigert.";
    exit;
}

// Benutzer laden
$stmt = $db->query("SELECT id, username,  role FROM users ORDER BY role DESC, username ASC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Benutzerverwaltung</title>
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
    <h2>Benutzerverwaltung (Admin)</h2>
    <table>
      <thead>
        <tr>
          <th>Benutzer ID</th>
          <th>Benutzername</th>
          
          <th>Rolle</th>
          <th>Aktion</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td><?= htmlspecialchars($u['id']) ?></td>
          <td><?= htmlspecialchars($u['username']) ?></td>
          <td><?= $u['role'] ?></td>
          <td>
            <?php if ($u['id'] != $user['id']): ?>
              <form method="post" action="update_user_role.php" style="display: inline-block">
                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                <select name="role">
                  <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>User</option>
                  <option value="support" <?= $u['role'] === 'support' ? 'selected' : '' ?>>Support</option>
                  <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
                <button type="submit">Speichern</button>
              </form>
            <?php else: ?>
              (Du)
            <?php endif; ?>
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
