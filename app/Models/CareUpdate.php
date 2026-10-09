<?php
namespace App\Models;
use App\Core\Model;

class CareUpdate extends Model {
    public function create($caseId, $hospitalId, $userId, $updateType, $updateText) {
        $stmt = $this->db->prepare("INSERT INTO care_updates (case_id, hospital_id, user_id, update_type, update_text) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$caseId, $hospitalId, $userId, $updateType, $updateText]);
        return $this->db->lastInsertId();
    }
    
    public function findByCaseId($caseId) {
        $stmt = $this->db->prepare("
            SELECT cu.*, u.name as author_name, u.staff_role, h.name as hospital_name 
            FROM care_updates cu
            JOIN users u ON cu.user_id = u.id
            JOIN hospitals h ON cu.hospital_id = h.id
            WHERE cu.case_id = ?
            ORDER BY cu.created_at ASC
        ");
        $stmt->execute([$caseId]);
        return $stmt->fetchAll();
    }
}
