<?php
namespace App\Controllers\Web;
use App\Core\Controller;
use App\Models\EmergencyCase;
use App\Models\AiAssessment;
use App\Models\CaseEvent;
use App\Models\CaseMessage;

class PatientController extends Controller {
    public function __construct() {
        parent::__construct();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
            $this->redirect('/login');
        }
    }


    public function history() {
        if (empty($_SESSION['user_id'])) {
            $this->redirect('/login');
        }

        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $historyModel = new \App\Models\HistoryModel();
        $cases = $historyModel->getPatientHistory($_SESSION['user_id'], $limit, $offset);

        $this->view('patient/history', [
            'cases' => $cases,
            'page' => $page
        ]);
    }

    public function viewCase() {
        $caseId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$caseId) {
            $this->redirect('/dashboard');
        }

        $caseModel = new EmergencyCase();
        $case = $caseModel->findById($caseId, $_SESSION['user_id']); // Ensure patient owns case
        
        if (!$case) {
            $this->redirect('/dashboard');
        }

        $assessmentModel = new AiAssessment();
        $assessment = $assessmentModel->findByCaseId($caseId);

        $eventModel = new CaseEvent();
        $events = $eventModel->findByCaseId($caseId);

        $messageModel = new CaseMessage();
        $messages = $messageModel->findByCaseId($caseId);
        $aiMessageModel = new \App\Models\AiMessage();
        $aiMessages = $aiMessageModel->findByCaseId($caseId);
        $careUpdateModel = new \App\Models\CareUpdate();
        $followupModel = new \App\Models\CaseFollowup();
        $followup = $followupModel->findByCaseId($caseId);
        $careUpdates = $careUpdateModel->findByCaseId($caseId);
        
        $recommendedHospitals = [];
        if (!empty($case['latitude']) && !empty($case['longitude'])) {
            $matchingService = new \App\Services\HospitalMatchingService();
            $recommendedHospitals = $matchingService->getRecommendedHospitals($case['latitude'], $case['longitude']);
        }
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $this->view('patient/case_view', [
            'title' => 'Emergency Case - ' . htmlspecialchars($case['case_number']),
            'case' => $case,
            'assessment' => $assessment,
            'events' => $events,
            'messages' => $messages,
            'aiMessages' => $aiMessages,
            'careUpdates' => $careUpdates ?? [],
            'followup' => $followup ?? null,
            'recommendedHospitals' => $recommendedHospitals,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    public function sendMessage() {


        $submittedToken = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
            $_SESSION['error'] = 'Invalid CSRF token.';
            $this->redirect('/dashboard');
            return;
        }

        $caseId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $message = trim($_POST['message'] ?? '');
        
        if (!$caseId || empty($message) || strlen($message) > 2000) {
            $this->redirect('/dashboard');
            return;
        }

        $caseModel = new EmergencyCase();
        $case = $caseModel->findById($caseId, $_SESSION['user_id']);

        // Only allow messaging if case is ACCEPTED
        if (!$case || !in_array($case['status'], ['ACCEPTED', 'ASSIGNED', 'IN_CARE'])) {
            $_SESSION['error'] = 'You can only message the hospital for accepted cases.';
            $this->redirect('/patient/case?id=' . $caseId);
            return;
        }

        try {
            $caseModel->beginTransaction();
            $msgModel = new CaseMessage();
            $msgModel->create($caseId, $_SESSION['user_id'], 'patient', $message);
            
            $eventModel = new \App\Models\CaseEvent();
            $eventModel->create($caseId, 'PATIENT_MESSAGE', 'patient_' . $_SESSION['user_id']);
            $caseModel->commit();
        } catch (\Exception $e) {
            $caseModel->rollBack();
        }
        
        $this->redirect('/patient/case?id=' . $caseId);
    }

    public function submitFollowup() {
        $submittedToken = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
            $_SESSION['error'] = 'Invalid CSRF token.';
            $this->redirect('/dashboard');
            return;
        }

        $caseId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $needsCoordination = filter_input(INPUT_POST, 'needs_coordination', FILTER_VALIDATE_INT);
        $ratingInput = trim($_POST['experience_rating'] ?? '');
        $rating = $ratingInput === '' ? null : filter_var($ratingInput, FILTER_VALIDATE_INT);
        $feedback = trim($_POST['feedback_notes'] ?? '');

        if (!$caseId || !in_array($needsCoordination, [0, 1], true) || ($ratingInput !== '' && ($rating === false || $rating < 1 || $rating > 5)) || strlen($feedback) > 2000) {
            $_SESSION['error'] = 'Invalid follow-up data.';
            $this->redirect('/dashboard');
            return;
        }

        $caseModel = new EmergencyCase();
        $case = $caseModel->findById($caseId, $_SESSION['user_id']);

        if (!$case || $case['status'] !== 'RESOLVED') {
            $_SESSION['error'] = 'Case must be resolved to submit follow-up.';
            $this->redirect('/dashboard');
            return;
        }

        $followupModel = new \App\Models\CaseFollowup();
        if ($followupModel->findByCaseId($caseId)) {
            $_SESSION['error'] = 'Follow-up already submitted.';
            $this->redirect('/patient/case?id=' . $caseId);
            return;
        }

        try {
            $chModel = new \App\Models\CaseHospital();
            $acceptedHospitalId = null;
            foreach ($chModel->getHospitalsForCase($caseId) as $route) {
                if ($route['status'] === 'ACCEPTED') {
                    $acceptedHospitalId = (int)$route['hospital_id'];
                    break;
                }
            }

            $caseModel->beginTransaction();
            $followupModel->create($caseId, $_SESSION['user_id'], $acceptedHospitalId, $needsCoordination, $rating, $feedback);
            
            $eventModel = new \App\Models\CaseEvent();
            $eventModel->create($caseId, 'CASE_FEEDBACK_SUBMITTED', 'patient_' . $_SESSION['user_id']);
            
            if ($needsCoordination && $acceptedHospitalId !== null) {
                $notificationService = new \App\Services\NotificationService();
                $notificationService->notifyHospital(
                    $acceptedHospitalId,
                    'FOLLOWUP_REQUESTED',
                    'Patient for case ' . $case['case_number'] . ' requested further coordination.',
                    $caseId
                );
            }
            $caseModel->commit();
            $_SESSION['success'] = $needsCoordination && $acceptedHospitalId === null
                ? 'Feedback saved. No receiving facility is linked to this case; contact local emergency services if you need urgent help.'
                : 'Follow-up submitted successfully.';
        } catch (\Exception $e) {
            $caseModel->rollBack();
            $_SESSION['error'] = 'Failed to submit follow-up.';
        }
        
        $this->redirect('/patient/case?id=' . $caseId);
    }
}
