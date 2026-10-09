<?php
namespace App\Controllers\Web;

use App\Core\Controller;
use App\Services\NotificationService;

class NotificationController extends Controller {

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('HTTP/1.1 401 Unauthorized');
            exit;
        }
    }

    public function markRead() {
        $submittedToken = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
            $_SESSION['error'] = 'Invalid CSRF token.';
            $this->redirectBack();
            return;
        }

        $notificationId = filter_input(INPUT_POST, 'notification_id', FILTER_VALIDATE_INT);
        $markAll = isset($_POST['mark_all']) && $_POST['mark_all'] === '1';

        $userId = $_SESSION['user_role'] === 'patient' ? $_SESSION['user_id'] : null;
        $hospitalId = $_SESSION['user_role'] === 'hospital' ? ($_SESSION['hospital_id'] ?? null) : null;

        $service = new NotificationService();

        if ($markAll) {
            $service->markAllAsRead($userId, $hospitalId);
        } elseif ($notificationId) {
            $service->markAsRead($notificationId, $userId, $hospitalId);
        }

        $this->redirectBack();
    }

    private function redirectBack() {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/dashboard';
        $this->redirect($referer);
    }
}
