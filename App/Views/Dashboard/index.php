<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Smart Study Planner</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php $halaman = 'dashboard'; require __DIR__ . '/../layouts/nav.php'; ?>

    <main class="p-6 max-w-5xl mx-auto">
        <h2 class="text-2xl font-semibold mb-1">Dashboard</h2>
        <p class="text-gray-500 mb-6">Halo, <?= htmlspecialchars($nama) ?>! Ini ringkasan aktivitas kamu.</p>

        <!-- Kartu ringkasan -->
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded shadow p-4">
                <p class="text-sm text-gray-500">Tugas Minggu Ini</p>
                <p class="text-2xl font-bold text-blue-700"><?= (int) $bebanMinggu['jumlah_tugas'] ?></p>
                <p class="text-xs text-gray-400"><?= (int) $bebanMinggu['total_menit'] ?> menit estimasi</p>
            </div>

            <div class="bg-white rounded shadow p-4">
                <p class="text-sm text-gray-500">Ketepatan Waktu</p>
                <p class="text-2xl font-bold text-green-600"><?= $ketepatan['persen_tepat_waktu'] ?>%</p>
                <p class="text-xs text-gray-400"><?= $ketepatan['total_selesai'] ?> tugas selesai</p>
            </div>

            <div class="bg-white rounded shadow p-4">
                <p class="text-sm text-gray-500">Realisasi Belajar</p>
                <p class="text-2xl font-bold text-purple-600">
                    <?= $evaluasiAlokasi['total_realisasi'] ?> / <?= $evaluasiAlokasi['total_rencana'] ?>
                </p>
                <p class="text-xs text-gray-400">menit minggu ini</p>
            </div>

            <div class="bg-white rounded shadow p-4">
                <p class="text-sm text-gray-500">Tugas Grup Aktif</p>
                <p class="text-2xl font-bold text-orange-600"><?= count($progresGrup) ?></p>
                <p class="text-xs text-gray-400">belum selesai</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <!-- Grafik produktivitas 7 hari -->
            <div class="bg-white rounded shadow p-4">
                <p class="font-medium mb-3">Waktu Belajar 7 Hari Terakhir</p>
                <canvas id="chartProduktivitas" height="150"></canvas>
            </div>

            <!-- Tugas grup yang perlu perhatian -->
            <div class="bg-white rounded shadow p-4">
                <p class="font-medium mb-3">Tugas Grup Perlu Dikerjakan</p>
                <?php if (empty($progresGrup)): ?>
                    <p class="text-sm text-gray-400">Tidak ada tugas grup yang tertunda. 🎉</p>
                <?php else: ?>
                    <div class="space-y-2">
                        <?php foreach ($progresGrup as $g): ?>
                            <div class="flex justify-between text-sm border-b pb-2">
                                <div>
                                    <p class="font-medium"><?= htmlspecialchars($g['judul']) ?></p>
                                    <p class="text-xs text-gray-400"><?= htmlspecialchars($g['nama_grup']) ?></p>
                                </div>
                                <span class="text-xs text-gray-500"><?= date('d M', strtotime($g['deadline'])) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        // Siapkan data 7 hari terakhir, termasuk hari yang tidak ada datanya (isi 0)
        const dataProduktivitas = <?= json_encode($produktivitas) ?>;

        const labelHari = [];
        const nilaiMenit = [];

        for (let i = 6; i >= 0; i--) {
            const tgl = new Date();
            tgl.setDate(tgl.getDate() - i);
            const tglString = tgl.toISOString().split('T')[0]; // format YYYY-MM-DD

            labelHari.push(tgl.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric' }));
            nilaiMenit.push(dataProduktivitas[tglString] || 0);
        }

        new Chart(document.getElementById('chartProduktivitas'), {
            type: 'bar',
            data: {
                labels: labelHari,
                datasets: [{
                    label: 'Menit Belajar',
                    data: nilaiMenit,
                    backgroundColor: '#3b82f6',
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>
</body>
</html>