<?php
namespace App\Controllers\Web;
use App\Core\Controller;

class AdminController extends Controller {

    private function requireAdmin() {
        if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('/login');
            exit;
        }
    }

    public function dashboard() {
        $this->requireAdmin();
        $adminModel = new \App\Models\AdminModel();
        $stats = $adminModel->getSystemStats();
        
        $this->view('admin/dashboard', [
            'title' => 'Admin Dashboard',
            'stats' => $stats
        ]);
    }

    public function users() {
        $this->requireAdmin();
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        $adminModel = new \App\Models\AdminModel();
        $users = $adminModel->getUsers($limit, $offset);
        
        $this->view('admin/users', [
            'title' => 'Manage Users',
            'users' => $users,
            'page' => $page
        ]);
    }

    public function hospitals() {
        $this->requireAdmin();
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        $adminModel = new \App\Models\AdminModel();
        $hospitals = $adminModel->getHospitals($limit, $offset);
        
        $this->view('admin/hospitals', [
            'title' => 'Manage Hospitals',
            'hospitals' => $hospitals,
            'page' => $page
        ]);
    }

    public function updateHospital() {
        $this->requireAdmin();
        $this->verifyCsrf();

        $hospitalId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $field = filter_input(INPUT_POST, 'field');
        $value = filter_input(INPUT_POST, 'value');

        if (!$hospitalId || !in_array($field, ['status', 'emergency_available'])) {
            $_SESSION['error'] = 'Invalid request parameters.';
            $this->redirect('/admin/hospitals');
            return;
        }

        $adminModel = new \App\Models\AdminModel();
        $hospital = $adminModel->getHospitalById($hospitalId);
        if (!$hospital) {
            $_SESSION['error'] = 'Hospital not found.';
            $this->redirect('/admin/hospitals');
            return;
        }

        $oldValue = $hospital[$field];
        
        try {
            $adminModel->beginTransaction();
            $adminModel->updateHospital($hospitalId, $field, $value);
            $adminModel->logAction($_SESSION['user_id'], 'UPDATE_HOSPITAL_' . strtoupper($field), 'hospital', $hospitalId, $oldValue, $value);
            $adminModel->commit();
            $_SESSION['success'] = 'Hospital updated successfully.';
        } catch (\Exception $e) {
            $adminModel->rollBack();
            $_SESSION['error'] = 'Failed to update hospital.';
        }

        $this->redirect('/admin/hospitals');
    }

    public function cases() {
        $this->requireAdmin();
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $status = filter_input(INPUT_GET, 'status');
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        $adminModel = new \App\Models\AdminModel();
        $cases = $adminModel->getCases($limit, $offset, $status);
        
        $this->view('admin/cases', [
            'title' => 'Emergency Cases',
            'cases' => $cases,
            'page' => $page,
            'statusFilter' => $status
        ]);
    }

    public function caseView() {
        $this->requireAdmin();
        $caseId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        
        if (!$caseId) {
            $this->redirect('/admin/cases');
            return;
        }

        $caseModel = new \App\Models\EmergencyCase();
        $case = $caseModel->findById($caseId);
        
        if (!$case) {
            $this->redirect('/admin/cases');
            return;
        }

        $assessmentModel = new \App\Models\AiAssessment();
        $assessment = $assessmentModel->findByCaseId($caseId);
        
        $eventModel = new \App\Models\CaseEvent();
        $events = $eventModel->findByCaseId($caseId);

        $caseHospitalModel = new \App\Models\CaseHospital();
        $routedHospitals = $caseHospitalModel->findByCaseId($caseId);

        $this->view('admin/case_view', [
            'title' => 'Case Details',
            'case' => $case,
            'assessment' => $assessment,
            'events' => $events,
            'routedHospitals' => $routedHospitals
        ]);
    }


    public function analytics() {
        $this->requireAdmin();
        $range = filter_input(INPUT_GET, 'range') ?: 'all';
        $allowedRanges = ['today', '7d', '30d', '90d', 'all'];
        
        if (!in_array($range, $allowedRanges)) {
            $range = 'all';
        }

        $analyticsModel = new \App\Models\AnalyticsModel();
        
        $metrics = [
            'core' => $analyticsModel->getCoreMetrics($range),
            'urgency' => $analyticsModel->getUrgencyDistribution($range),
            'sos' => $analyticsModel->getSosMetrics($range),
            'routing' => $analyticsModel->getHospitalRoutingMetrics($range),
            'followup' => $analyticsModel->getFollowupMetrics($range),
            'resolution' => $analyticsModel->getResolutionOutcomes($range),
            'timing' => $analyticsModel->getTimingMetrics($range)
        ];
        
        $this->view('admin/analytics', [
            'title' => 'Operational Analytics',
            'range' => $range,
            'metrics' => $metrics
        ]);
    }

    public function events() {
        $this->requireAdmin();
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $limit = 100;
        $offset = ($page - 1) * $limit;
        
        $adminModel = new \App\Models\AdminModel();
        $events = $adminModel->getEvents($limit, $offset);
        
        $this->view('admin/events', [
            'title' => 'System Events Log',
            'events' => $events,
            'page' => $page
        ]);
    }
}
