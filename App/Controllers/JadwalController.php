<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/JadwalKuliah.php';
require_once __DIR__ . '/../models/MataKuliah.php';

class JadwalController extends Controller
{
    private JadwalKuliah $jadwalModel;
    private MataKuliah $mataKuliahModel;

    public function __construct()
    {
        parent::__construct();
        $this->jadwalModel = new JadwalKuliah();
        $this->mataKuliahModel = new MataKuliah();
    }

    // Tampilkan daftar jadwal + form tambah
    public function index(): void
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'];
        $jadwal = $this->jadwalModel->getAllByUser($userId);
        $mataKuliah = $this->mataKuliahModel->getAllByUser($userId);

        $this->view('jadwal/index', [
            'jadwal' => $jadwal,
            'mataKuliah' => $mataKuliah,
        ]);
    }

    // Proses tambah jadwal — sesuai flowchart 3.2
    public function store(): void
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'];
        $mataKuliahId = (int) ($_POST['mata_kuliah_id'] ?? 0);
        $hari = $_POST['hari'] ?? '';
        $jamMulai = $_POST['jam_mulai'] ?? '';
        $jamSelesai = $_POST['jam_selesai'] ?? '';

        $error = null;

        // Validasi 1: jam_selesai > jam_mulai?
        if ($jamMulai === '' || $jamSelesai === '' || $jamSelesai <= $jamMulai) {
            $error = 'Jam selesai harus lebih besar dari jam mulai.';
        }

        // Validasi 2: bentrok jadwal lain?
        if ($error === null && $this->jadwalModel->adaBentrok($userId, $hari, $jamMulai, $jamSelesai)) {
            $error = 'Jadwal bentrok dengan jadwal lain di hari yang sama.';
        }

        if ($error !== null) {
            $jadwal = $this->jadwalModel->getAllByUser($userId);
            $mataKuliah = $this->mataKuliahModel->getAllByUser($userId);

            $this->view('jadwal/index', [
                'jadwal' => $jadwal,
                'mataKuliah' => $mataKuliah,
                'error' => $error,
            ]);
            return;
        }

        $this->jadwalModel->create($mataKuliahId, $hari, $jamMulai, $jamSelesai);

        $this->redirect('/jadwal');
    }

    public function destroy(): void
    {
        $this->requireLogin();

        $id = (int) ($_POST['id'] ?? 0);
        $this->jadwalModel->delete($id, $_SESSION['user_id']);

        $this->redirect('/jadwal');
    }
}