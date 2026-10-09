<?php
namespace App\Models;
use App\Core\Model;

class ApiToken extends Model {
    
    public function createToken($userId, $name, $expiresAt = null) {
        $rawToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);
        
        $stmt = $this->db->prepare("INSERT INTO api_tokens (user_id, token_hash, name, expires_at) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $tokenHash, $name, $expiresAt]);
        
        return [
            'id' => $this->db->lastInsertId(),
            'raw_token' => $rawToken // Only returned once!
        ];
    }
    
    public function findByToken($rawToken) {
        $tokenHash = hash('sha256', $rawToken);
        $stmt = $this->db->prepare("
            SELECT t.*, u.role, u.hospital_id 
            FROM api_tokens t
            JOIN users u ON t.user_id = u.id
            WHERE t.token_hash = ? AND t.revoked_at IS NULL AND (t.expires_at IS NULL OR t.expires_at > NOW())
        ");
        $stmt->execute([$tokenHash]);
        return $stmt->fetch();
    }
    
    public function recordUsage($id) {
        $stmt = $this->db->prepare("UPDATE api_tokens SET last_used_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
