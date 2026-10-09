<?php
namespace App\Models;
use App\Core\Model;

class CaseHospital extends Model {
    
    public function createOrUpdate($caseId, $hospitalId, $distance, $status = 'MATCHED') {
        $stmt = $this->db->prepare("
            INSERT INTO case_hospitals (case_id, hospital_id, distance, status)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE distance = VALUES(distance), status = VALUES(status)
        ");
        return $stmt->execute([$caseId, $hospitalId, $distance, $status]);
    }

    public function updateStatus($caseId, $hospitalId, $status) {
        $stmt = $this->db->prepare("UPDATE case_hospitals SET status = ? WHERE case_id = ? AND hospital_id = ?");
        return $stmt->execute([$status, $caseId, $hospitalId]);
    }

    public function assignCase($caseId, $hospitalId, $userId) {
        $stmt = $this->db->prepare("UPDATE case_hospitals SET assigned_to = ? WHERE case_id = ? AND hospital_id = ?");
        return $stmt->execute([$userId, $caseId, $hospitalId]);
    }

    public function setDoctorReview($caseId, $hospitalId, $requiresReview) {
        $stmt = $this->db->prepare("UPDATE case_hospitals SET requires_doctor_review = ? WHERE case_id = ? AND hospital_id = ?");
        return $stmt->execute([$requiresReview ? 1 : 0, $caseId, $hospitalId]);
    }

    public function getHospitalsForCase($caseId) {
        $stmt = $this->db->prepare("
            SELECT ch.*, h.name, h.address, h.phone 
            FROM case_hospitals ch
            JOIN hospitals h ON ch.hospital_id = h.id
            WHERE ch.case_id = ?
            ORDER BY ch.distance ASC
        ");
        $stmt->execute([$caseId]);
        return $stmt->fetchAll();
    }

    public function getCasesForHospital($hospitalId) {
        $stmt = $this->db->prepare("
            SELECT ch.*, c.case_number, c.status as case_status,
                   COALESCE(a.urgency, c.urgency) as urgency, c.created_at as case_created_at
            FROM case_hospitals ch
            JOIN emergency_cases c ON ch.case_id = c.id
            LEFT JOIN ai_assessments a ON a.id = (
                SELECT MAX(a2.id) FROM ai_assessments a2 WHERE a2.case_id = c.id
            )
            WHERE ch.hospital_id = ?
            ORDER BY ch.notified_at DESC
        ");
        $stmt->execute([$hospitalId]);
        return $stmt->fetchAll();
    }

    public function getDashboardStatsForHospital($hospitalId) {
        $stmt = $this->db->prepare("
            SELECT c.status, COUNT(DISTINCT c.id) AS count
            FROM case_hospitals ch
            JOIN emergency_cases c ON c.id = ch.case_id
            WHERE ch.hospital_id = ? AND ch.status NOT IN ('DECLINED', 'EXPIRED')
            GROUP BY c.status
        ");
        $stmt->execute([$hospitalId]);
        $stats = ['TRIAGED' => 0, 'ACCEPTED' => 0, 'IN_CARE' => 0, 'RESOLVED' => 0];
        foreach ($stmt->fetchAll() as $row) {
            if (isset($stats[$row['status']])) $stats[$row['status']] = (int)$row['count'];
        }
        return $stats;
    }

    public function findByCaseAndHospital($caseId, $hospitalId) {
        $stmt = $this->db->prepare("SELECT * FROM case_hospitals WHERE case_id = ? AND hospital_id = ?");
        $stmt->execute([$caseId, $hospitalId]);
        return $stmt->fetch();
    }
}
