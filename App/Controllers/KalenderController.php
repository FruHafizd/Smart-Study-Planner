<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/JadwalKuliah.php';
require_once __DIR__ . '/../models/Tugas.php';
require_once __DIR__ . '/../models/TugasGrup.php';
require_once __DIR__ . '/../models/KalenderEvent.php';

class KalenderController extends Controller
{
    private JadwalKuliah $jadwalModel;
    private Tugas $tugasModel;
    private TugasGrup $tugasGrupModel;
    private KalenderEvent $eventModel;

    public function __construct()
    {
        parent::__construct();
        $this->jadwalModel = new JadwalKuliah();
        $this->tugasModel = new Tugas();
        $this->tugasGrupModel = new TugasGrup();
        $this->eventModel = new KalenderEvent();
    }

    public function index(): void
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'];

        // Tentukan bulan yang ditampilkan (default: bulan sekarang)
        $tahun = (int) ($_GET['tahun'] ?? date('Y'));
        $bulan = (int) ($_GET['bulan'] ?? date('n'));

        $events = $this->kumpulkanEvent($userId, $tahun, $bulan);

        $this->view('kalender/index', [
            'tahun' => $tahun,
            'bulan' => $bulan,
            'events' => $events,
        ]);
    }

    // Ini jantung dari flowchart 3.3: gabungkan 4 sumber jadi 1 array event
    private function kumpulkanEvent(int $userId, int $tahun, int $bulan): array
    {
        $awalBulan = sprintf('%04d-%02d-01', $tahun, $bulan);
        $akhirBulan = date('Y-m-t', strtotime($awalBulan)); // 't' = tanggal terakhir bulan itu

        $events = []; // dikelompokkan per tanggal: ['2026-10-05' => [event1, event2, ...]]

        // 1. Jadwal kuliah (mingguan) -> ubah jadi tanggal nyata di bulan ini
        $semuaJadwal = $this->jadwalModel->getAllByUser($userId);
        $periode = new DatePeriod(
            new DateTime($awalBulan),
            new DateInterval('P1D'),
            (new DateTime($akhirBulan))->modify('+1 day')
        );

        foreach ($periode as $tanggal) {
            $namaHari = $this->namaHariIndonesia($tanggal->format('N'));

            foreach ($semuaJadwal as $j) {
                if ($j['hari'] === $namaHari) {
                    $tgl = $tanggal->format('Y-m-d');
                    $events[$tgl][] = [
                        'jenis' => 'KELAS',
                        'judul' => $j['nama_mk'],
                        'waktu' => substr($j['jam_mulai'], 0, 5),
                    ];
                }
            }
        }

        // 2. Tugas pribadi
        foreach ($this->tugasModel->getDeadlineRange($userId, $awalBulan, $akhirBulan) as $t) {
            $events[$t['deadline']][] = [
                'jenis' => 'TUGAS',
                'judul' => $t['judul'],
                'waktu' => null,
            ];
        }

        // 3. Tugas grup
        foreach ($this->tugasGrupModel->getDeadlineByAnggota($userId, $awalBulan, $akhirBulan) as $t) {
            $tgl = substr($t['deadline'], 0, 10);
            $events[$tgl][] = [
                'jenis' => 'TUGAS_GRUP',
                'judul' => $t['judul'],
                'waktu' => substr($t['deadline'], 11, 5),
            ];
        }

        // 4. Event custom (ujian, acara, libur, dll)
        foreach ($this->eventModel->getRange($userId, $awalBulan, $akhirBulan) as $e) {
            $tgl = substr($e['waktu_mulai'], 0, 10);
            $events[$tgl][] = [
                'jenis' => $e['jenis'],
                'judul' => $e['judul'],
                'waktu' => substr($e['waktu_mulai'], 11, 5),
            ];
        }

        return $events;
    }

    private function namaHariIndonesia(string $nomorHariIso): string
    {
        // ISO-8601: 1 = Senin, 7 = Minggu
        $map = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        return $map[(int) $nomorHariIso];
    }

     // Tambah event custom
    public function store(): void
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'];
        $judul = trim($_POST['judul'] ?? '');
        $jenis = $_POST['jenis'] ?? 'LAINNYA';
        $waktuMulai = $_POST['waktu_mulai'] ?? '';
        $deskripsi = trim($_POST['deskripsi'] ?? '') ?: null;

        if ($judul !== '' && $waktuMulai !== '') {
            $this->eventModel->create($userId, null, $judul, $deskripsi, $jenis, $waktuMulai, null);
        }

        $this->redirect('/kalender');
    }

}