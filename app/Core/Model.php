<?php
namespace App\Core;
use App\Config\Database;

class Model {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function beginTransaction() {
        if (!$this->db->inTransaction()) {
            return $this->db->beginTransaction();
        }
        return false;
    }

    public function commit() {
        if ($this->db->inTransaction()) {
            return $this->db->commit();
        }
        return false;
    }

    public function rollBack() {
        if ($this->db->inTransaction()) {
            return $this->db->rollBack();
        }
        return false;
    }
}
