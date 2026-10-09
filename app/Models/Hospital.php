<?php
namespace App\Models;
use App\Core\Model;

class Hospital extends Model {
    public function getActiveEmergencyHospitals() {
        $stmt = $this->db->query("
            SELECT id, name, latitude, longitude, address, phone, emergency_available, status 
            FROM hospitals 
            WHERE status = 'active' 
              AND emergency_available = 1 
              AND latitude IS NOT NULL 
              AND longitude IS NOT NULL
        ");
        return $stmt->fetchAll();
    }

    public function getAllActiveHospitals() {
        $stmt = $this->db->query("
            SELECT id, name, latitude, longitude, address, phone, emergency_available, status 
            FROM hospitals 
            WHERE status = 'active' 
              AND latitude IS NOT NULL 
              AND longitude IS NOT NULL
        ");
        return $stmt->fetchAll();
    }
}
