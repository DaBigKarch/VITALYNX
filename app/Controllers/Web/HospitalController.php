<?php
namespace App\Controllers\Web;
use App\Core\Controller;
use App\Models\EmergencyCase;
use App\Models\AiAssessment;
use App\Models\AiMessage;
use App\Models\CaseEvent;
use App\Models\CaseMessage;
use App\Config\Database;

class HospitalController extends Controller {
    public function __construct() {
        parent::__construct();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
        if ($_SESSION['user_role'] === 'patient') {
            $this->redirect('/dashboard');
        }
        if ($_SESSION['user_role'] === 'admin') {
            $this->redirect('/dashboard');
        }
        if ($_SESSION['user_role'] !== 'hospital') {
            $this->redirect('/logout');
        }
    }

    public function dashboard() {
        $caseModel = new EmergencyCase();
        
        // Fetch specifically routed cases for this hospital
        $caseHospitalModel = new \App\Models\CaseHospital();
        $routedCases = isset($_SESSION['hospital_id']) ? $caseHospitalModel->getCasesForHospital($_SESSION['hospital_id']) : [];
        
        $notificationService = new \App\Services\NotificationService();
        $notifications = isset($_SESSION['hospital_id']) ? $notificationService->getUnreadForHospital($_SESSION['hospital_id']) : [];

        $this->view('hospital/dashboard', [
            'title' => 'Hospital Dashboard',
            'routedCases' => $routedCases,
            'notifications' => $notifications,
            'stats' => isset($_SESSION['hospital_id'])
                ? $caseHospitalModel->getDashboardStatsForHospital($_SESSION['hospital_id'])
                : ['TRIAGED' => 0, 'ACCEPTED' => 0, 'IN_CARE' => 0, 'RESOLVED' => 0]
        ]);
    }
    

    public function history() {
        if (empty($_SESSION['hospital_id'])) {
            $this->redirect('/login');
        }

        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $historyModel = new \App\Models\HistoryModel();
        $cases = $historyModel->getHospitalHistory($_SESSION['hospital_id'], $limit, $offset);

        $this->view('hospital/history', [
            'cases' => $cases,
            'page' => $page
        ]);
    }

    public function viewCase() {
        $caseId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$caseId) {
            $this->redirect('/hospital/dashboard');
        }

        $caseModel = new EmergencyCase();
        $case = $caseModel->findById($caseId); // Not restricted by user_id for hospitals
        
        if (!$case) {
            $this->redirect('/hospital/dashboard');
        }

        $assessmentModel = new AiAssessment();
        $assessment = $assessmentModel->findByCaseId($caseId);

        $msgModel = new AiMessage();
        $aiMessages = $msgModel->findByCaseId($caseId);

        $eventModel = new CaseEvent();
        $events = $eventModel->findByCaseId($caseId);

        $caseMessageModel = new CaseMessage();
        $humanMessages = $caseMessageModel->findByCaseId($caseId);
        
        $recommendedHospitals = [];
        if (!empty($case['latitude']) && !empty($case['longitude'])) {
            $matchingService = new \App\Services\HospitalMatchingService();
            $recommendedHospitals = $matchingService->getRecommendedHospitals($case['latitude'], $case['longitude']);
        }
        
        $caseHospitalModel = new \App\Models\CaseHospital();
        $isRouted = isset($_SESSION['hospital_id']) ? $caseHospitalModel->findByCaseAndHospital($caseId, $_SESSION['hospital_id']) : false;
        
