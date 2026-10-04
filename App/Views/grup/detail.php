<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($grup['nama_grup']) ?> - Smart Study Planner</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php $halaman = 'grup'; require __DIR__ . '/../layouts/nav.php'; ?>

    <main class="p-6 max-w-3xl mx-auto">
        <a href="/grup" class="text-sm text-blue-600 hover:underline">&larr; Kembali ke daftar grup</a>

        <div class="bg-white rounded shadow p-4 my-4">
            <h2 class="text-2xl font-semibold"><?= htmlspecialchars($grup['nama_grup']) ?></h2>
            <p class="text-gray-500 text-sm mt-1"><?= htmlspecialchars($grup['deskripsi'] ?? '') ?></p>
            <p class="text-sm mt-2">
                Kode undangan: <span class="font-mono bg-gray-100 px-2 py-1 rounded"><?= htmlspecialchars($grup['kode_undangan']) ?></span>
            </p>

            <div class="mt-3 flex gap-2 flex-wrap">
                <?php foreach ($anggota as $a): ?>
                    <span class="text-xs bg-gray-100 px-2 py-1 rounded">
                        <?= htmlspecialchars($a['nama']) ?> <?= $a['peran'] === 'KETUA' ? '👑' : '' ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($peranSaya === 'KETUA'): ?>
            <div class="bg-white rounded shadow p-4 mb-6">
                <p class="font-medium mb-2">Buat Tugas Grup</p>
                <form method="POST" action="/grup/tugas" class="space-y-2">
                    <input type="hidden" name="grup_id" value="<?= (int) $grup['id'] ?>">
                    <input type="text" name="judul" placeholder="Judul Tugas" required class="border rounded px-3 py-2 w-full">
                    <textarea name="deskripsi" placeholder="Deskripsi" class="border rounded px-3 py-2 w-full" rows="2"></textarea>
                    <div class="flex gap-2">
                        <input type="datetime-local" name="deadline" required class="border rounded px-3 py-2 flex-1">
                        <select name="prioritas" class="border rounded px-3 py-2">
                            <?php for ($p = 1; $p <= 5; $p++): ?>
                                <option value="<?= $p ?>"><?= $p ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Assign ke anggota:</p>
                        <?php foreach ($anggota as $a): ?>
                            <label class="inline-flex items-center gap-1 mr-3 text-sm">
                                <input type="checkbox" name="anggota[]" value="<?= (int) $a['id'] ?>">
                                <?= htmlspecialchars($a['nama']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Buat Tugas</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="space-y-4">
            <?php foreach ($daftarTugas as $t): ?>
                <?php $anggotaTugas = $tugasGrupModel->getAnggotaTugas($t['id']); ?>
                <div class="bg-white rounded shadow p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-medium"><?= htmlspecialchars($t['judul']) ?></p>
                            <p class="text-sm text-gray-500">
                                Deadline: <?= date('d M Y, H:i', strtotime($t['deadline'])) ?> ·
                                Prioritas <?= (int) $t['prioritas'] ?>
                            </p>
                        </div>
                        <span class="text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded">
                            <?= (int) $t['total_selesai'] ?> / <?= (int) $t['total_anggota'] ?> selesai
                        </span>
                    </div>

                    <div class="mt-3 space-y-2">
                        <?php foreach ($anggotaTugas as $at): ?>
                            <?php
                                $bolehUbah = ($at['user_id'] == $_SESSION['user_id']) || ($peranSaya === 'KETUA');
                            ?>
                            <div class="flex justify-between items-center text-sm border-t pt-2">
                                <span><?= htmlspecialchars($at['nama']) ?></span>
                                <?php if ($bolehUbah): ?>
                                    <form method="POST" action="/grup/tugas/status">
                                        <input type="hidden" name="tugas_grup_id" value="<?= (int) $t['id'] ?>">
                                        <input type="hidden" name="grup_id" value="<?= (int) $grup['id'] ?>">
                                        <input type="hidden" name="user_id" value="<?= (int) $at['user_id'] ?>">
                                        <select name="status" onchange="this.form.submit()" class="border rounded px-2 py-1 text-xs">
                                            <option value="BELUM" <?= $at['status'] === 'BELUM' ? 'selected' : '' ?>>BELUM</option>
                                            <option value="PROSES" <?= $at['status'] === 'PROSES' ? 'selected' : '' ?>>PROSES</option>
                                            <option value="SELESAI" <?= $at['status'] === 'SELESAI' ? 'selected' : '' ?>>SELESAI</option>
                                        </select>
                                    </form>
                                <?php else: ?>
                                    <span class="text-xs text-gray-500"><?= htmlspecialchars($at['status']) ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php $komentar = $komentarModel->getByTugasGrup($t['id']); ?>
                    <div class="mt-3 pt-3 border-t">
                        <?php foreach ($komentar as $k): ?>
                            <p class="text-xs text-gray-600 mb-1">
                                <span class="font-medium"><?= htmlspecialchars($k['nama']) ?>:</span>
                                <?= htmlspecialchars($k['isi']) ?>
                            </p>
                        <?php endforeach; ?>

                        <form method="POST" action="/grup/komentar" class="flex gap-2 mt-2">
                            <input type="hidden" name="tugas_grup_id" value="<?= (int) $t['id'] ?>">
                            <input type="hidden" name="grup_id" value="<?= (int) $grup['id'] ?>">
                            <input type="text" name="isi" placeholder="Tulis komentar..." required
                                   class="border rounded px-3 py-1 text-sm flex-1">
                            <button type="submit" class="bg-gray-700 text-white px-3 py-1 rounded text-sm">Kirim</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>