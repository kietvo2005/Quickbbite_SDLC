<?php
/**
 * Reusable Database PDO Connection Class
 * Implementing Singleton pattern to avoid duplicate connections.
 */

require_once __DIR__ . '/../config/config.php';

class Database {
    private static $instance = null;
    private $conn;

    /**
     * Database constructor.
     * Establish PDO connection with security settings.
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]);
        } catch (PDOException $e) {
            die("Database Connection Error: " . $e->getMessage());
        }
    }

    /**
     * Get Database class instance.
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Get PDO connection.
     * @return PDO
     */
    public function getConnection() {
        return $this->conn;
    }

    /**
     * Execute a SELECT statement and fetch all rows.
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function queryAll($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Execute a SELECT statement and fetch a single row.
     * @param string $sql
     * @param array $params
     * @return array|false
     */
    public function queryRow($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Execute an INSERT, UPDATE, or DELETE statement.
     * @param string $sql
     * @param array $params
     * @return PDOStatement
     */
    public function execute($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Get the last inserted ID.
     * @return string
     */
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
}
