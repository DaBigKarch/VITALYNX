<?php
namespace App\Services;
use App\Core\Model;

class NotificationService extends Model {
    
    public function notifyUser($userId, $type, $message, $caseId = null) {
        if ($this->hasUnreadUser($userId, $type, $caseId)) return false;
        $stmt = $this->db->prepare("INSERT INTO notifications (user_id, case_id, type, message) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$userId, $caseId, $type, $message]);
    }

    public function notifyHospital($hospitalId, $type, $message, $caseId = null) {
        if ($this->hasUnreadHospital($hospitalId, $type, $caseId)) return false;
        $stmt = $this->db->prepare("INSERT INTO notifications (hospital_id, case_id, type, message) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$hospitalId, $caseId, $type, $message]);
    }

    private function hasUnreadUser($userId, $type, $caseId) {
        $stmt = $this->db->prepare("SELECT id FROM notifications WHERE user_id = ? AND type = ? AND case_id = ? AND is_read = 0");
        $stmt->execute([$userId, $type, $caseId]);
        return $stmt->fetch() !== false;
    }

    private function hasUnreadHospital($hospitalId, $type, $caseId) {
        $stmt = $this->db->prepare("SELECT id FROM notifications WHERE hospital_id = ? AND type = ? AND case_id = ? AND is_read = 0");
        $stmt->execute([$hospitalId, $type, $caseId]);
        return $stmt->fetch() !== false;
    }

    public function getUnreadForUser($userId) {
        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getUnreadForHospital($hospitalId) {
        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE hospital_id = ? AND is_read = 0 ORDER BY created_at DESC");
        $stmt->execute([$hospitalId]);
        return $stmt->fetchAll();
    }

    public function markAsRead($notificationId, $userId = null, $hospitalId = null) {
        if ($userId) {
            $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
            return $stmt->execute([$notificationId, $userId]);
        } elseif ($hospitalId) {
            $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND hospital_id = ?");
            return $stmt->execute([$notificationId, $hospitalId]);
        }
        return false;
    }

    public function markAllAsRead($userId = null, $hospitalId = null) {
        if ($userId) {
            $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
            return $stmt->execute([$userId]);
        } elseif ($hospitalId) {
            $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE hospital_id = ?");
            return $stmt->execute([$hospitalId]);
        }
        return false;
    }
}
