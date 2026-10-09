<?php
namespace App\Controllers\Web;
use App\Core\Controller;
use App\Models\EmergencyCase;
use App\Models\AiMessage;
use App\Models\AiAssessment;
use App\Services\AIService;

class EmergencyController extends Controller {
    public function reportForm() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
            $this->redirect('/dashboard');
        }
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        $this->view('emergency/report', ['title' => 'Report an Emergency', 'csrf_token' => $_SESSION['csrf_token']]);
    }
    
    public function submitReport() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
            $this->redirect('/dashboard');
        }
        
        $submittedToken = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
            $this->view('emergency/report', ['title' => 'Report an Emergency', 'error' => 'Invalid or missing CSRF token.', 'csrf_token' => $_SESSION['csrf_token'] ?? '']);
            return;
        }
        
        $description = trim($_POST['description'] ?? '');
        $lat = $_POST['latitude'] ?? '';
        $lng = $_POST['longitude'] ?? '';
        
        if (strlen($description) > 5000) {
            $this->view('emergency/report', ['title' => 'Report an Emergency', 'error' => 'Description too long (max 5000 chars).', 'csrf_token' => $_SESSION['csrf_token']]);
            return;
        }
        if (empty($description)) {
            $this->view('emergency/report', ['title' => 'Report an Emergency', 'error' => 'Please describe what is happening.', 'csrf_token' => $_SESSION['csrf_token']]);
            return;
        }

        $latitude = null;
        $longitude = null;

        if ($lat !== '' && $lng !== '') {
            $parsedLat = filter_var($lat, FILTER_VALIDATE_FLOAT);
            $parsedLng = filter_var($lng, FILTER_VALIDATE_FLOAT);

            if ($parsedLat !== false && $parsedLng !== false && $parsedLat >= -90 && $parsedLat <= 90 && $parsedLng >= -180 && $parsedLng <= 180) {
                $latitude = $parsedLat;
                $longitude = $parsedLng;
            }
            // If location is provided but invalid, we just ignore it (per requirements: "Reject malformed values... allow submission without location")
        }
        
        $caseModel = null;
        try {
            $caseModel = new EmergencyCase();
            $caseModel->beginTransaction();
            $caseId = $caseModel->create($_SESSION['user_id'], $description, $latitude, $longitude);
            $eventModel = new \App\Models\CaseEvent();
            $eventModel->create($caseId, 'EMERGENCY_REPORTED', 'patient_' . $_SESSION['user_id']);
            $msgModel = new AiMessage();
            $msgModel->create($caseId, 'user', $description);
            $caseModel->commit();
        } catch (\Throwable $e) {
            if ($caseModel) $caseModel->rollBack();
            error_log('VITALYNX emergency report save failed: ' . get_class($e));
            http_response_code(200);
            $this->view('emergency/report', [
                'title' => 'Report an Emergency',
                'error' => 'We could not save this report. Your submission was not confirmed. Please try again when safe; contact local emergency services now for immediate danger.',
                'description' => $description,
                'csrf_token' => $_SESSION['csrf_token']
            ]);
            return;
        }

        try {
            $aiService = new AIService();
            $aiResp = $aiService->processConversation($description, []);
        
        if ($aiResp['type'] === 'message') {
            if (!empty($aiResp['notice'])) $msgModel->create($caseId, 'system', $aiResp['notice']);
            $msgModel->create($caseId, 'ai', $aiResp['data']);
        } elseif ($aiResp['type'] === 'completion') {
            $data = $aiResp['data'];
            try {
                $caseModel->beginTransaction();
                $assessmentModel = new AiAssessment();
                $assessmentModel->create($caseId, $data['summary'], $data['urgency'], $data['confidence'], $data['red_flags'], $data['recommended_action'], $data['model'] ?? 'vitalynx-safe-fallback-v1');
                $caseModel->updateStatus($caseId, 'TRIAGED');
                
                $eventModel = new \App\Models\CaseEvent();
                $eventModel->create($caseId, 'AI_ASSESSMENT_COMPLETED', 'system');

                // Route case
                $case = $caseModel->findById($caseId, $_SESSION['user_id']);
                if ($case && !empty($case['latitude']) && !empty($case['longitude'])) {
                    $matchingService = new \App\Services\HospitalMatchingService();
                    $matchingService->matchAndRouteCase($caseId, $case['latitude'], $case['longitude'], $data['urgency']);
                }
                $caseModel->commit();
            } catch (\Throwable $e) {
                $caseModel->rollBack();
                error_log('VITALYNX assessment persistence failed: ' . get_class($e));
                $_SESSION['error'] = 'Your emergency report is saved, but this assessment could not be finalized. You can return to the case and continue when safe.';
            }
        }
        } catch (\Throwable $e) {
            error_log('VITALYNX assessment turn failed: ' . get_class($e));
            $_SESSION['error'] = 'Your emergency report is saved, but the assessment response could not be saved. You can return to the case and continue when safe.';
        }
        
        $this->redirect('/emergency/triage?id=' . $caseId);
    }
    
    public function triage() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
            $this->redirect('/dashboard');
        }
        
        $caseId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$caseId) {
            $this->redirect('/dashboard');
        }

        $caseModel = new EmergencyCase();
        $case = $caseModel->findById($caseId, $_SESSION['user_id']);
        
        if (!$case) {
            $this->redirect('/dashboard');
        }

        if ($case['status'] === 'TRIAGED') {
            $assessmentModel = new AiAssessment();
            $assessment = $assessmentModel->findByCaseId($caseId);
            
            if ($assessment) {
                $caseHospitalModel = new \App\Models\CaseHospital();
                $matchedHospitals = $caseHospitalModel->getHospitalsForCase($caseId);
                
                $this->view('emergency/assessment_result', [
                    'title' => 'Emergency Assessment Complete',
                    'case' => $case,
                    'assessment' => $assessment,
                    'matchedHospitals' => $matchedHospitals
                ]);
                return;
            }
        }
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $msgModel = new AiMessage();
        $messages = $msgModel->findByCaseId($caseId);
        
        $this->view('emergency/triage', [
            'title' => 'Emergency Assessment',
            'case' => $case,
            'messages' => $messages,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    public function sendMessage() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
            $this->redirect('/dashboard');
        }
        
        $caseId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$caseId) {
            $this->redirect('/dashboard');
        }

        $caseModel = new EmergencyCase();
        $case = $caseModel->findById($caseId, $_SESSION['user_id']);
        
        if (!$case) {
            $this->redirect('/dashboard');
        }

        if ($case['status'] === 'TRIAGED') {
            $this->redirect('/emergency/triage?id=' . $caseId);
        }

        $submittedToken = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
            $this->redirect('/emergency/triage?id=' . $caseId);
        }

        $message = trim($_POST['message'] ?? '');
        if ($message === '' || strlen($message) > 2000) {
            $_SESSION['error'] = 'Please enter a message of 2,000 characters or fewer.';
            $this->redirect('/emergency/triage?id=' . $caseId);
        }

        $msgModel = new AiMessage();
        $msgModel->create($caseId, 'user', $message);

        $messages = $msgModel->findByCaseId($caseId);

        $aiService = new AIService();
        $aiResp = $aiService->processConversation($case['description'], $messages);
        
        if ($aiResp['type'] === 'message') {
            if (!empty($aiResp['notice'])) $msgModel->create($caseId, 'system', $aiResp['notice']);
            $msgModel->create($caseId, 'ai', $aiResp['data']);
        } elseif ($aiResp['type'] === 'completion') {
            $data = $aiResp['data'];
            
            try {
                $caseModel->beginTransaction();
                $assessmentModel = new AiAssessment();
                $existing = $assessmentModel->findByCaseId($caseId);
                
                if (!$existing) {
                    $assessmentModel->create($caseId, $data['summary'], $data['urgency'], $data['confidence'], $data['red_flags'], $data['recommended_action'], $data['model'] ?? 'vitalynx-safe-fallback-v1');
                    $caseModel->updateStatus($caseId, 'TRIAGED');
                    
                    $eventModel = new \App\Models\CaseEvent();
                    $eventModel->create($caseId, 'AI_ASSESSMENT_COMPLETED', 'system');

                    // Route case
                    $updatedCase = $caseModel->findById($caseId, $_SESSION['user_id']);
                    if ($updatedCase && !empty($updatedCase['latitude']) && !empty($updatedCase['longitude'])) {
                        $matchingService = new \App\Services\HospitalMatchingService();
                        $matchingService->matchAndRouteCase($caseId, $updatedCase['latitude'], $updatedCase['longitude'], $data['urgency']);
                    }
                }
                $caseModel->commit();
            } catch (\Exception $e) {
                $caseModel->rollBack();
                error_log("Failed to create assessment for case ID $caseId: " . $e->getMessage());
                // In a real app we might redirect to an error page, but for now we'll just continue or show a generic error
                // We'll let it redirect to triage which will just reload the conversation since status isn't TRIAGED
                $_SESSION['error'] = 'An unexpected error occurred finalizing your assessment. Please try again.';
            }
        }

        $this->redirect('/emergency/triage?id=' . $caseId);
    }

    public function submitSos() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
            $this->redirect('/login');
            return;
        }

        $submittedToken = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
            $_SESSION['error'] = 'Invalid CSRF token.';
            $this->redirect('/dashboard');
            return;
        }

        $caseModel = new EmergencyCase();
        
        // Duplicate SOS protection
        if ($caseModel->hasActiveCase($_SESSION['user_id'])) {
            $_SESSION['error'] = 'You already have an active emergency case.';
            $this->redirect('/dashboard');
            return;
        }

        $lat = $_POST['latitude'] ?? '';
        $lng = $_POST['longitude'] ?? '';
        
        $latitude = null;
        $longitude = null;

        if ($lat !== '' && $lng !== '') {
            $parsedLat = filter_var($lat, FILTER_VALIDATE_FLOAT);
            $parsedLng = filter_var($lng, FILTER_VALIDATE_FLOAT);

            if ($parsedLat !== false && $parsedLng !== false && $parsedLat >= -90 && $parsedLat <= 90 && $parsedLng >= -180 && $parsedLng <= 180) {
                $latitude = $parsedLat;
                $longitude = $parsedLng;
            }
        }

        $description = 'EMERGENCY SOS ACTIVATED. Immediate escalation requested by patient.';
        $caseId = $caseModel->create($_SESSION['user_id'], $description, $latitude, $longitude);

        // Add SOS_ACTIVATED event
        $eventModel = new \App\Models\CaseEvent();
        $eventModel->create($caseId, 'SOS_ACTIVATED', 'patient_' . $_SESSION['user_id']);

        $msgModel = new \App\Models\AiMessage();
        $msgModel->create($caseId, 'user', $description);

        try {
            $caseModel->beginTransaction();
            // Instantly escalate to TRIAGED so it enters the hospital workflow
            $assessmentModel = new \App\Models\AiAssessment();
            $summary = 'Patient explicitly activated Emergency SOS via hold interaction.';
            $urgency = 'CRITICAL';
            $flags = json_encode(['SOS Activated']);
            $rec = 'Immediate emergency medical response requested.';
            
            $assessmentModel->create($caseId, $summary, $urgency, 1.0, $flags, $rec);
            $caseModel->updateStatus($caseId, 'TRIAGED');
            
            $eventModel->create($caseId, 'AI_ASSESSMENT_COMPLETED', 'system');

            if ($latitude !== null && $longitude !== null) {
                $matchingService = new \App\Services\HospitalMatchingService();
                $matchingService->matchAndRouteCase($caseId, $latitude, $longitude, 'CRITICAL');
            }
            $caseModel->commit();
        } catch (\Exception $e) {
            $caseModel->rollBack();
            error_log("Failed to process SOS case ID $caseId: " . $e->getMessage());
        }

        $this->redirect('/emergency/sos/result?id=' . $caseId);
    }

    public function sosResult() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
            $this->redirect('/login');
        }

        $caseId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$caseId) {
            $this->redirect('/dashboard');
        }

        $caseModel = new EmergencyCase();
        $case = $caseModel->findById($caseId, $_SESSION['user_id']);

        if (!$case) {
            $this->redirect('/dashboard');
        }

        $caseHospitalModel = new \App\Models\CaseHospital();
        $matchedHospitals = $caseHospitalModel->getHospitalsForCase($caseId);

        $this->view('emergency/sos_result', [
            'title' => 'Emergency SOS Activated',
            'case' => $case,
            'matchedHospitals' => $matchedHospitals
        ]);
    }
}
