<?php
namespace App\Models;
use App\Core\Model;

class User extends Model {
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    public function create($name, $email, $password, $phone = '', $role = 'patient') {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$name, $email, $hash, $phone, $role]);
    }
    
    public function getHospitalStaff($hospitalId) {
        $stmt = $this->db->prepare("SELECT id, name, email, staff_role FROM users WHERE hospital_id = ? AND role = 'hospital'");
        $stmt->execute([$hospitalId]);
        return $stmt->fetchAll();
    }

    public function isClinicalStaffForHospital($userId, $hospitalId) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE id = ? AND hospital_id = ? AND role = 'hospital' AND staff_role IN ('doctor', 'nurse') LIMIT 1");
        $stmt->execute([$userId, $hospitalId]);
        return $stmt->fetch() !== false;
    }
}
