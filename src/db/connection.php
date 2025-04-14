<?php
/**
 * Database connection class
 */

class DatabaseConnection {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        $host = 'db';  // service name in docker-compose
        $dbname = 'php_app';
        $username = 'app_user';
        $password = 'secret';
        
        $dsn = "mysql:host=$host;dbname=$dbname";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        try {
            $this->pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    // Singleton pattern to ensure only one connection
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new DatabaseConnection();
        }
        return self::$instance;
    }
    
    // Get the PDO connection
    public function getConnection() {
        return $this->pdo;
    }
    
    // Get MySQL version
    public function getMySQLVersion() {
        try {
            $stmt = $this->pdo->query('SELECT VERSION() as version');
            $result = $stmt->fetch();
            return $result['version'];
        } catch (PDOException $e) {
            return 'N/A';
        }
    }
}
