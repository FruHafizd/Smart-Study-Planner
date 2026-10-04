<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/AlokasiBelajar.php';
require_once __DIR__ . '/../models/SesiBelajar.php';
require_once __DIR__ . '/../models/MataKuliah.php';
require_once __DIR__ . '/../models/Pengaturan.php';

class AlokasiController extends Controller
{
    private AlokasiBelajar $alokasiModel;
    private SesiBelajar $sesiModel;
    private MataKuliah $mataKuliahModel;
    private Pengaturan $pengaturanModel;

    public function __construct()
    {
        parent::__construct();
        $this->alokasiModel = new AlokasiBelajar();
        $this->sesiModel = new SesiBelajar();
        $this->mataKuliahModel = new MataKuliah();
        $this->pengaturanModel = new Pengaturan();
    }


    // Tentukan tanggal Senin dari minggu berjalan
    private function mingguMulaiSekarang(): string
    {
        $hariIni = new DateTime();
        $hariIni->modify('monday this week');
        return $hariIni->format('Y-m-d');
    }

    // Hitung alokasi -- sesuai flowchart 3.8
    public function hitung(): void
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'];
        $mingguMulai = $this->mingguMulaiSekarang();

        $pengaturan = $this->pengaturanModel->getByUser($userId);
        $targetMenit = (int) $pengaturan['target_belajar_mingguan_menit'];

        $daftarMK = $this->mataKuliahModel->getAllByUser($userId);

        if (empty($daftarMK)) {
            $this->redirect('/alokasi');
            return;
        }

        // 1. Hitung bobot tiap mata kuliah: sks x bobot_prioritas
        $totalBobot = 0;
        $bobotPerMK = [];

        foreach ($daftarMK as $mk) {
            $bobot = (int) $mk['sks'] * (int) $mk['bobot_prioritas'];
            $bobotPerMK[$mk['id']] = $bobot;
            $totalBobot += $bobot;
        }

        // 2. Bagi target waktu secara proporsional sesuai bobot masing-masing
        foreach ($daftarMK as $mk) {
            $bobot = $bobotPerMK[$mk['id']];
            $rencanaMenit = $totalBobot > 0 ? (int) round($targetMenit * $bobot / $totalBobot) : 0;

            $this->alokasiModel->upsert($userId, $mk['id'], $mingguMulai, $rencanaMenit);
        }

        $this->redirect('/alokasi');
    }

     // Tampilkan halaman rencana vs realisasi
    public function index(): void
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'];
        $mingguMulai = $this->mingguMulaiSekarang();

        $alokasi = $this->alokasiModel->getMingguIni($userId, $mingguMulai);
        $realisasi = $this->sesiModel->totalFokusPerMK($userId, $mingguMulai);

        // Gabungkan rencana + realisasi, hitung persen capaian
        $data = [];
        foreach ($alokasi as $a) {
            $menitTercapai = $realisasi[$a['mata_kuliah_id']] ?? 0;
            $persen = $a['rencana_menit'] > 0
                ? min(round($menitTercapai / $a['rencana_menit'] * 100), 100)
                : 0;

            $data[] = [
                'nama_mk' => $a['nama_mk'],
                'rencana_menit' => $a['rencana_menit'],
                'realisasi_menit' => $menitTercapai,
                'persen' => $persen,
            ];
        }

        $this->view('alokasi/index', [
            'data' => $data,
            'mingguMulai' => $mingguMulai,
        ]);
    }



}