<?php
namespace App\Models;
use App\Core\Model;

class EmergencyCase extends Model {
    public function create($userId, $description, $latitude = null, $longitude = null) {
        $caseNumber = 'CASE-' . strtoupper(uniqid());
        $stmt = $this->db->prepare("INSERT INTO emergency_cases (case_number, user_id, description, latitude, longitude, status) VALUES (?, ?, ?, ?, ?, 'REPORTED')");
        $stmt->execute([$caseNumber, $userId, $description, $latitude, $longitude]);
        return $this->db->lastInsertId();
    }

    public function hasActiveCase($userId) {
        $stmt = $this->db->prepare("
            SELECT id FROM emergency_cases 
            WHERE user_id = ? AND status NOT IN ('RESOLVED')
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch() !== false;
    }

    public function findById($id, $userId = null) {
        if ($userId) {
            $stmt = $this->db->prepare("SELECT * FROM emergency_cases WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
        } else {
            $stmt = $this->db->prepare("SELECT * FROM emergency_cases WHERE id = ?");
            $stmt->execute([$id]);
        }
        return $stmt->fetch();
    }
    
    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE emergency_cases SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
    
    public function getByUserId($userId) {
        $stmt = $this->db->prepare("
            SELECT e.id, e.case_number, e.status, e.created_at, e.resolved_at, e.resolution_outcome,
                   a.urgency, a.summary 
            FROM emergency_cases e
            LEFT JOIN ai_assessments a ON e.id = a.case_id
            WHERE e.user_id = ?
            ORDER BY e.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getDashboardStats() {
        $stmt = $this->db->query("SELECT status, COUNT(*) as count FROM emergency_cases GROUP BY status");
        $results = $stmt->fetchAll();
        $stats = [
            'TRIAGED' => 0,
            'ACCEPTED' => 0,
            'IN_CARE' => 0,
            'RESOLVED' => 0
        ];
        foreach ($results as $row) {
            if (isset($stats[$row['status']])) {
                $stats[$row['status']] = $row['count'];
            }
        }
        return $stats;
    }

}