        if (!$isRouted) {
            $_SESSION['error'] = 'Case not found or not routed to your facility.';
            $this->redirect('/hospital/dashboard');
            return;
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT name, phone FROM users WHERE id = ?");
        $stmt->execute([$case['user_id']]);
        $patient = $stmt->fetch();

        // Clinical Notes & Staff
        $clinicalNotes = [];
        $hospitalStaff = [];
        if (isset($_SESSION['hospital_id'])) {
            $noteModel = new \App\Models\ClinicalNote();
            $clinicalNotes = $noteModel->findByCaseIdAndHospital($caseId, $_SESSION['hospital_id']);
            
            $userModel = new \App\Models\User();
            $hospitalStaff = $userModel->getHospitalStaff($_SESSION['hospital_id']);
            $careUpdateModel = new \App\Models\CareUpdate();
        $followupModel = new \App\Models\CaseFollowup();
        $followup = $followupModel->findByCaseId($caseId);
            $careUpdates = $careUpdateModel->findByCaseId($caseId);
        }

        $this->view('hospital/case_review', [
            'title' => 'Case Review - ' . htmlspecialchars($case['case_number']),
            'case' => $case,
            'assessment' => $assessment,
            'aiMessages' => $aiMessages,
            'events' => $events,
            'humanMessages' => $humanMessages,
            'patient' => $patient,
            'recommendedHospitals' => $recommendedHospitals,
            'isRouted' => $isRouted,
            'clinicalNotes' => $clinicalNotes,
            'hospitalStaff' => $hospitalStaff,
            'careUpdates' => $careUpdates ?? [],
            'followup' => $followup ?? null,
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    }

    public function caseAction() {
        $submittedToken = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
            $_SESSION['error'] = 'Invalid CSRF token.';
            $this->redirect('/hospital/dashboard');
            return;
        }

        $caseId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $action = $_POST['action'] ?? '';
        
        if (!$caseId || !in_array($action, ['accept', 'decline', 'request_info', 'send_message'])) {
            $this->redirect('/hospital/dashboard');
            return;
        }

        $caseModel = new EmergencyCase();
        $case = $caseModel->findById($caseId);
        
        $caseHospitalModel = new \App\Models\CaseHospital();
        $hospitalId = $_SESSION['hospital_id'] ?? null;
        $handoff = $hospitalId ? $caseHospitalModel->findByCaseAndHospital($caseId, $hospitalId) : false;

        if (!$case || !$handoff || ($action !== 'send_message' && $case['status'] !== 'TRIAGED') || ($action === 'send_message' && !in_array($case['status'], ['ACCEPTED', 'ASSIGNED', 'IN_CARE']))) {
            $_SESSION['error'] = 'Case is not available for this action or not routed to your facility.';
            $this->redirect('/hospital/dashboard');
            return;
        }

        $eventModel = new CaseEvent();
        $notificationService = new \App\Services\NotificationService();

        try {
            $caseModel->beginTransaction();

            if ($action === 'accept') {
                $caseModel->updateStatus($caseId, 'ACCEPTED');
                $eventModel->create($caseId, 'CASE_ACCEPTED', 'user_' . $_SESSION['user_id']);
                
                $caseHospitalModel = new \App\Models\CaseHospital();
                $hospitalId = $_SESSION['hospital_id'] ?? null;
                if ($hospitalId) {
                    $handoff = $caseHospitalModel->findByCaseAndHospital($caseId, $hospitalId);
                    if ($handoff) {
                        $caseHospitalModel->updateStatus($caseId, $hospitalId, 'ACCEPTED');
                    }
                }
                
                $notificationService->notifyUser($case['user_id'], 'CASE_ACCEPTED', 'Your emergency case has been accepted by the receiving facility.', $caseId);
                
                $caseModel->commit();
                $_SESSION['success'] = 'You have accepted this case. You can now communicate directly with the patient.';
                $this->redirect('/hospital/case?id=' . $caseId);
                return;

            } elseif ($action === 'decline') {
                $caseHospitalModel = new \App\Models\CaseHospital();
                $hospitalId = $_SESSION['hospital_id'] ?? null;
                $handoff = $hospitalId ? $caseHospitalModel->findByCaseAndHospital($caseId, $hospitalId) : false;
                
                if ($handoff) {
                    $caseHospitalModel->updateStatus($caseId, $hospitalId, 'DECLINED');
                    $eventModel->create($caseId, 'CASE_DECLINED', 'user_' . $_SESSION['user_id']);
                    $notificationService->notifyUser($case['user_id'], 'CASE_DECLINED', 'The previously matched facility is unavailable. VITALYNX is continuing the coordination process.', $caseId);
                    $caseModel->commit();
                    $_SESSION['success'] = 'You have declined this case. It remains available in the queue for other facilities.';
                } else {
                    $caseModel->rollBack();
                    $_SESSION['error'] = 'You cannot decline a case that was not routed to you.';
                }
                $this->redirect('/hospital/dashboard');
                return;

            } elseif ($action === 'more_info' || $action === 'request_info') {
                $eventModel->create($caseId, 'MORE_INFORMATION_REQUESTED', 'user_' . $_SESSION['user_id']);
                $notificationService->notifyUser($case['user_id'], 'MORE_INFORMATION_REQUESTED', 'The reviewing facility has requested more information.', $caseId);
                $caseModel->commit();
                $_SESSION['success'] = 'Requested more information for case ' . $case['case_number'] . '.';
                $this->redirect('/hospital/case?id=' . $caseId);
                return;
            }
        } catch (\Exception $e) {
            $caseModel->rollBack();
            error_log("Failed to process action $action for case $caseId: " . $e->getMessage());
            $_SESSION['error'] = 'An unexpected error occurred while processing your request.';
            $this->redirect('/hospital/case?id=' . $caseId);
            return;
        }

        if ($action === 'send_message') {
            $message = trim($_POST['message'] ?? '');
            if (!empty($message) && strlen($message) <= 2000) {
                try {
                    $caseModel->beginTransaction();
                    $msgModel = new \App\Models\CaseMessage();
                    $msgModel->create($caseId, $_SESSION['user_id'], 'hospital', $message);
                    
                    $eventModel = new \App\Models\CaseEvent();
                    $eventModel->create($caseId, 'HOSPITAL_MESSAGE', 'user_' . $_SESSION['user_id']);
                    $caseModel->commit();
                } catch (\Exception $e) {
                    $caseModel->rollBack();
                }
            }
            $this->redirect('/hospital/case?id=' . $caseId);
        }
    }
    public function assignCase() {
        $this->verifyCsrf();
        $caseId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $assigneeId = filter_input(INPUT_POST, 'assignee_id', FILTER_VALIDATE_INT);
        
        if (!$caseId || !$assigneeId || !isset($_SESSION['hospital_id'])) {
            $this->redirect('/hospital/dashboard');
            return;
        }

        $caseModel = new \App\Models\EmergencyCase();
        $case = $caseModel->findById($caseId);
        
        $caseHospitalModel = new \App\Models\CaseHospital();
        $handoff = $caseHospitalModel->findByCaseAndHospital($caseId, $_SESSION['hospital_id']);

        if (!$case || !$handoff || $handoff['status'] !== 'ACCEPTED') {
            $_SESSION['error'] = 'Case must be accepted before assignment.';
            $this->redirect('/hospital/case?id=' . $caseId);
            return;
        }

        $userModel = new \App\Models\User();
        if (!$userModel->isClinicalStaffForHospital($assigneeId, $_SESSION['hospital_id'])) {
            $_SESSION['error'] = 'Choose a doctor or nurse who belongs to your facility.';
            $this->redirect('/hospital/case?id=' . $caseId);
            return;
        }

        try {
            $caseModel->beginTransaction();
            
            $caseHospitalModel->assignCase($caseId, $_SESSION['hospital_id'], $assigneeId);
            if ($case['status'] === 'ACCEPTED') {
                $caseModel->updateStatus($caseId, 'ASSIGNED');
            }
            
            $eventModel = new \App\Models\CaseEvent();
            $eventModel->create($caseId, 'CASE_ASSIGNED', 'user_' . $_SESSION['user_id']);
            
            $notificationService = new \App\Services\NotificationService();
            $notificationService->notifyUser($assigneeId, 'CASE_ASSIGNED', "You have been assigned to Case {$case['case_number']}.", $caseId);
            
            // Optionally notify patient
            $notificationService->notifyUser($case['user_id'], 'CASE_STATUS', 'Your care team has been assigned.', $caseId);

            $caseModel->commit();
            $_SESSION['success'] = 'Case assigned successfully.';
        } catch (\Exception $e) {
            $caseModel->rollBack();
            error_log("Failed to assign case $caseId: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to assign case.';
        }
        
        $this->redirect('/hospital/case?id=' . $caseId);
    }

    public function addNote() {
        $this->verifyCsrf();
        $caseId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $noteText = trim($_POST['note_text'] ?? '');
        $noteType = $_POST['note_type'] ?? 'GENERAL';
        
        if (!$caseId || empty($noteText) || strlen($noteText) > 5000 || !isset($_SESSION['hospital_id'])) {
            $this->redirect('/hospital/case?id=' . $caseId);
            return;
        }

        $caseModel = new \App\Models\EmergencyCase();
        $caseHospitalModel = new \App\Models\CaseHospital();
        if (!$caseHospitalModel->findByCaseAndHospital($caseId, $_SESSION['hospital_id'])) {
            $_SESSION['error'] = 'Case not routed to your facility.';
            $this->redirect('/hospital/dashboard');
            return;
        }
        
        try {
            $caseModel->beginTransaction();
            
            $noteModel = new \App\Models\ClinicalNote();
            $noteModel->create($caseId, $_SESSION['user_id'], $_SESSION['hospital_id'], $noteText, $noteType);
            
            $eventModel = new \App\Models\CaseEvent();
            $eventModel->create($caseId, 'CLINICAL_NOTE_ADDED', 'user_' . $_SESSION['user_id']);
            
            $caseModel->commit();
            $_SESSION['success'] = 'Clinical note added.';
        } catch (\Exception $e) {
            $caseModel->rollBack();
            $_SESSION['error'] = 'Failed to add note.';
        }
        $this->redirect('/hospital/case?id=' . $caseId);
    }

    public function escalateCase() {
        $this->verifyCsrf();
        $caseId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        
        if (!$caseId || !isset($_SESSION['hospital_id'])) {
            $this->redirect('/hospital/dashboard');
            return;
        }

        $caseModel = new \App\Models\EmergencyCase();
        $caseHospitalModel = new \App\Models\CaseHospital();
        
        if (!$caseHospitalModel->findByCaseAndHospital($caseId, $_SESSION['hospital_id'])) {
            $_SESSION['error'] = 'Case not routed to your facility.';
            $this->redirect('/hospital/dashboard');
            return;
        }

        try {
            $caseModel->beginTransaction();
            
            $caseHospitalModel->setDoctorReview($caseId, $_SESSION['hospital_id'], true);
            
            $eventModel = new \App\Models\CaseEvent();
            $eventModel->create($caseId, 'DOCTOR_REVIEW_REQUESTED', 'user_' . $_SESSION['user_id']);
            
            // Notify all doctors in the hospital
            $userModel = new \App\Models\User();
            $staff = $userModel->getHospitalStaff($_SESSION['hospital_id']);
            $notificationService = new \App\Services\NotificationService();
            
            foreach ($staff as $member) {
                if ($member['staff_role'] === 'doctor') {
                    $notificationService->notifyUser($member['id'], 'DOCTOR_REVIEW_REQUESTED', "Doctor review requested for Case {$caseId}.", $caseId);
                }
            }
            
            // Notify patient
            $notificationService->notifyUser($caseModel->findById($caseId)['user_id'], 'CASE_STATUS', 'A doctor review has been requested for your case.', $caseId);
            
            $caseModel->commit();
            $_SESSION['success'] = 'Doctor review requested.';
        } catch (\Exception $e) {
            $caseModel->rollBack();
            $_SESSION['error'] = 'Failed to request doctor review.';
        }
        $this->redirect('/hospital/case?id=' . $caseId);
    }

    public function startCare() {
        $this->verifyCsrf();
        $caseId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        
        if (!$caseId || !isset($_SESSION['hospital_id'])) {
            $this->redirect('/hospital/dashboard');
            return;
        }

        // Verify authorized clinical staff
        if (empty($_SESSION['staff_role']) || !in_array($_SESSION['staff_role'], ['nurse', 'doctor'])) {
            $_SESSION['error'] = 'Only authorized clinical staff can start care.';
            $this->redirect('/hospital/case?id=' . $caseId);
            return;
        }

        $caseModel = new \App\Models\EmergencyCase();
        $case = $caseModel->findById($caseId);
        
        $caseHospitalModel = new \App\Models\CaseHospital();
        $handoff = $caseHospitalModel->findByCaseAndHospital($caseId, $_SESSION['hospital_id']);

        if (!$case || !$handoff) {
            $_SESSION['error'] = 'Case not found or not routed to your facility.';
            $this->redirect('/hospital/dashboard');
            return;
        }

        if ($case['status'] !== 'ASSIGNED' || empty($handoff['assigned_to'])) {
            $_SESSION['error'] = 'Case must be ASSIGNED to a staff member before starting care.';
            $this->redirect('/hospital/case?id=' . $caseId);
            return;
        }

        try {
            $caseModel->beginTransaction();
            
            // Re-fetch to ensure it hasn't changed since transaction began
            $lockedCase = $caseModel->findById($caseId);
            if ($lockedCase['status'] === 'IN_CARE') {
                $caseModel->rollBack();
                $_SESSION['success'] = 'Care has already been started for this case.';
                $this->redirect('/hospital/case?id=' . $caseId);
                return;
            }
            
            $caseModel->updateStatus($caseId, 'IN_CARE');
            
            $eventModel = new \App\Models\CaseEvent();
            $eventModel->create($caseId, 'CASE_IN_CARE', 'user_' . $_SESSION['user_id']);
            
            $notificationService = new \App\Services\NotificationService();
            $notificationService->notifyUser($case['user_id'], 'CASE_STATUS', 'Your emergency case is now being handled by the care team.', $caseId);

            $caseModel->commit();
            $_SESSION['success'] = 'Active care started successfully.';
        } catch (\Exception $e) {
            $caseModel->rollBack();
            error_log("Failed to start care for case $caseId: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to start care.';
        }
        
        $this->redirect('/hospital/case?id=' . $caseId);
    }


    public function addCareUpdate() {
        $this->verifyCsrf();
        $caseId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $updateType = filter_input(INPUT_POST, 'update_type');
        $updateText = trim(filter_input(INPUT_POST, 'update_text') ?? '');
        
        if (!$caseId || !isset($_SESSION['hospital_id'])) {
            $this->redirect('/hospital/dashboard');
            return;
        }

        // Verify authorized clinical staff
        if (empty($_SESSION['staff_role']) || !in_array($_SESSION['staff_role'], ['nurse', 'doctor'])) {
            $_SESSION['error'] = 'Only authorized clinical staff can post care updates.';
            $this->redirect('/hospital/case?id=' . $caseId);
            return;
        }

        $validTypes = ['CARE_STARTED', 'PATIENT_CONTACTED', 'MONITORING_UPDATE', 'CARE_TEAM_UPDATE', 'DOCTOR_UPDATE', 'STATUS_UPDATE'];
        if (!in_array($updateType, $validTypes)) {
            $_SESSION['error'] = 'Invalid update type.';
            $this->redirect('/hospital/case?id=' . $caseId);
            return;
        }

        if (empty($updateText) || strlen($updateText) > 2000) {
            $_SESSION['error'] = 'Update text must be between 1 and 2000 characters.';
            $this->redirect('/hospital/case?id=' . $caseId);
            return;
        }

        $caseModel = new \App\Models\EmergencyCase();
        $case = $caseModel->findById($caseId);
        
        $caseHospitalModel = new \App\Models\CaseHospital();
        $handoff = $caseHospitalModel->findByCaseAndHospital($caseId, $_SESSION['hospital_id']);

        if (!$case || !$handoff) {
            $_SESSION['error'] = 'Case not found or not routed to your facility.';
            $this->redirect('/hospital/dashboard');
            return;
        }

        if ($case['status'] !== 'IN_CARE') {
            $_SESSION['error'] = 'Care updates can only be posted while the case is IN CARE.';
            $this->redirect('/hospital/case?id=' . $caseId);
            return;
        }

        try {
            $caseModel->beginTransaction();
            
            $careUpdateModel = new \App\Models\CareUpdate();
            $careUpdateModel->create($caseId, $_SESSION['hospital_id'], $_SESSION['user_id'], $updateType, $updateText);
            
            $eventModel = new \App\Models\CaseEvent();
            $eventModel->create($caseId, 'CARE_UPDATE_POSTED', 'user_' . $_SESSION['user_id']);
            
            $notificationService = new \App\Services\NotificationService();
            $notificationService->notifyUser($case['user_id'], 'CARE_UPDATE', 'Your care team has provided a new update on your case.', $caseId);

            $caseModel->commit();
            $_SESSION['success'] = 'Care update posted successfully.';
        } catch (\Exception $e) {
            $caseModel->rollBack();
            error_log("Failed to post care update for case $caseId: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to post care update.';
        }
        
        $this->redirect('/hospital/case?id=' . $caseId);
    }

    public function resolveCase() {
        $this->verifyCsrf();
        $caseId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $outcome = filter_input(INPUT_POST, 'resolution_outcome');
        $summary = trim(filter_input(INPUT_POST, 'resolution_summary') ?? '');
        
        if (!$caseId || !isset($_SESSION['hospital_id'])) {
            $this->redirect('/hospital/dashboard');
            return;
        }

        if (empty($_SESSION['staff_role']) || !in_array($_SESSION['staff_role'], ['nurse', 'doctor'])) {
            $_SESSION['error'] = 'Only authorized clinical staff can resolve cases.';
            $this->redirect('/hospital/case?id=' . $caseId);
            return;
        }

        $validOutcomes = ['CARE_COMPLETED', 'TRANSFERRED', 'REFERRED', 'OTHER'];
        if (!in_array($outcome, $validOutcomes)) {
            $_SESSION['error'] = 'Invalid resolution outcome.';
            $this->redirect('/hospital/case?id=' . $caseId);
            return;
        }

        if (empty($summary) || strlen($summary) > 2000) {
            $_SESSION['error'] = 'Resolution summary must be between 1 and 2000 characters.';
            $this->redirect('/hospital/case?id=' . $caseId);
            return;
        }

        $caseModel = new \App\Models\EmergencyCase();
        $case = $caseModel->findById($caseId);
        
        $caseHospitalModel = new \App\Models\CaseHospital();
        $handoff = $caseHospitalModel->findByCaseAndHospital($caseId, $_SESSION['hospital_id']);

        if (!$case || !$handoff) {
            $_SESSION['error'] = 'Case not found or not routed to your facility.';
            $this->redirect('/hospital/dashboard');
            return;
        }

        if ($case['status'] !== 'IN_CARE') {
            $_SESSION['error'] = 'Only IN CARE cases can be resolved.';
            $this->redirect('/hospital/case?id=' . $caseId);
            return;
        }

        try {
            $caseModel->beginTransaction();
            
            // Re-fetch to ensure atomicity
            $lockedCase = $caseModel->findById($caseId);
            if ($lockedCase['status'] === 'RESOLVED') {
                $caseModel->rollBack();
                $_SESSION['success'] = 'Case is already resolved.';
                $this->redirect('/hospital/case?id=' . $caseId);
                return;
            }

            // Perform direct DB update to store resolution data
            $db = \App\Core\Database::getConnection();
            $stmt = $db->prepare("UPDATE emergency_cases SET status = 'RESOLVED', resolved_at = NOW(), resolved_by = ?, resolution_outcome = ?, resolution_summary = ? WHERE id = ?");
            $stmt->execute([$_SESSION['user_id'], $outcome, $summary, $caseId]);
            
            $eventModel = new \App\Models\CaseEvent();
            $eventModel->create($caseId, 'CASE_RESOLVED', 'user_' . $_SESSION['user_id']);
            
            $notificationService = new \App\Services\NotificationService();
            $notificationService->notifyUser($case['user_id'], 'CASE_RESOLVED', 'Your emergency case has been marked as resolved by the care team.', $caseId);

            $caseModel->commit();
            $_SESSION['success'] = 'Case resolved successfully.';
        } catch (\Exception $e) {
            $caseModel->rollBack();
            error_log("Failed to resolve case $caseId: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to resolve case.';
        }
        
        $this->redirect('/hospital/case?id=' . $caseId);
    }
    private function verifyCsrf() {
        $submittedToken = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
            $_SESSION['error'] = 'Invalid CSRF token.';
            $this->redirect('/hospital/dashboard');
            exit;
        }
    }
}
