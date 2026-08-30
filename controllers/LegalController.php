<?php

require_once __DIR__ . '/../core/Controller.php';

class LegalController extends Controller
{
    public function terms(): void
    {
        $this->view('legal/terms');
    }

    public function privacy(): void
    {
        $this->view('legal/privacy');
    }
}
