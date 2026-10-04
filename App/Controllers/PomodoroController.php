<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/SesiBelajar.php';
require_once __DIR__ . '/../models/Pengaturan.php';
require_once __DIR__ . '/../models/MataKuliah.php';
require_once __DIR__ . '/../models/Tugas.php';

class PomodoroController extends Controller
{
    private SesiBelajar $sesiModel;
    private Pengaturan $pengaturanModel;
    private MataKuliah $mataKuliahModel;
    private Tugas $tugasModel;

    public function __construct()
    {
        parent::__construct();
        $this->sesiModel = new SesiBelajar();
        $this->pengaturanModel = new Pengaturan();
        $this->mataKuliahModel = new MataKuliah();
        $this->tugasModel = new Tugas();
    }

    // Tampilkan halaman Pomodoro
    public function index(): void
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'];
        $pengaturan = $this->pengaturanModel->getByUser($userId);

        $this->view('pomodoro/index', [
            'pengaturan' => $pengaturan,
            'mataKuliah' => $this->mataKuliahModel->getAllByUser($userId),
            'tugas' => $this->tugasModel->getAktif($userId),
        ]);
    }

    // AJAX: mulai sesi baru -> balas JSON
    public function mulai(): void
    {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];
        $mataKuliahId = !empty($_POST['mata_kuliah_id']) ? (int) $_POST['mata_kuliah_id'] : null;
        $tugasId = !empty($_POST['tugas_id']) ? (int) $_POST['tugas_id'] : null;
        $jenis = $_POST['jenis'] ?? 'FOKUS';
        $durasi = (int) ($_POST['durasi_menit'] ?? 25);

        if (!in_array($jenis, ['FOKUS', 'ISTIRAHAT_PENDEK', 'ISTIRAHAT_PANJANG'], true)) {
            $this->json(['sukses' => false, 'pesan' => 'Jenis sesi tidak valid.']);
            return;
        }

        $sesiId = $this->sesiModel->mulai($userId, $mataKuliahId, $tugasId, $jenis, $durasi);

        $this->json(['sukses' => true, 'sesi_id' => $sesiId]);
    }

    // AJAX: sesi selesai normal (timer habis di JS) -> balas JSON
    public function selesai(): void
    {
        $this->requireLogin();

        $sesiId = (int) ($_POST['sesi_id'] ?? 0);
        $detik = (int) ($_POST['detik'] ?? 0);

        $this->sesiModel->selesai($sesiId, $_SESSION['user_id'], $detik);

        // Hitung apakah sudah waktunya istirahat panjang
        $jumlahSesi = $this->sesiModel->hitungSesiFokusHariIni($_SESSION['user_id']);
        $sesiSebelumPanjang = (int) ($this->pengaturanModel->getByUser($_SESSION['user_id'])['sesi_sebelum_istirahat_panjang'] ?? 4);

        $saranIstirahat = ($jumlahSesi % $sesiSebelumPanjang === 0) ? 'ISTIRAHAT_PANJANG' : 'ISTIRAHAT_PENDEK';

        $this->json([
            'sukses' => true,
            'jumlah_sesi_hari_ini' => $jumlahSesi,
            'saran_istirahat' => $saranIstirahat,
        ]);
    }

    // AJAX: user klik Stop sebelum waktu habis -> balas JSON
    public function stop(): void
    {
        $this->requireLogin();

        $sesiId = (int) ($_POST['sesi_id'] ?? 0);
        $detik = (int) ($_POST['detik'] ?? 0);

        $this->sesiModel->hentikan($sesiId, $_SESSION['user_id'], $detik);

        $this->json(['sukses' => true]);
    }
}