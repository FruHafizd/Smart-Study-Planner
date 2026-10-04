<!DOCTYPE html>
<html>
<head>
    <title>Kalender - Smart Study Planner</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php $halaman = 'kalender'; require __DIR__ . '/../layouts/nav.php'; ?>

    <main class="p-6 max-w-5xl mx-auto">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-semibold">Kalender</h2>

            <div class="flex gap-2 items-center">
                <?php
                    // Hitung bulan sebelum & sesudah untuk tombol navigasi
                    $tsBulanIni = mktime(0, 0, 0, $bulan, 1, $tahun);
                    $bulanSebelum = ['bulan' => (int) date('n', strtotime('-1 month', $tsBulanIni)), 'tahun' => (int) date('Y', strtotime('-1 month', $tsBulanIni))];
                    $bulanSesudah = ['bulan' => (int) date('n', strtotime('+1 month', $tsBulanIni)), 'tahun' => (int) date('Y', strtotime('+1 month', $tsBulanIni))];

                    $namaBulan = ['', 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                ?>
                <a href="/kalender?bulan=<?= $bulanSebelum['bulan'] ?>&tahun=<?= $bulanSebelum['tahun'] ?>"
                   class="bg-white border rounded px-3 py-2 hover:bg-gray-50">&larr;</a>
                <span class="font-medium w-40 text-center"><?= $namaBulan[$bulan] ?> <?= $tahun ?></span>
                <a href="/kalender?bulan=<?= $bulanSesudah['bulan'] ?>&tahun=<?= $bulanSesudah['tahun'] ?>"
                   class="bg-white border rounded px-3 py-2 hover:bg-gray-50">&rarr;</a>
            </div>
        </div>

        <form method="POST" action="/kalender" class="bg-white p-4 rounded shadow mb-6 flex gap-3 flex-wrap items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Judul Event</label>
                <input type="text" name="judul" required class="border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Jenis</label>
                <select name="jenis" class="border rounded px-3 py-2">
                    <option value="UJIAN">Ujian</option>
                    <option value="ACARA">Acara</option>
                    <option value="LIBUR">Libur</option>
                    <option value="LAINNYA">Lainnya</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Waktu</label>
                <input type="datetime-local" name="waktu_mulai" required class="border rounded px-3 py-2">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Tambah Event</button>
        </form>

        <?php
            $warnaJenis = [
                'KELAS' => 'bg-blue-100 text-blue-700',
                'TUGAS' => 'bg-orange-100 text-orange-700',
                'TUGAS_GRUP' => 'bg-purple-100 text-purple-700',
                'UJIAN' => 'bg-red-100 text-red-700',
                'ACARA' => 'bg-green-100 text-green-700',
                'LIBUR' => 'bg-gray-200 text-gray-700',
                'LAINNYA' => 'bg-gray-100 text-gray-600',
            ];

            $jumlahHari = (int) date('t', $tsBulanIni);
            $hariPertama = (int) date('N', $tsBulanIni); // 1 = Senin ... 7 = Minggu
        ?>

        <div class="bg-white rounded shadow p-4">
            <div class="grid grid-cols-7 gap-2 text-center text-xs font-medium text-gray-500 mb-2">
                <?php foreach (['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $h): ?>
                    <div><?= $h ?></div>
                <?php endforeach; ?>
            </div>

            <div class="grid grid-cols-7 gap-2">
                <?php for ($i = 1; $i < $hariPertama; $i++): ?>
                    <div></div>
                <?php endfor; ?>

                <?php for ($tgl = 1; $tgl <= $jumlahHari; $tgl++): ?>
                    <?php
                        $tanggalFull = sprintf('%04d-%02d-%02d', $tahun, $bulan, $tgl);
                        $eventHariIni = $events[$tanggalFull] ?? [];
                        $isHariIni = $tanggalFull === date('Y-m-d');
                    ?>
                    <div class="border rounded p-1 min-h-[80px] text-xs <?= $isHariIni ? 'border-blue-400 bg-blue-50' : 'border-gray-100' ?>">
                        <p class="font-medium mb-1"><?= $tgl ?></p>
                        <?php foreach (array_slice($eventHariIni, 0, 3) as $e): ?>
                            <div class="<?= $warnaJenis[$e['jenis']] ?? 'bg-gray-100' ?> rounded px-1 py-0.5 mb-0.5 truncate" title="<?= htmlspecialchars($e['judul']) ?>">
                                <?= $e['waktu'] ? $e['waktu'] . ' ' : '' ?><?= htmlspecialchars($e['judul']) ?>
                            </div>
                        <?php endforeach; ?>
                        <?php if (count($eventHariIni) > 3): ?>
                            <p class="text-gray-400">+<?= count($eventHariIni) - 3 ?> lagi</p>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </main>
</body>
</html>