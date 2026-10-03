<!DOCTYPE html>
<html>
<head>
    <title>Tugas - Smart Study Planner</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php $halaman = 'tugas'; require __DIR__ . '/../layouts/nav.php'; ?>

    <main class="p-6 max-w-4xl mx-auto">
        <h2 class="text-2xl font-semibold mb-4">Tugas</h2>

        <?php if (!empty($error)): ?>
            <p class="text-red-600 bg-red-50 border border-red-200 rounded p-3 mb-4"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if (empty($mataKuliah)): ?>
            <p class="bg-yellow-50 border border-yellow-200 text-yellow-700 rounded p-3 mb-4">
                Tambahkan mata kuliah dulu sebelum membuat tugas.
                <a href="/matakuliah" class="underline">Ke halaman Mata Kuliah</a>
            </p>
        <?php else: ?>
            <form method="POST" action="/tugas" class="bg-white p-4 rounded shadow mb-6 grid grid-cols-2 gap-3">
                <input type="text" name="judul" placeholder="Judul Tugas" required
                       class="border rounded px-3 py-2 col-span-2">

                <select name="mata_kuliah_id" required class="border rounded px-3 py-2">
                    <?php foreach ($mataKuliah as $mk): ?>
                        <option value="<?= (int) $mk['id'] ?>"><?= htmlspecialchars($mk['nama_mk']) ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="date" name="deadline" required class="border rounded px-3 py-2">

                <select name="prioritas" required class="border rounded px-3 py-2">
                    <option value="">Pilih Prioritas</option>
                    <option value="1">1 - Sangat Rendah</option>
                    <option value="2">2 - Rendah</option>
                    <option value="3">3 - Sedang</option>
                    <option value="4">4 - Tinggi</option>
                    <option value="5">5 - Sangat Tinggi</option>
                </select>

                <input type="number" name="estimasi_menit" placeholder="Estimasi (menit)" min="1" required
                       class="border rounded px-3 py-2">

                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 col-span-2">
                    Tambah Tugas
                </button>
            </form>
        <?php endif; ?>

        <div class="bg-white rounded shadow divide-y">
            <?php if (empty($daftar)): ?>
                <p class="p-4 text-gray-500">Belum ada tugas.</p>
            <?php endif; ?>

            <?php foreach ($daftar as $t): ?>
                <?php
                    $warnaStatus = match ($t['status']) {
                        'SELESAI' => 'bg-green-100 text-green-700',
                        'PROSES'  => 'bg-blue-100 text-blue-700',
                        default   => 'bg-gray-100 text-gray-700',
                    };
                ?>
                <div class="p-4 flex justify-between items-center">
                    <div>
                        <p class="font-medium"><?= htmlspecialchars($t['judul']) ?></p>
                        <p class="text-sm text-gray-500">
                            <?= htmlspecialchars($t['nama_mk']) ?> ·
                            Deadline: <?= htmlspecialchars($t['deadline']) ?> ·
                            Prioritas <?= (int) $t['prioritas'] ?> ·
                            <?= (int) $t['estimasi_menit'] ?> menit
                        </p>
                        <span class="inline-block mt-1 text-xs px-2 py-1 rounded <?= $warnaStatus ?>">
                            <?= htmlspecialchars($t['status']) ?>
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        <form method="POST" action="/tugas/status">
                            <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                            <select name="status" onchange="this.form.submit()" class="border rounded px-2 py-1 text-sm">
                                <option value="BELUM" <?= $t['status'] === 'BELUM' ? 'selected' : '' ?>>BELUM</option>
                                <option value="PROSES" <?= $t['status'] === 'PROSES' ? 'selected' : '' ?>>PROSES</option>
                                <option value="SELESAI" <?= $t['status'] === 'SELESAI' ? 'selected' : '' ?>>SELESAI</option>
                            </select>
                        </form>

                        <form method="POST" action="/tugas/hapus" onsubmit="return confirm('Hapus tugas ini?')">
                            <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                            <button type="submit" class="text-red-600 hover:underline text-sm">Hapus</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>