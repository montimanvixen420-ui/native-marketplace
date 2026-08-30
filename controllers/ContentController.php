<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Content.php';

class ContentController extends Controller
{
    private Content $contentModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->contentModel = new Content();
    }

    // GET /superadmin/content
    public function index(): void
    {
        $this->requireRole('superadmin');

        $type = $_GET['type'] ?? '';
        $items = $this->contentModel->getAll($type);

        $this->view('superadmin/content/index', [
            'name' => $_SESSION['user_name'],
            'items' => $items,
            'typeFilter' => $type,
            'active' => 'content',
        ]);
    }

    // GET /superadmin/content/create
    public function create(): void
    {
        $this->requireRole('superadmin');

        $this->view('superadmin/content/create', [
            'name' => $_SESSION['user_name'],
            'error' => null,
            'active' => 'content',
        ]);
    }

    // POST /superadmin/content/store
    public function store(): void
    {
        $this->requireRole('superadmin');

        [$valid, $data, $error] = $this->validate($_POST);

        if (!$valid) {
            $this->view('superadmin/content/create', [
                'name' => $_SESSION['user_name'],
                'error' => $error,
                'active' => 'content',
            ]);
            return;
        }

        $this->contentModel->create($data['type'], $data['title'], $data['body'], $data['image_url'], $data['is_active']);
        $this->redirect('/superadmin/content');
    }

    // GET /superadmin/content/{id}/edit
    public function edit(int $id): void
    {
        $this->requireRole('superadmin');

        $item = $this->contentModel->findById($id);
        if (!$item) {
            $this->redirect('/superadmin/content');
            return;
        }

        $this->view('superadmin/content/edit', [
            'name' => $_SESSION['user_name'],
            'item' => $item,
            'error' => null,
            'active' => 'content',
        ]);
    }

    // POST /superadmin/content/{id}/update
    public function update(int $id): void
    {
        $this->requireRole('superadmin');

        [$valid, $data, $error] = $this->validate($_POST);

        if (!$valid) {
            $item = $this->contentModel->findById($id);
            $this->view('superadmin/content/edit', [
                'name' => $_SESSION['user_name'],
                'item' => $item,
                'error' => $error,
                'active' => 'content',
            ]);
            return;
        }

        $this->contentModel->update($id, $data['type'], $data['title'], $data['body'], $data['image_url'], $data['is_active']);
        $this->redirect('/superadmin/content');
    }

    // POST /superadmin/content/{id}/toggle
    public function toggle(int $id): void
    {
        $this->requireRole('superadmin');
        $this->contentModel->toggleActive($id);
        $this->redirect('/superadmin/content');
    }

    // POST /superadmin/content/{id}/delete
    public function delete(int $id): void
    {
        $this->requireRole('superadmin');
        $this->contentModel->delete($id);
        $this->redirect('/superadmin/content');
    }

    /**
     * Shared validation for both create and update.
     * Returns [isValid, cleanedData, errorMessage].
     */
    private function validate(array $input): array
    {
        $type = $input['type'] ?? '';
        $title = trim($input['title'] ?? '');
        $body = trim($input['body'] ?? '');
        $imageUrl = trim($input['image_url'] ?? '');
        $isActive = isset($input['is_active']);

        $allowedTypes = [Content::TYPE_BANNER, Content::TYPE_ANNOUNCEMENT, Content::TYPE_SITE_TEXT];

        if (!in_array($type, $allowedTypes, true) || $title === '') {
            return [false, [], 'Please select a type and enter a title.'];
        }

        return [true, [
            'type' => $type,
            'title' => $title,
            'body' => $body !== '' ? $body : null,
            'image_url' => $imageUrl !== '' ? $imageUrl : null,
            'is_active' => $isActive,
        ], null];
    }
}