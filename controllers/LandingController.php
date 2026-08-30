<?php

require_once __DIR__ . '/../core/Controller.php';

class LandingController extends Controller
{
    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->view('landing/index', [
            'isLoggedIn' => isset($_SESSION['user_id']),
            'userRole' => $_SESSION['user_role'] ?? null,
        ]);
    }
}
