<?php
namespace App\Models;
use App\Core\Model;

class ClinicalNote extends Model {
    public function create($caseId, $userId, $hospitalId, $noteText, $noteType = 'GENERAL') {
        $stmt = $this->db->prepare("INSERT INTO clinical_notes (case_id, user_id, hospital_id, note_text, note_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$caseId, $userId, $hospitalId, $noteText, $noteType]);
        return $this->db->lastInsertId();
    }
    
    public function findByCaseIdAndHospital($caseId, $hospitalId) {
        $stmt = $this->db->prepare("
            SELECT cn.*, u.name as author_name, u.staff_role 
            FROM clinical_notes cn
            JOIN users u ON cn.user_id = u.id
            WHERE cn.case_id = ? AND cn.hospital_id = ?
            ORDER BY cn.created_at ASC
        ");
        $stmt->execute([$caseId, $hospitalId]);
        return $stmt->fetchAll();
    }
}
