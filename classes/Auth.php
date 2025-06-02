<?php
class Auth {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function login($username, $password) {
        error_log("Login attempt for: ".$username);

        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            error_log("Benutzer nicht gefunden");
            return false;
        }

        if (empty($user['password_hash'])) {
            error_log("Kein Passwort-Hash gespeichert!");
            return false;
        }

        $isValid = password_verify($password, $user['password_hash']);
        error_log("Password verify: " . ($isValid ? "MATCH" : "NO MATCH"));

        if ($isValid) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role']
            ];
            return true;
        }

        return false;
    }

    public function logout() {
        session_destroy();
    }

    public function isLoggedIn(): bool {
        return isset($_SESSION['user']);
    }

    public function currentUser() {
        return $_SESSION['user'] ?? null;
    }
}
