<?php
namespace App\Models;
use App\Core\Model;

class HistoryModel extends Model {
    
    public function getPatientHistory($userId, $limit = 50, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT e.id, e.case_number, e.status, e.created_at, e.resolved_at, e.resolution_outcome,
                   a.urgency,
                   f.id as followup_id, f.needs_further_coordination,
                   GROUP_CONCAT(h.name SEPARATOR ', ') as hospital_names
            FROM emergency_cases e
            LEFT JOIN ai_assessments a ON e.id = a.case_id
            LEFT JOIN case_followups f ON e.id = f.case_id
            LEFT JOIN case_hospitals ch ON e.id = ch.case_id AND ch.status IN ('MATCHED', 'NOTIFIED', 'VIEWED', 'ACCEPTED')
            LEFT JOIN hospitals h ON ch.hospital_id = h.id
            WHERE e.user_id = ?
            GROUP BY e.id
            ORDER BY e.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bindValue(1, $userId, \PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$limit, \PDO::PARAM_INT);
        $stmt->bindValue(3, (int)$offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getHospitalHistory($hospitalId, $limit = 50, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT e.id, e.case_number, e.status, e.created_at, e.resolved_at, e.resolution_outcome,
                   a.urgency,
                   ch.status as hospital_status,
                   u.name as assigned_name,
                   f.id as followup_id, f.needs_further_coordination
            FROM emergency_cases e
            JOIN case_hospitals ch ON e.id = ch.case_id
            LEFT JOIN ai_assessments a ON e.id = a.case_id
            LEFT JOIN case_followups f ON e.id = f.case_id
            LEFT JOIN users u ON ch.assigned_to = u.id
            WHERE ch.hospital_id = ? AND (e.status = 'RESOLVED' OR ch.status IN ('DECLINED', 'EXPIRED'))
            ORDER BY e.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bindValue(1, $hospitalId, \PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$limit, \PDO::PARAM_INT);
        $stmt->bindValue(3, (int)$offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
