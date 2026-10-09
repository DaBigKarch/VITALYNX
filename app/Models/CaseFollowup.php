<?php
namespace App\Models;
use App\Core\Model;

class CaseFollowup extends Model {
    public function create($caseId, $userId, $hospitalId, $needsCoordination, $rating, $feedback) {
        $stmt = $this->db->prepare("INSERT INTO case_followups (case_id, user_id, hospital_id, needs_further_coordination, experience_rating, feedback_text) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$caseId, $userId, $hospitalId, $needsCoordination, $rating, $feedback]);
        return $this->db->lastInsertId();
    }
    
    public function findByCaseId($caseId) {
        $stmt = $this->db->prepare("SELECT * FROM case_followups WHERE case_id = ?");
        $stmt->execute([$caseId]);
        return $stmt->fetch();
    }
}
