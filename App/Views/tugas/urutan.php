<!DOCTYPE html>
<html>
<head>
    <title>Susun Urutan - Smart Study Planner</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php $halaman = 'tugas'; require __DIR__ . '/../layouts/nav.php'; ?>

    <main class="p-6 max-w-4xl mx-auto">
        <h2 class="text-2xl font-semibold mb-4">Susun Urutan Pengerjaan</h2>

        <form method="POST" action="/tugas/urutan" class="bg-white p-4 rounded shadow mb-6 flex gap-3 items-end flex-wrap">
            <div>
                <label class="block text-sm text-gray-600 mb-1">Metode Pengurutan</label>
                <select name="metode" class="border rounded px-3 py-2">
                    <option value="PRIORITAS">Prioritas (tertinggi dulu)</option>
                    <option value="DEADLINE">Deadline (terdekat dulu)</option>
                    <option value="DURASI_TERPENDEK">Durasi Terpendek (dulu)</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Susun Urutan
            </button>
        </form>

        <?php if (!$rencana): ?>
            <p class="bg-white p-4 rounded shadow text-gray-500">
                Belum ada rencana. Pastikan ada tugas dengan status BELUM/PROSES, lalu klik "Susun Urutan".
            </p>
        <?php else: ?>

            <div class="bg-white rounded shadow p-4 mb-6">
                <p class="text-sm text-gray-500 mb-4">
                    Metode: <span class="font-medium"><?= htmlspecialchars($rencana['metode']) ?></span> ·
                    Total tugas: <?= (int) $rencana['total_tugas'] ?> ·
                    Dibuat: <?= htmlspecialchars($rencana['created_at']) ?>
                </p>

                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b">
                            <th class="pb-2">#</th>
                            <th class="pb-2">Judul Tugas</th>
                            <th class="pb-2">Mulai</th>
                            <th class="pb-2">Selesai</th>
                            <th class="pb-2">Prioritas</th>
                            <th class="pb-2">Deadline</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach ($rencana['detail'] as $d): ?>
                            <tr>
                                <td class="py-2"><?= (int) $d['urutan'] ?></td>
                                <td class="py-2"><?= htmlspecialchars($d['judul']) ?></td>
                                <td class="py-2"><?= date('d M, H:i', strtotime($d['rencana_mulai'])) ?></td>
                                <td class="py-2"><?= date('d M, H:i', strtotime($d['rencana_selesai'])) ?></td>
                                <td class="py-2"><?= (int) $d['prioritas'] ?></td>
                                <td class="py-2"><?= htmlspecialchars($d['deadline']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded shadow p-4">
                <h3 class="font-medium mb-4">Gantt Chart</h3>

                <?php
                    // Hitung total durasi keseluruhan (dari awal tugas pertama sampai akhir tugas terakhir)
                    // supaya setiap bar bisa dihitung lebar proporsionalnya dalam persen
                    $mulaiAwal = strtotime($rencana['detail'][0]['rencana_mulai']);
                    $selesaiAkhir = strtotime(end($rencana['detail'])['rencana_selesai']);
                    $totalDetik = max($selesaiAkhir - $mulaiAwal, 1); // hindari pembagian dengan 0

                    $warnaBar = ['bg-blue-500', 'bg-green-500', 'bg-purple-500', 'bg-orange-500', 'bg-pink-500'];
                ?>

                <div class="space-y-3">
                    <?php foreach ($rencana['detail'] as $i => $d): ?>
                        <?php
                            $mulaiTugas = strtotime($d['rencana_mulai']);
                            $selesaiTugas = strtotime($d['rencana_selesai']);

                            // Posisi bar dari kiri (persen) & lebar bar (persen), relatif terhadap total durasi
                            $offsetPersen = (($mulaiTugas - $mulaiAwal) / $totalDetik) * 100;
                            $lebarPersen = max((($selesaiTugas - $mulaiTugas) / $totalDetik) * 100, 2);
                            $warna = $warnaBar[$i % count($warnaBar)];
                        ?>
                        <div>
                            <p class="text-xs text-gray-600 mb-1"><?= htmlspecialchars($d['judul']) ?></p>
                            <div class="w-full bg-gray-100 rounded h-6 relative">
                                <div class="<?= $warna ?> h-6 rounded absolute"
                                     style="left: <?= $offsetPersen ?>%; width: <?= $lebarPersen ?>%;">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php endif; ?>
    </main>
</body>
</html>