<?php
namespace App\Models;
use App\Core\Model;

class CaseEvent extends Model {
    public function create($caseId, $event, $actor = null) {
        $stmt = $this->db->prepare("INSERT INTO case_events (case_id, event, actor) VALUES (?, ?, ?)");
        $stmt->execute([$caseId, $event, $actor]);
        return $this->db->lastInsertId();
    }
    
    public function hasEvent($caseId, $eventName) {
        $stmt = $this->db->prepare("SELECT id FROM case_events WHERE case_id = ? AND event = ?");
        $stmt->execute([$caseId, $eventName]);
        return $stmt->fetch() !== false;
    }

    public function findByCaseId($caseId) {
        $stmt = $this->db->prepare("SELECT * FROM case_events WHERE case_id = ? ORDER BY created_at DESC");
        $stmt->execute([$caseId]);
        return $stmt->fetchAll();
    }
}
