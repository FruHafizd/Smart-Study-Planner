<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Tugas.php';
require_once __DIR__ . '/../models/SesiBelajar.php';
require_once __DIR__ . '/../models/AlokasiBelajar.php';
require_once __DIR__ . '/../models/TugasGrup.php';

class DashboardController extends Controller
{
    private Tugas $tugasModel;
    private SesiBelajar $sesiModel;
    private AlokasiBelajar $alokasiModel;
    private TugasGrup $tugasGrupModel;

    public function __construct()
    {
        parent::__construct();
        $this->tugasModel = new Tugas();
        $this->sesiModel = new SesiBelajar();
        $this->alokasiModel = new AlokasiBelajar();
        $this->tugasGrupModel = new TugasGrup();
    }

    public function index(): void
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'];
        $mingguMulai = (new DateTime('monday this week'))->format('Y-m-d');

        // Kumpulkan semua statistik dari Model masing-masing modul
        $bebanMinggu = $this->tugasModel->bebanMingguIni($userId);
        $ketepatan = $this->tugasModel->statistikKetepatan($userId);
        $produktivitas = $this->sesiModel->produktivitas7Hari($userId);
        $realisasiPerMK = $this->sesiModel->totalFokusPerMK($userId, $mingguMulai);
        $evaluasiAlokasi = $this->alokasiModel->evaluasiMingguan($userId, $mingguMulai, $realisasiPerMK);
        $progresGrup = $this->tugasGrupModel->progresSaya($userId);

        $this->view('dashboard/index', [
            'nama' => $_SESSION['nama'],
            'bebanMinggu' => $bebanMinggu,
            'ketepatan' => $ketepatan,
            'produktivitas' => $produktivitas,
            'evaluasiAlokasi' => $evaluasiAlokasi,
            'progresGrup' => $progresGrup,
        ]);
    }
}