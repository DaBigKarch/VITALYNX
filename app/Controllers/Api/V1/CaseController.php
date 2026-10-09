<?php
namespace App\Controllers\Api\V1;
use App\Controllers\Api\ApiController;

class CaseController extends ApiController {

    public function index() {
        $this->requireAuth();
        
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
        $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT);
        
        if (!$page || $page < 1) $page = 1;
        if (!$limit || $limit < 1) $limit = 20;
        if ($limit > 100) $limit = 100;
        
        $offset = ($page - 1) * $limit;

        $caseModel = new \App\Models\EmergencyCase();
        $db = \App\Config\Database::getConnection();
        
        $cases = [];
        $total = 0;

        if ($this->apiUser['role'] === 'patient') {
            // we should get paginated patient cases if method existed, else manually slice.
            $allCases = $caseModel->getByUserId($this->apiUser['id']);
            $total = count($allCases);
            $cases = array_slice($allCases, $offset, $limit);
        } elseif ($this->apiUser['role'] === 'hospital') {
            $stmt = $db->prepare("
                SELECT e.id, e.case_number, e.status, e.created_at, e.resolved_at, e.resolution_outcome, a.urgency 
                FROM emergency_cases e
                JOIN case_hospitals ch ON e.id = ch.case_id
                LEFT JOIN ai_assessments a ON a.id = (SELECT MAX(a2.id) FROM ai_assessments a2 WHERE a2.case_id = e.id)
                WHERE ch.hospital_id = ?
                ORDER BY e.created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->bindValue(1, $this->apiUser['hospital_id'], \PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
            $stmt->bindValue(3, $offset, \PDO::PARAM_INT);
            $stmt->execute();
            $cases = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $stmtCount = $db->prepare("SELECT COUNT(*) FROM case_hospitals WHERE hospital_id = ?");
            $stmtCount->execute([$this->apiUser['hospital_id']]);
            $total = (int)$stmtCount->fetchColumn();
        } elseif ($this->apiUser['role'] === 'admin') {
            $adminModel = new \App\Models\AdminModel();
            $cases = $adminModel->getCases($limit, $offset);
            $stmtCount = $db->query("SELECT COUNT(*) FROM emergency_cases");
            $total = (int)$stmtCount->fetchColumn();
        } else {
            $this->jsonError('Unauthorized role', 'FORBIDDEN', 403);
            return;
        }

        // Sanitize output for API
        $safeCases = [];
        foreach ($cases as $c) {
            $safeCases[] = [
                'id' => $c['id'],
                'case_number' => $c['case_number'],
                'status' => $c['status'],
                'urgency' => $c['urgency'] ?? null,
                'resolution_outcome' => $c['resolution_outcome'] ?? null,
                'created_at' => $c['created_at'],
                'resolved_at' => $c['resolved_at'] ?? null
            ];
        }

        $this->jsonResponse([
            'cases' => $safeCases,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total
            ]
        ]);
    }

    public function view($id) {
        $this->requireAuth();
        
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            $this->jsonError('Invalid case ID', 'BAD_REQUEST', 400);
        }

        $caseModel = new \App\Models\EmergencyCase();
        $case = $caseModel->findById($id);

        if (!$case) {
            $this->jsonError('Case not found', 'NOT_FOUND', 404);
        }

        // Apply access rules
        if ($this->apiUser['role'] === 'patient' && $case['user_id'] != $this->apiUser['id']) {
            $this->jsonError('Case not found', 'NOT_FOUND', 404); // prevent IDOR enumeration
        }

        if ($this->apiUser['role'] === 'hospital') {
            $chModel = new \App\Models\CaseHospital();
            $routed = $chModel->getHospitalsForCase($id);
            $isRouted = false;
            foreach ($routed as $r) {
                if ($r['hospital_id'] == $this->apiUser['hospital_id']) {
                    $isRouted = true;
                    break;
                }
            }
            if (!$isRouted) {
                $this->jsonError('Case not found', 'NOT_FOUND', 404);
            }
        }

        $aiModel = new \App\Models\AiAssessment();
        $assessment = $aiModel->findByCaseId($id);

        // Optional: Fetch Care Updates (patient-visible)
        $cuModel = new \App\Models\CareUpdate();
        $updates = $cuModel->findByCaseId($id);

        $safeCase = [
            'id' => $case['id'],
            'case_number' => $case['case_number'],
            'status' => $case['status'],
            'created_at' => $case['created_at'],
            'resolved_at' => $case['resolved_at'],
            'resolution_outcome' => $case['resolution_outcome'],
        ];

        $safeAssessment = $assessment ? [
            'urgency' => $assessment['urgency'],
            'summary' => $assessment['summary'],
            'recommendation' => $assessment['recommendation']
        ] : null;

        $safeUpdates = array_map(function($u) {
            return [
                'update_type' => $u['update_type'],
                'message' => $u['update_text'],
                'created_at' => $u['created_at']
            ];
        }, $updates);

        $this->jsonResponse([
            'case' => $safeCase,
            'assessment' => $safeAssessment,
            'care_updates' => $safeUpdates
        ]);
    }

    public function timeline($id) {
        $this->requireAuth();
        
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            $this->jsonError('Invalid case ID', 'BAD_REQUEST', 400);
        }

        $caseModel = new \App\Models\EmergencyCase();
        $case = $caseModel->findById($id);
        
        if (!$case) {
            $this->jsonError('Case not found', 'NOT_FOUND', 404);
        }
        
        if ($this->apiUser['role'] === 'patient' && $case['user_id'] != $this->apiUser['id']) {
            $this->jsonError('Case not found', 'NOT_FOUND', 404);
        }

        if ($this->apiUser['role'] === 'hospital') {
            $chModel = new \App\Models\CaseHospital();
            $routed = $chModel->getHospitalsForCase($id);
            $isRouted = false;
            foreach ($routed as $r) {
                if ($r['hospital_id'] == $this->apiUser['hospital_id']) {
                    $isRouted = true;
                    break;
                }
            }
            if (!$isRouted) {
                $this->jsonError('Case not found', 'NOT_FOUND', 404);
            }
        }

        $eventModel = new \App\Models\CaseEvent();
        $events = $eventModel->findByCaseId($id);

        $safeEvents = array_map(function($e) {
            return [
                'event_type' => $e['event'],
                'created_at' => $e['created_at']
            ];
        }, $events);

        $this->jsonResponse(['timeline' => $safeEvents]);
    }
}
