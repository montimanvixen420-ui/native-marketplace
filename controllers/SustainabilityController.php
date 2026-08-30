<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/SustainabilityMetrics.php';

class SustainabilityController extends Controller
{
    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->view('sustainability/index', [
            'isLoggedIn' => isset($_SESSION['user_id']),
            'userRole' => $_SESSION['user_role'] ?? null,
            'metrics' => (new SustainabilityMetrics())->get(),
        ]);
    }
}
