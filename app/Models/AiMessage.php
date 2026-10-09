<?php
namespace App\Models;
use App\Core\Model;

class AiMessage extends Model {
    public function create($caseId, $sender, $message) {
        $stmt = $this->db->prepare("INSERT INTO ai_messages (case_id, sender, message) VALUES (?, ?, ?)");
        $stmt->execute([$caseId, $sender, $message]);
        return $this->db->lastInsertId();
    }

    public function findByCaseId($caseId) {
        $stmt = $this->db->prepare("SELECT * FROM ai_messages WHERE case_id = ? ORDER BY created_at ASC, id ASC");
        $stmt->execute([$caseId]);
        return $stmt->fetchAll();
    }
}
