<?php
namespace App\Models;
use App\Core\Model;

class CaseMessage extends Model {
    public function create($caseId, $senderId, $senderRole, $message) {
        $stmt = $this->db->prepare("INSERT INTO case_messages (case_id, sender_id, sender_role, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$caseId, $senderId, $senderRole, $message]);
        return $this->db->lastInsertId();
    }
    
    public function findByCaseId($caseId) {
        $stmt = $this->db->prepare("SELECT * FROM case_messages WHERE case_id = ? ORDER BY created_at ASC");
        $stmt->execute([$caseId]);
        return $stmt->fetchAll();
    }
}
