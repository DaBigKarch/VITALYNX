<?php
namespace App\Controllers\Web;
use App\Core\Controller;

class DashboardController extends Controller {
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
        
        $role = $_SESSION['user_role'];
        if ($role === 'patient') {
            $caseModel = new \App\Models\EmergencyCase();
            $cases = $caseModel->getByUserId($_SESSION['user_id']);
            
            $notificationService = new \App\Services\NotificationService();
            $notifications = $notificationService->getUnreadForUser($_SESSION['user_id']);

            $this->view('dashboard/patient', [
                'title' => 'Patient Dashboard', 
                'cases' => $cases,
                'notifications' => $notifications
            ]);
        } else if ($role === 'hospital') {
            $this->redirect('/hospital/dashboard');
        } else if ($role === 'admin') {
            $this->redirect('/admin/dashboard');
        } else {
            $this->redirect('/logout');
        }
    }
}
