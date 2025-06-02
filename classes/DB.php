<?php
class DB {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $dsn = "mysql:host=localhost;dbname=ticketsystem2;charset=utf8mb4";
        $user = "root"; // ggf. anpassen
        $pass = "";     // ggf. anpassen
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        $this->connection = new PDO($dsn, $user, $pass, $options);
    }

    public static function getInstance(): DB {
        if (self::$instance === null) {
            self::$instance = new DB();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->connection;
    }
}
