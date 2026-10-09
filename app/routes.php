<?php
use App\Controllers\Web\HomeController;
use App\Controllers\Web\AuthController;
use App\Controllers\Web\DashboardController;

$router->get('/', [HomeController::class, 'index']);
$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'registerForm']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);

$router->get('/dashboard', [DashboardController::class, 'index']);

use App\Controllers\Web\EmergencyController;
$router->get('/emergency/report', [EmergencyController::class, 'reportForm']);
$router->post('/emergency/report', [EmergencyController::class, 'submitReport']);
$router->get('/emergency/triage', [EmergencyController::class, 'triage']);
$router->post('/emergency/triage', [EmergencyController::class, 'sendMessage']);
$router->post('/emergency/sos', [EmergencyController::class, 'submitSos']);
$router->get('/emergency/sos/result', [EmergencyController::class, 'sosResult']);

use App\Controllers\Web\PatientController;
$router->get('/patient/case', [PatientController::class, 'viewCase']);
$router->get('/patient/history', [PatientController::class, 'history']);
$router->post('/patient/case/message', [PatientController::class, 'sendMessage']);
$router->post('/patient/case/follow-up', [PatientController::class, 'submitFollowup']);

use App\Controllers\Web\HospitalController;
$router->get('/hospital/dashboard', [HospitalController::class, 'dashboard']);
$router->get('/hospital/history', [HospitalController::class, 'history']);
$router->get('/hospital/case', [HospitalController::class, 'viewCase']);
$router->post('/hospital/case/action', [HospitalController::class, 'caseAction']);
$router->post('/hospital/case/assign', [HospitalController::class, 'assignCase']);
$router->post('/hospital/case/start-care', [HospitalController::class, 'startCare']);
$router->post('/hospital/case/care-update', [HospitalController::class, 'addCareUpdate']);
$router->post('/hospital/case/resolve', [HospitalController::class, 'resolveCase']);
$router->post('/hospital/case/note', [HospitalController::class, 'addNote']);
$router->post('/hospital/case/escalate', [HospitalController::class, 'escalateCase']);

use App\Controllers\Web\NotificationController;
$router->post('/notifications/read', [NotificationController::class, 'markRead']);

// Future API routes
// $router->post('/api/auth/login', [AuthApiController::class, 'login']);
// $router->get('/api/cases', [CaseApiController::class, 'index']);

use App\Controllers\Web\AdminController;
$router->get('/admin/dashboard', [AdminController::class, 'dashboard']);
$router->get('/admin/users', [AdminController::class, 'users']);
$router->get('/admin/hospitals', [AdminController::class, 'hospitals']);
$router->post('/admin/hospital/update', [AdminController::class, 'updateHospital']);
$router->get('/admin/cases', [AdminController::class, 'cases']);
$router->get('/admin/case', [AdminController::class, 'caseView']);
$router->get('/admin/events', [AdminController::class, 'events']);
$router->get('/admin/analytics', [AdminController::class, 'analytics']);

// API V1 Routes
use App\Controllers\Api\V1\CaseController;
$router->get('/api/v1/cases', [CaseController::class, 'index']);
$router->get('/api/v1/cases/{id}', [CaseController::class, 'view']);
$router->get('/api/v1/cases/{id}/timeline', [CaseController::class, 'timeline']);
?>
