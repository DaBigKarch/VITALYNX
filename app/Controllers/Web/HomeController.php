<?php
namespace App\Controllers\Web;
use App\Core\Controller;

class HomeController extends Controller {
    public function index() {
        $this->view('home', ['title' => 'VITALYNX - Home']);
    }
}
