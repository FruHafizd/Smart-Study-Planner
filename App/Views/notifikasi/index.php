<!DOCTYPE html>
<html>
<head>
    <title>Notifikasi - Smart Study Planner</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php $halaman = 'notifikasi'; require __DIR__ . '/../layouts/nav.php'; ?>

    <main class="p-6 max-w-2xl mx-auto">
        <h2 class="text-2xl font-semibold mb-4">Notifikasi</h2>

        <div class="bg-white rounded shadow divide-y">
            <?php if (empty($daftar)): ?>
                <p class="p-4 text-gray-500">Belum ada notifikasi.</p>
            <?php endif; ?>

            <?php
                $ikonJenis = [
                    'KELAS' => '📘', 'DEADLINE_TUGAS' => '📝',
                    'DEADLINE_GRUP' => '👥', 'GRUP' => '👥', 'SISTEM' => 'ℹ️',
                ];
            ?>

            <?php foreach ($daftar as $n): ?>
                <div class="p-4 flex gap-3">
                    <span class="text-xl"><?= $ikonJenis[$n['jenis']] ?? 'ℹ️' ?></span>
                    <div>
                        <p class="font-medium text-sm"><?= htmlspecialchars($n['judul']) ?></p>
                        <p class="text-sm text-gray-500"><?= htmlspecialchars($n['pesan']) ?></p>
                        <p class="text-xs text-gray-400 mt-1"><?= date('d M Y, H:i', strtotime($n['created_at'])) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>