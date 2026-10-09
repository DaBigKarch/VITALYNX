<?php
namespace App\Models;
use App\Core\Model;

class AiAssessment extends Model {
    public function create($caseId, $summary, $urgency, $confidence, $redFlags, $recommendation, $modelName = 'vitalynx-mock-triage-v1') {
        $stmt = $this->db->prepare("
            INSERT INTO ai_assessments (case_id, summary, urgency, confidence, red_flags, recommendation, model)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $redFlagsJson = json_encode($redFlags);
        
        // Map UI urgency terms to DB ENUM
        $dbUrgency = 'medium';
        $u = strtoupper($urgency);
        if ($u === 'CRITICAL') $dbUrgency = 'critical';
        elseif ($u === 'HIGH') $dbUrgency = 'high';
        elseif ($u === 'LOW') $dbUrgency = 'low';
        
        $stmt->execute([
            $caseId,
            $summary,
            $dbUrgency,
            $confidence,
            $redFlagsJson,
            $recommendation,
            $modelName
        ]);
        return $this->db->lastInsertId();
    }

    public function findByCaseId($caseId) {
        $stmt = $this->db->prepare("SELECT * FROM ai_assessments WHERE case_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$caseId]);
        return $stmt->fetch();
    }
}
