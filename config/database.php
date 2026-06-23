<?php
class Database {
    private $host;
    private $db;
    private $user;
    private $pass;
    private $port;
    private $conn;

    public function __construct() {
        // Read from Environment Variables (Railway) or fallback to local XAMPP
        $this->host = getenv('MYSQLHOST') ?: (getenv('MYSQL_HOST') ?: 'localhost');
        $this->db   = getenv('MYSQLDATABASE') ?: (getenv('MYSQL_DATABASE') ?: 'taskflow_pro');
        $this->user = getenv('MYSQLUSER') ?: (getenv('MYSQL_USER') ?: 'root');
        $this->pass = getenv('MYSQLPASSWORD') ?: (getenv('MYSQL_PASSWORD') ?: '');
        $this->port = getenv('MYSQLPORT') ?: (getenv('MYSQL_PORT') ?: '3306');
    }

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};port={$this->port};dbname={$this->db};charset=utf8",
                $this->user,
                $this->pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            die(json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]));
        }
        return $this->conn;
    }
}
