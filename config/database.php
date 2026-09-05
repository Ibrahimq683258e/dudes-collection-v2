<?php
/**
 * Database Connection Management (PDO Driver)
 * Supports MySQL and SQLite fallback for local CLI testing.
 */

require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $pdo;

    // Database Configuration
    private $host = '127.0.0.1';
    private $db   = 'boutique_db';
    private $user = 'root';
    private $pass = '';
    private $charset = 'utf8mb4';

    private function __construct() {
        // Check if MySQL PDO is available and host is reachable, otherwise use SQLite file fallback for CLI testing
        $useSqliteFallback = false;

        if (defined('USE_SQLITE_TESTING') && USE_SQLITE_TESTING) {
            $useSqliteFallback = true;
        }

        if (!$useSqliteFallback) {
            $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
            } catch (PDOException $e) {
                // Fallback to SQLite database file in memory or storage if MySQL connection fails in sandbox CLI test environment
                $useSqliteFallback = true;
            }
        }

        if ($useSqliteFallback) {
            $sqlitePath = APP_ROOT . '/data/boutique.sqlite';
            if (!file_exists(dirname($sqlitePath))) {
                @mkdir(dirname($sqlitePath), 0755, true);
            }
            $dsn = "sqlite:" . $sqlitePath;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            $this->pdo = new PDO($dsn, null, null, $options);
            // Enable foreign keys in SQLite
            $this->pdo->exec("PRAGMA foreign_keys = ON;");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup() {
        throw new \Exception("Cannot unserialize a singleton.");
    }
}
