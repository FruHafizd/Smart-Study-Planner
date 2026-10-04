<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Notifikasi.php';

class NotifikasiController extends Controller
{
    private Notifikasi $notifikasiModel;

    public function __construct()
    {
        parent::__construct();
        $this->notifikasiModel = new Notifikasi();
    }

    public function index(): void
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'];

        // Setiap buka halaman ini, tandai semua sudah dibaca
        $this->notifikasiModel->tandaiSemuaDibaca($userId);

        $this->view('notifikasi/index', [
            'daftar' => $this->notifikasiModel->getByUser($userId),
        ]);
    }
}