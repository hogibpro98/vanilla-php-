<?php

require_once __DIR__ . '/connection.php';

/**
 * Database class for secure queries
 */
class Database {
    private $pdo;
    
    public function __construct() {
        $this->pdo = DatabaseConnection::getInstance()->getConnection();
    }
    
    /**
     * Execute a SELECT query with prepared statements
     *
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind to placeholders
     * @return array Result set
     */
    public function select($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }
    
    /**
     * Execute a single row SELECT query
     *
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind to placeholders
     * @return array|null Single result row or null
     */
    public function selectOne($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }
    
    /**
     * Execute an INSERT query
     *
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @return int Last insert ID
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $query = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($data);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Insert failed: " . $e->getMessage());
        }
    }
    
    /**
     * Execute an UPDATE query
     *
     * @param string $table Table name
     * @param array $data Associative array of column => value to update
     * @param string $where WHERE clause (with placeholders)
     * @param array $params Parameters for WHERE clause
     * @return int Number of affected rows
     */
    public function update($table, $data, $where, $params = []) {
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setParts);
        
        $query = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(array_merge($data, $params));
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Update failed: " . $e->getMessage());
        }
    }
    
    /**
     * Execute a DELETE query
     *
     * @param string $table Table name
     * @param string $where WHERE clause (with placeholders)
     * @param array $params Parameters for WHERE clause
     * @return int Number of affected rows
     */
    public function delete($table, $where, $params = []) {
        $query = "DELETE FROM {$table} WHERE {$where}";
        
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Delete failed: " . $e->getMessage());
        }
    }
    
    /**
     * Execute a custom query with prepared statements
     *
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind to placeholders
     * @return \PDOStatement Statement object
     */
    public function query($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }
}
