<!DOCTYPE html>
<html>
<head>
    <title>Jadwal Kuliah - Smart Study Planner</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php $halaman = 'jadwal'; require __DIR__ . '/../layouts/nav.php'; ?>

    <main class="p-6 max-w-3xl mx-auto">
        <h2 class="text-2xl font-semibold mb-4">Jadwal Kuliah</h2>

        <?php if (!empty($error)): ?>
            <p class="text-red-600 bg-red-50 border border-red-200 rounded p-3 mb-4"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if (empty($mataKuliah)): ?>
            <p class="bg-yellow-50 border border-yellow-200 text-yellow-700 rounded p-3 mb-4">
                Tambahkan mata kuliah dulu sebelum membuat jadwal.
                <a href="/matakuliah" class="underline">Ke halaman Mata Kuliah</a>
            </p>
        <?php else: ?>
            <form method="POST" action="/jadwal" class="bg-white p-4 rounded shadow mb-6 flex gap-3 flex-wrap">
                <select name="mata_kuliah_id" required class="border rounded px-3 py-2 flex-1 min-w-[180px]">
                    <?php foreach ($mataKuliah as $mk): ?>
                        <option value="<?= (int) $mk['id'] ?>"><?= htmlspecialchars($mk['nama_mk']) ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="hari" required class="border rounded px-3 py-2">
                    <?php foreach (['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $hari): ?>
                        <option value="<?= $hari ?>"><?= $hari ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="time" name="jam_mulai" required class="border rounded px-3 py-2">
                <input type="time" name="jam_selesai" required class="border rounded px-3 py-2">

                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Tambah Jadwal
                </button>
            </form>
        <?php endif; ?>

        <div class="bg-white rounded shadow divide-y">
            <?php if (empty($jadwal)): ?>
                <p class="p-4 text-gray-500">Belum ada jadwal.</p>
            <?php endif; ?>

            <?php foreach ($jadwal as $j): ?>
                <div class="p-4 flex justify-between items-center">
                    <div>
                        <p class="font-medium"><?= htmlspecialchars($j['nama_mk']) ?></p>
                        <p class="text-sm text-gray-500">
                            <?= htmlspecialchars($j['hari']) ?>,
                            <?= substr($j['jam_mulai'], 0, 5) ?> - <?= substr($j['jam_selesai'], 0, 5) ?>
                        </p>
                    </div>
                    <form method="POST" action="/jadwal/hapus"
                          onsubmit="return confirm('Hapus jadwal ini?')">
                        <input type="hidden" name="id" value="<?= (int) $j['id'] ?>">
                        <button type="submit" class="text-red-600 hover:underline text-sm">Hapus</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>