<?php
namespace App\Models;
use App\Core\Model;

class AdminModel extends Model {
    
    public function getSystemStats() {
        $stats = [];
        
        $stats['total_users'] = $this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats['total_patients'] = $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'patient'")->fetchColumn();
        $stats['total_staff'] = $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'hospital'")->fetchColumn();
        $stats['total_hospitals'] = $this->db->query("SELECT COUNT(*) FROM hospitals")->fetchColumn();
        $stats['active_hospitals'] = $this->db->query("SELECT COUNT(*) FROM hospitals WHERE status = 'active'")->fetchColumn();
        
        $stats['total_cases'] = $this->db->query("SELECT COUNT(*) FROM emergency_cases")->fetchColumn();
        $stats['resolved_cases'] = $this->db->query("SELECT COUNT(*) FROM emergency_cases WHERE status = 'RESOLVED'")->fetchColumn();
        $stats['active_cases'] = $this->db->query("SELECT COUNT(*) FROM emergency_cases WHERE status NOT IN ('RESOLVED')")->fetchColumn();
        $stats['in_care_cases'] = $this->db->query("SELECT COUNT(*) FROM emergency_cases WHERE status = 'IN_CARE'")->fetchColumn();
        
        return $stats;
    }

    public function getUsers($limit, $offset) {
        $stmt = $this->db->prepare("SELECT id, name, email, role, staff_role, hospital_id, created_at FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, (int)$limit, \PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getHospitals($limit, $offset) {
        $stmt = $this->db->prepare("SELECT * FROM hospitals ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, (int)$limit, \PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getCases($limit, $offset, $status = null) {
        if ($status) {
            $stmt = $this->db->prepare("SELECT e.id, e.case_number, e.status, e.created_at, e.resolved_at, a.urgency FROM emergency_cases e LEFT JOIN ai_assessments a ON e.id = a.case_id WHERE e.status = ? ORDER BY e.created_at DESC LIMIT ? OFFSET ?");
            $stmt->bindValue(1, $status);
            $stmt->bindValue(2, (int)$limit, \PDO::PARAM_INT);
            $stmt->bindValue(3, (int)$offset, \PDO::PARAM_INT);
        } else {
            $stmt = $this->db->prepare("SELECT e.id, e.case_number, e.status, e.created_at, e.resolved_at, a.urgency FROM emergency_cases e LEFT JOIN ai_assessments a ON e.id = a.case_id ORDER BY e.created_at DESC LIMIT ? OFFSET ?");
            $stmt->bindValue(1, (int)$limit, \PDO::PARAM_INT);
            $stmt->bindValue(2, (int)$offset, \PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getHospitalById($id) {
        $stmt = $this->db->prepare("SELECT * FROM hospitals WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateHospital($id, $field, $value) {
        $allowedFields = ['status', 'emergency_available'];
        if (!in_array($field, $allowedFields)) return false;
        
        $stmt = $this->db->prepare("UPDATE hospitals SET {$field} = ? WHERE id = ?");
        return $stmt->execute([$value, $id]);
    }

    public function logAction($adminId, $action, $entityType, $entityId, $oldValue, $newValue) {
        $stmt = $this->db->prepare("INSERT INTO admin_audit_logs (admin_user_id, action, entity_type, entity_id, old_value, new_value) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$adminId, $action, $entityType, $entityId, $oldValue, $newValue]);
    }

    public function getEvents($limit, $offset) {
        $stmt = $this->db->prepare("SELECT * FROM case_events ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, (int)$limit, \PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
