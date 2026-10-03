<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/MataKuliah.php';

class MataKuliahController extends Controller
{
    private MataKuliah $mataKuliahModel;

    public function __construct()
    {
        parent::__construct();
        $this->mataKuliahModel = new MataKuliah();
    }

    // Tampilkan daftar mata kuliah
    public function index(): void
    {
        $this->requireLogin();

        $daftar = $this->mataKuliahModel->getAllByUser($_SESSION['user_id']);

        $this->view('matakuliah/index', ['daftar' => $daftar]);
    }


    // Proses tambah mata kuliah baru
    public function store(): void
    {
        $this->requireLogin();

        $nama = trim($_POST['nama_mk'] ?? '');
        $sks = (int) ($_POST['sks'] ?? 0);
        $bobot = (int) ($_POST['bobot_prioritas'] ?? 1);

        if ($nama === '' || $sks <= 0) {
            $daftar = $this->mataKuliahModel->getAllByUser($_SESSION['user_id']);
            $this->view('matakuliah/index', [
                'daftar' => $daftar,
                'error' => 'Nama mata kuliah dan SKS wajib diisi dengan benar.',
            ]);
            return;
        }

        $this->mataKuliahModel->create($_SESSION['user_id'], $nama, $sks, $bobot);

        $this->redirect('/matakuliah');
    }

    // Proses hapus mata kuliah
    public function destroy(): void
    {
        $this->requireLogin();

        $id = (int) ($_POST['id'] ?? 0);
        $this->mataKuliahModel->delete($id, $_SESSION['user_id']);

        $this->redirect('/matakuliah');
    }
}