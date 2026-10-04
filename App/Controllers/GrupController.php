<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Grup.php';
require_once __DIR__ . '/../models/TugasGrup.php';
require_once __DIR__ . '/../models/Komentar.php';
require_once __DIR__ . '/../models/LogAktivitas.php';

class GrupController extends Controller
{
    private Grup $grupModel;
    private TugasGrup $tugasGrupModel;
    private Komentar $komentarModel;
    private LogAktivitas $logModel;

    public function __construct()
    {
        parent::__construct();
        $this->grupModel = new Grup();
        $this->tugasGrupModel = new TugasGrup();
        $this->komentarModel = new Komentar();
        $this->logModel = new LogAktivitas();
    }

    // Daftar grup yang diikuti user
    public function index(): void
    {
        $this->requireLogin();

        $this->view('grup/index', [
            'daftarGrup' => $this->grupModel->getAllByUser($_SESSION['user_id']),
        ]);
    }

    // Buat grup baru
    public function store(): void
    {
        $this->requireLogin();

        $nama = trim($_POST['nama_grup'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '') ?: null;
        $namaMk = trim($_POST['nama_mk'] ?? '') ?: null;

        if ($nama === '') {
            $this->view('grup/index', [
                'daftarGrup' => $this->grupModel->getAllByUser($_SESSION['user_id']),
                'error' => 'Nama grup wajib diisi.',
            ]);
            return;
        }

        $this->grupModel->create($_SESSION['user_id'], $nama, $deskripsi, $namaMk);

        $this->redirect('/grup');
    }

    // Gabung ke grup pakai kode undangan
    public function gabung(): void
    {
        $this->requireLogin();

        $kode = strtoupper(trim($_POST['kode_undangan'] ?? ''));
        $userId = $_SESSION['user_id'];

        $grup = $this->grupModel->findByKode($kode);

        $error = null;

        if (!$grup) {
            $error = 'Kode undangan tidak ditemukan.';
        } elseif ($this->grupModel->sudahJadiAnggota($grup['id'], $userId)) {
            $error = 'Kamu sudah menjadi anggota grup ini.';
        }

        if ($error !== null) {
            $this->view('grup/index', [
                'daftarGrup' => $this->grupModel->getAllByUser($userId),
                'error' => $error,
            ]);
            return;
        }

        $this->grupModel->gabung($grup['id'], $userId);

        $this->redirect('/grup');
    }

    // Detail 1 grup: daftar anggota + daftar tugas grup
    public function detail(): void
    {
        $this->requireLogin();

        $grupId = (int) ($_GET['id'] ?? 0);
        $userId = $_SESSION['user_id'];

        // Pastikan user ini memang anggota grup tersebut
        if (!$this->grupModel->sudahJadiAnggota($grupId, $userId)) {
            $this->redirect('/grup');
            return;
        }

        $this->view('grup/detail', [
            'grup' => $this->grupModel->getById($grupId),
            'anggota' => $this->grupModel->getAnggota($grupId),
            'daftarTugas' => $this->tugasGrupModel->getAllByGrup($grupId),
            'peranSaya' => $this->grupModel->peranUser($grupId, $userId),
            'tugasGrupModel' => $this->tugasGrupModel,
            'komentarModel' => $this->komentarModel,
        ]);
    }

    // Buat tugas grup baru + assign anggota
    public function storeTugas(): void
    {
        $this->requireLogin();

        $grupId = (int) ($_POST['grup_id'] ?? 0);
        $userId = $_SESSION['user_id'];

        // Hanya KETUA yang boleh buat tugas grup
        if ($this->grupModel->peranUser($grupId, $userId) !== 'KETUA') {
            $this->redirect('/grup/detail?id=' . $grupId);
            return;
        }

        $judul = trim($_POST['judul'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '') ?: null;
        $deadline = $_POST['deadline'] ?? '';
        $prioritas = (int) ($_POST['prioritas'] ?? 3);
        $anggotaIds = $_POST['anggota'] ?? []; // array dari checkbox

        if ($judul === '' || $deadline === '' || empty($anggotaIds)) {
            $this->redirect('/grup/detail?id=' . $grupId);
            return;
        }

        $this->tugasGrupModel->create($grupId, $userId, $judul, $deskripsi, $deadline, $prioritas, $anggotaIds);

        $this->redirect('/grup/detail?id=' . $grupId);
    }

    // Ubah status pengerjaan tugas grup -- sesuai flowchart: "anggota itu sendiri atau ketua?"
    public function updateStatusTugas(): void
    {
        $this->requireLogin();

        $tugasGrupId = (int) ($_POST['tugas_grup_id'] ?? 0);
        $grupId = (int) ($_POST['grup_id'] ?? 0);
        $targetUserId = (int) ($_POST['user_id'] ?? 0); // anggota yang statusnya mau diubah
        $statusBaru = $_POST['status'] ?? '';
        $pelakuId = $_SESSION['user_id'];

        $peranPelaku = $this->grupModel->peranUser($grupId, $pelakuId);

        // Boleh ubah HANYA jika: dia sendiri anggotanya, ATAU dia KETUA grup
        $bolehUbah = ($pelakuId === $targetUserId) || ($peranPelaku === 'KETUA');

        if (!$bolehUbah || !in_array($statusBaru, ['BELUM', 'PROSES', 'SELESAI'], true)) {
            $this->redirect('/grup/detail?id=' . $grupId);
            return;
        }

        $this->tugasGrupModel->updateStatus($tugasGrupId, $targetUserId, $statusBaru);
        $this->logModel->catat($grupId, $pelakuId, 'UBAH_STATUS', "Status tugas diubah jadi $statusBaru");

        $this->redirect('/grup/detail?id=' . $grupId);
    }

    // Kirim komentar
    public function storeKomentar(): void
    {
        $this->requireLogin();

        $tugasGrupId = (int) ($_POST['tugas_grup_id'] ?? 0);
        $grupId = (int) ($_POST['grup_id'] ?? 0);
        $isi = trim($_POST['isi'] ?? '');

        if ($isi !== '') {
            $this->komentarModel->create($tugasGrupId, $grupId, $_SESSION['user_id'], null, $isi);
        }

        $this->redirect('/grup/detail?id=' . $grupId);
    }
}