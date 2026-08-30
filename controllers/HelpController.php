<?php

require_once __DIR__ . '/../core/Controller.php';

class HelpController extends Controller
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            exit;
        }
    }

    // GET /help
    public function index(): void
    {
        $this->view('customer/help', [
            'name' => $_SESSION['user_name'],
            'active' => 'help',
        ]);
    }
}
