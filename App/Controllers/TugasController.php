<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Tugas.php';
require_once __DIR__ . '/../models/MataKuliah.php';

class TugasController extends Controller
{
    private Tugas $tugasModel;
    private MataKuliah $mataKuliahModel;

    public function __construct()
    {
        parent::__construct();
        $this->tugasModel = new Tugas();
        $this->mataKuliahModel = new MataKuliah();
    }

    public function index(): void
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'];

        $this->view('tugas/index', [
            'daftar' => $this->tugasModel->getAllByUser($userId),
            'mataKuliah' => $this->mataKuliahModel->getAllByUser($userId),
        ]);
    }

    // Proses tambah tugas — validasi sesuai flowchart 3.5
    public function store(): void
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'];
        $mataKuliahId = (int) ($_POST['mata_kuliah_id'] ?? 0);
        $judul = trim($_POST['judul'] ?? '');
        $deadline = $_POST['deadline'] ?? '';
        $prioritas = (int) ($_POST['prioritas'] ?? 0);
        $estimasi = (int) ($_POST['estimasi_menit'] ?? 0);

        $error = null;

        // Validasi: judul, deadline, prioritas 1-5, estimasi > 0
        if ($judul === '') {
            $error = 'Judul tugas wajib diisi.';
        } elseif ($deadline === '') {
            $error = 'Deadline wajib diisi.';
        } elseif ($prioritas < 1 || $prioritas > 5) {
            $error = 'Prioritas harus antara 1 sampai 5.';
        } elseif ($estimasi <= 0) {
            $error = 'Estimasi waktu harus lebih dari 0 menit.';
        }

        if ($error !== null) {
            $this->view('tugas/index', [
                'daftar' => $this->tugasModel->getAllByUser($userId),
                'mataKuliah' => $this->mataKuliahModel->getAllByUser($userId),
                'error' => $error,
            ]);
            return;
        }

        $this->tugasModel->save($userId, $mataKuliahId, $judul, $deadline, $prioritas, $estimasi);

        $this->redirect('/tugas');
    }

    // Ubah status tugas (tombol toggle BELUM <-> SELESAI)
    public function updateStatus(): void
    {
        $this->requireLogin();

        $id = (int) ($_POST['id'] ?? 0);
        $statusBaru = $_POST['status'] ?? '';

        if (in_array($statusBaru, ['BELUM', 'PROSES', 'SELESAI'], true)) {
            $this->tugasModel->updateStatus($id, $_SESSION['user_id'], $statusBaru);
        }

        $this->redirect('/tugas');
    }

    public function destroy(): void
    {
        $this->requireLogin();

        $id = (int) ($_POST['id'] ?? 0);
        $this->tugasModel->delete($id, $_SESSION['user_id']);

        $this->redirect('/tugas');
    }
}