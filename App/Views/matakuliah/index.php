<!DOCTYPE html>
<html>
<head>
    <title>Mata Kuliah - Smart Study Planner</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php $halaman = 'matakuliah'; require __DIR__ . '/../layouts/nav.php'; ?>

    <main class="p-6 max-w-3xl mx-auto">
        <h2 class="text-2xl font-semibold mb-4">Mata Kuliah</h2>

        <?php if (!empty($error)): ?>
            <p class="text-red-600 bg-red-50 border border-red-200 rounded p-3 mb-4"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="/matakuliah" class="bg-white p-4 rounded shadow mb-6 flex gap-3 flex-wrap">
            <input type="text" name="nama_mk" placeholder="Nama Mata Kuliah" required
                   class="border rounded px-3 py-2 flex-1 min-w-[200px]">
            <input type="number" name="sks" placeholder="SKS" min="1" required
                   class="border rounded px-3 py-2 w-24">
            <select name="bobot_prioritas" class="border rounded px-3 py-2">
                <option value="1">Prioritas 1 (Rendah)</option>
                <option value="2">Prioritas 2</option>
                <option value="3" selected>Prioritas 3 (Sedang)</option>
                <option value="4">Prioritas 4</option>
                <option value="5">Prioritas 5 (Tinggi)</option>
            </select>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Tambah
            </button>
        </form>

        <div class="bg-white rounded shadow divide-y">
            <?php if (empty($daftar)): ?>
                <p class="p-4 text-gray-500">Belum ada mata kuliah.</p>
            <?php endif; ?>

            <?php foreach ($daftar as $mk): ?>
                <div class="p-4 flex justify-between items-center">
                    <div>
                        <p class="font-medium"><?= htmlspecialchars($mk['nama_mk']) ?></p>
                        <p class="text-sm text-gray-500">
                            <?= (int) $mk['sks'] ?> SKS · Prioritas <?= (int) $mk['bobot_prioritas'] ?>
                        </p>
                    </div>
                    <form method="POST" action="/matakuliah/hapus"
                          onsubmit="return confirm('Hapus mata kuliah ini?')">
                        <input type="hidden" name="id" value="<?= (int) $mk['id'] ?>">
                        <button type="submit" class="text-red-600 hover:underline text-sm">Hapus</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>