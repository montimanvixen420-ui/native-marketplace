<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/ProhibitedItem.php';

class ProhibitedItemController extends Controller
{
    private ProhibitedItem $itemModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->itemModel = new ProhibitedItem();
    }

    // GET /superadmin/prohibited-items
    public function index(): void
    {
        $this->requireRole('superadmin');

        $this->view('superadmin/prohibited-items/index', [
            'name' => $_SESSION['user_name'],
            'items' => $this->itemModel->getAll(),
            'active' => 'settings',
        ]);
    }

    // GET /superadmin/prohibited-items/create
    public function create(): void
    {
        $this->requireRole('superadmin');

        $this->view('superadmin/prohibited-items/create', [
            'name' => $_SESSION['user_name'],
            'error' => null,
            'active' => 'settings',
        ]);
    }

    // POST /superadmin/prohibited-items/store
    public function store(): void
    {
        $this->requireRole('superadmin');

        $itemName = trim($_POST['item_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($itemName === '') {
            $this->view('superadmin/prohibited-items/create', [
                'name' => $_SESSION['user_name'],
                'error' => 'Please enter an item name.',
                'active' => 'settings',
            ]);
            return;
        }

        $this->itemModel->create($itemName, $description !== '' ? $description : null);
        $this->redirect('/superadmin/prohibited-items');
    }

    // GET /superadmin/prohibited-items/{id}/edit
    public function edit(int $id): void
    {
        $this->requireRole('superadmin');

        $item = $this->itemModel->findById($id);
        if (!$item) {
            $this->redirect('/superadmin/prohibited-items');
            return;
        }

        $this->view('superadmin/prohibited-items/edit', [
            'name' => $_SESSION['user_name'],
            'item' => $item,
            'error' => null,
            'active' => 'settings',
        ]);
    }

    // POST /superadmin/prohibited-items/{id}/update
    public function update(int $id): void
    {
        $this->requireRole('superadmin');

        $itemName = trim($_POST['item_name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($itemName === '') {
            $item = $this->itemModel->findById($id);
            $this->view('superadmin/prohibited-items/edit', [
                'name' => $_SESSION['user_name'],
                'item' => $item,
                'error' => 'Please enter an item name.',
                'active' => 'settings',
            ]);
            return;
        }

        $this->itemModel->update($id, $itemName, $description !== '' ? $description : null);
        $this->redirect('/superadmin/prohibited-items');
    }

    // POST /superadmin/prohibited-items/{id}/delete
    public function delete(int $id): void
    {
        $this->requireRole('superadmin');
        $this->itemModel->delete($id);
        $this->redirect('/superadmin/prohibited-items');
    }
}