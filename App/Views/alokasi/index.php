<!DOCTYPE html>
<html>
<head>
    <title>Alokasi Belajar - Smart Study Planner</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php $halaman = 'alokasi'; require __DIR__ . '/../layouts/nav.php'; ?>

    <main class="p-6 max-w-3xl mx-auto">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-semibold">Alokasi Jam Belajar</h2>
            <form method="POST" action="/alokasi/hitung">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 text-sm">
                    Hitung Alokasi Minggu Ini
                </button>
            </form>
        </div>

        <p class="text-sm text-gray-500 mb-4">Minggu mulai: <?= htmlspecialchars($mingguMulai) ?></p>

        <?php if (empty($data)): ?>
            <p class="bg-white p-4 rounded shadow text-gray-500">
                Belum ada alokasi. Klik "Hitung Alokasi Minggu Ini" untuk membagi waktu belajar otomatis.
            </p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($data as $d): ?>
                    <div class="bg-white rounded shadow p-4">
                        <div class="flex justify-between mb-2">
                            <span class="font-medium"><?= htmlspecialchars($d['nama_mk']) ?></span>
                            <span class="text-sm text-gray-500">
                                <?= $d['realisasi_menit'] ?> / <?= $d['rencana_menit'] ?> menit
                            </span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-4">
                            <div class="bg-green-500 h-4 rounded-full" style="width: <?= $d['persen'] ?>%"></div>
                        </div>
                        <p class="text-xs text-gray-400 mt-1"><?= $d['persen'] ?>% tercapai</p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>