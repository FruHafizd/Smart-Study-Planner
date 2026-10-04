<!DOCTYPE html>
<html>
<head>
    <title>Grup - Smart Study Planner</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php $halaman = 'grup'; require __DIR__ . '/../layouts/nav.php'; ?>

    <main class="p-6 max-w-3xl mx-auto">
        <h2 class="text-2xl font-semibold mb-4">Grup Kolaborasi</h2>

        <?php if (!empty($error)): ?>
            <p class="text-red-600 bg-red-50 border border-red-200 rounded p-3 mb-4"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <form method="POST" action="/grup" class="bg-white p-4 rounded shadow space-y-2">
                <p class="font-medium text-sm mb-2">Buat Grup Baru</p>
                <input type="text" name="nama_grup" placeholder="Nama Grup" required class="border rounded px-3 py-2 w-full">
                <input type="text" name="nama_mk" placeholder="Mata Kuliah (opsional)" class="border rounded px-3 py-2 w-full">
                <textarea name="deskripsi" placeholder="Deskripsi (opsional)" class="border rounded px-3 py-2 w-full" rows="2"></textarea>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full">Buat Grup</button>
            </form>

            <form method="POST" action="/grup/gabung" class="bg-white p-4 rounded shadow space-y-2">
                <p class="font-medium text-sm mb-2">Gabung Pakai Kode</p>
                <input type="text" name="kode_undangan" placeholder="Kode Undangan (8 karakter)" required
                       maxlength="8" class="border rounded px-3 py-2 w-full uppercase">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 w-full">Gabung</button>
            </form>
        </div>

        <div class="bg-white rounded shadow divide-y">
            <?php if (empty($daftarGrup)): ?>
                <p class="p-4 text-gray-500">Kamu belum tergabung di grup manapun.</p>
            <?php endif; ?>

            <?php foreach ($daftarGrup as $g): ?>
                <a href="/grup/detail?id=<?= (int) $g['id'] ?>" class="p-4 flex justify-between items-center hover:bg-gray-50 block">
                    <div>
                        <p class="font-medium"><?= htmlspecialchars($g['nama_grup']) ?></p>
                        <p class="text-sm text-gray-500">
                            <?= htmlspecialchars($g['nama_mk'] ?? '-') ?> ·
                            Kode: <?= htmlspecialchars($g['kode_undangan']) ?> ·
                            <?= $g['peran'] === 'KETUA' ? 'Ketua' : 'Anggota' ?>
                        </p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>