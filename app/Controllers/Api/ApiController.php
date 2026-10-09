<?php
namespace App\Controllers\Api;
use App\Core\Controller;

class ApiController extends Controller {
    
    protected $apiUser = null;

    public function __construct() {
        parent::__construct();
        // Prevent session redirects for API
    }

    protected function jsonResponse($data = null, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        
        echo json_encode([
            'success' => true,
            'data' => $data,
            'error' => null
        ]);
        exit;
    }

    protected function jsonError($message, $code = 'BAD_REQUEST', $statusCode = 400) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        
        echo json_encode([
            'success' => false,
            'data' => null,
            'error' => [
                'code' => $code,
                'message' => $message
            ]
        ]);
        exit;
    }

    protected function requireAuth() {
        $headers = function_exists('apache_request_headers') ? apache_request_headers() : [];
        $authHeader = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $this->jsonError('Missing or invalid Authorization header', 'UNAUTHORIZED', 401);
        }

        $token = $matches[1];
        
        $tokenModel = new \App\Models\ApiToken();
        $tokenRecord = $tokenModel->findByToken($token);

        if (!$tokenRecord) {
            $this->jsonError('Invalid, expired, or revoked token', 'UNAUTHORIZED', 401);
        }

        $tokenModel->recordUsage($tokenRecord['id']);
        
        $this->apiUser = [
            'id' => $tokenRecord['user_id'],
            'role' => $tokenRecord['role'],
            'hospital_id' => $tokenRecord['hospital_id']
        ];
    }
}
