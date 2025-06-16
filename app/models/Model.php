<?php
/**
 * Base Model - All models extend this
 */
class Model {
    protected $db;
    protected $table;
    protected $primary_key = 'id';
    
    public function __construct() {
        global $conn;
        $this->db = $conn;
    }
    
    // Find a record by ID
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primary_key} = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    // Find a record by a field value
    public function findByField($field, $value) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$field} = :value LIMIT 1");
        $stmt->execute(['value' => $value]);
        return $stmt->fetch();
    }
    
    // Get all records
    public function findAll($order_by = null) {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($order_by) {
            $sql .= " ORDER BY {$order_by}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Create a new record
    public function create($data) {
        $fields = array_keys($data);
        $placeholders = array_map(function($field) {
            return ":{$field}";
        }, $fields);
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        
        return $this->db->lastInsertId();
    }
    
    // Update a record
    public function update($id, $data) {
        $fields = array_keys($data);
        $set_clause = array_map(function($field) {
            return "{$field} = :{$field}";
        }, $fields);
        
        $sql = "UPDATE {$this->table} 
                SET " . implode(', ', $set_clause) . " 
                WHERE {$this->primary_key} = :id";
        
        $data['id'] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
    
    // Delete a record
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primary_key} = :id");
        return $stmt->execute(['id' => $id]);
    }
    
    // Count all records
    public function count() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table}");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    // Custom query
    public function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
