<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Feedback.php';

class FeedbackController extends Controller
{
    private Feedback $feedbackModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->feedbackModel = new Feedback();
    }

    private function requireLoggedIn(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            exit;
        }
    }

    // GET /feedback — customer-facing form
    public function showForm(): void
    {
        $this->requireLoggedIn();

        $this->view('customer/feedback', [
            'name' => $_SESSION['user_name'],
            'success' => isset($_GET['sent']),
            'error' => null,
            'active' => 'feedback',
        ]);
    }

    // POST /feedback/submit
    public function submit(): void
    {
        $this->requireLoggedIn();

        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if ($subject === '' || $message === '') {
            $this->view('customer/feedback', [
                'name' => $_SESSION['user_name'],
                'success' => false,
                'error' => 'Please fill in both the subject and message.',
                'active' => 'feedback',
            ]);
            return;
        }

        $this->feedbackModel->create((int) $_SESSION['user_id'], $subject, $message);
        $this->redirect('/feedback?sent=1');
    }

    // GET /superadmin/feedback — superadmin-facing list
    public function indexForSuperadmin(): void
    {
        $this->requireRole('superadmin');

        $this->view('superadmin/feedback', [
            'name' => $_SESSION['user_name'],
            'feedbackList' => $this->feedbackModel->all(),
            'active' => 'feedback',
        ]);
    }

    // POST /superadmin/feedback/mark-reviewed
    public function markReviewed(): void
    {
        $this->requireRole('superadmin');

        $id = (int) ($_POST['id'] ?? 0);
        $this->feedbackModel->markReviewed($id);
        $this->redirect('/superadmin/feedback');
    }
}
