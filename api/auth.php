<?php
require_once __DIR__ . '/../classes/DB.php';
require_once __DIR__ . '/../classes/Auth.php';

// Debugging aktivieren
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Session sicher starten
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => false,    // Für Entwicklung, in Produktion auf true
        'cookie_httponly' => true,
        'use_strict_mode' => true
    ]);
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

// Datenbankverbindung
$db = DB::getInstance()->getConnection();
$auth = new Auth($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $rawData = file_get_contents("php://input");
        $data = json_decode($rawData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON Parse Error: " . json_last_error_msg());
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Ungültiges JSON']);
            exit;
        }

        if (isset($data['action']) && $data['action'] === 'login') {
            if (empty($data['username']) || empty($data['password'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Benutzername/Passwort fehlt']);
                exit;
            }

            if ($auth->login($data['username'], $data['password'])) {
                echo json_encode(['status' => 'success']);
            } else {
                http_response_code(401); 
                echo json_encode(['status' => 'error', 'message' => 'Login fehlgeschlagen']);
            }

        }

        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Methode nicht erlaubt']);
        break;

}
