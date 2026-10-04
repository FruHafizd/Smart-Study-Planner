<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Tugas.php';
require_once __DIR__ . '/../models/MataKuliah.php';
require_once __DIR__ . '/../models/RencanaPengerjaan.php';

class TugasController extends Controller
{
    private Tugas $tugasModel;
    private MataKuliah $mataKuliahModel;
    private RencanaPengerjaan $rencanaModel;

    public function __construct()
    {
        parent::__construct();
        $this->tugasModel = new Tugas();
        $this->mataKuliahModel = new MataKuliah();
        $this->rencanaModel = new RencanaPengerjaan();
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

    public function susunUrutan(): void
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'];
        $metode = $_POST['metode'] ?? 'PRIORITAS';

        if (!in_array($metode, ['PRIORITAS', 'DEADLINE', 'DURASI_TERPENDEK'], true)) {
            $metode = 'PRIORITAS';
        }

         // 1. Ambil tugas yang masih aktif
        $tugasAktif = $this->tugasModel->getAktif($userId);

        if (empty($tugasAktif)) {
            $this->redirect('/tugas/urutan');
            return;
        }

         // 2. Urutkan sesuai metode yang dipilih (usort)
        switch ($metode) {
            case 'PRIORITAS':
            // prioritas tertinggi dulu (DESC), kalau sama -> deadline terdekat dulu (ASC)
            usort($tugasAktif, function ($a, $b) {
                if ($a['prioritas'] !== $b['prioritas']) {
                    return $b['prioritas'] <=> $a['prioritas'];
                }
                return strtotime($a['deadline']) <=> strtotime($b['deadline']);
            });
            break;
            
            case 'DEADLINE':
            // deadline terdekat dulu (ASC), kalau sama -> prioritas tertinggi dulu (DESC)
            usort($tugasAktif, function ($a, $b) {
                if ($a['deadline'] !== $b['deadline']) {
                    return strtotime($a['deadline']) <=> strtotime($b['deadline']);
                }
                return $b['prioritas'] <=> $a['prioritas'];
            });
            break;

            case 'DURASI_TERPENDEK':
            // estimasi menit tersingkat dulu (ASC), kalau sama -> deadline terdekat dulu (ASC)
            usort($tugasAktif, function ($a, $b) {
                if ($a['estimasi_menit'] !== $b['estimasi_menit']) {
                    return $a['estimasi_menit'] <=> $b['estimasi_menit'];
                }
                return strtotime($a['deadline']) <=> strtotime($b['deadline']);
            });
            break;
        }

        // 3. Hitung rencana_mulai & rencana_selesai berurutan dari waktu sekarang
        $waktuSekarang = new DateTime();
        $urutanTugas = [];
        $urutan = 1;

        foreach ($tugasAktif as $t) {
            $mulai = clone $waktuSekarang;
            $selesai = clone $waktuSekarang;
            $selesai->modify('+' . $t['estimasi_menit'] . ' minutes');

            $urutanTugas[] = [
                'tugas_id' => $t['id'],
                'urutan' => $urutan,
                'rencana_mulai' => $mulai->format('Y-m-d H:i:s'),
                'rencana_selesai' => $selesai->format('Y-m-d H:i:s'),
            ];

            // Tugas berikutnya mulai persis setelah tugas ini selesai
            $waktuSekarang = clone $selesai;
            $urutan++;
        }

         // 4. Simpan ke database (transaksi di dalam Model)
        $this->rencanaModel->simpan($userId, $metode, (new DateTime())->format('Y-m-d H:i:s'), $urutanTugas);

        $this->redirect('/tugas/urutan');
    }

    // Tampilkan halaman hasil susun urutan (Gantt Chart)
    public function halamanUrutan(): void
    {
        $this->requireLogin();

        $rencana = $this->rencanaModel->getRencanaTerbaru($_SESSION['user_id']);

        $this->view('tugas/urutan', ['rencana' => $rencana]);
    }



    
}