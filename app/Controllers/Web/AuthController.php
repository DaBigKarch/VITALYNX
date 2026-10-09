<?php
namespace App\Controllers\Web;
use App\Core\Controller;
use App\Models\User;

class AuthController extends Controller {
    public function loginForm() {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $this->view('auth/login', ['title' => 'Login', 'csrf_token' => $_SESSION['csrf_token']]);
    }
    
    public function login() {
        $submittedToken = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
            $this->view('auth/login', ['title' => 'Login', 'error' => 'Invalid or missing CSRF token.', 'csrf_token' => $_SESSION['csrf_token'] ?? '']);
            return;
        }

        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        
        $userModel = new User();
        $user = $userModel->findByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['name'];
            if (!empty($user['hospital_id'])) {
                $_SESSION['hospital_id'] = $user['hospital_id'];
            }
            if (!empty($user['staff_role'])) {
                $_SESSION['staff_role'] = $user['staff_role'];
            }
            
            $this->redirect('/dashboard');
        } else {
            $this->view('auth/login', ['title' => 'Login', 'error' => 'Invalid credentials', 'csrf_token' => $_SESSION['csrf_token']]);
        }
    }
    
    public function registerForm() {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $this->view('auth/register', ['title' => 'Register', 'csrf_token' => $_SESSION['csrf_token']]);
    }
    
    public function register() {
        $submittedToken = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
            $this->view('auth/register', ['title' => 'Register', 'error' => 'Invalid or missing CSRF token.', 'csrf_token' => $_SESSION['csrf_token'] ?? '']);
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $phone = trim($_POST['phone'] ?? '');
        
        if(empty($name) || empty($email) || empty($password)) {
            $this->view('auth/register', ['title' => 'Register', 'error' => 'Please fill all required fields.', 'csrf_token' => $_SESSION['csrf_token']]);
            return;
        }

        $userModel = new User();
        
        if ($userModel->findByEmail($email)) {
            $this->view('auth/register', ['title' => 'Register', 'error' => 'Email already registered.', 'csrf_token' => $_SESSION['csrf_token']]);
            return;
        }
        
        if ($userModel->create($name, $email, $password, $phone)) {
            $this->redirect('/login');
        } else {
            $this->view('auth/register', ['title' => 'Register', 'error' => 'Registration failed.', 'csrf_token' => $_SESSION['csrf_token']]);
        }
    }
    
    public function logout() {
        session_destroy();
        $this->redirect('/');
    }
}
