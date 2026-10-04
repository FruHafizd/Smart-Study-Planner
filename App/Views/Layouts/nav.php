<?php
require_once __DIR__ . '/../../models/Notifikasi.php';
$jumlahNotifBelumDibaca = (new Notifikasi())->hitungBelumDibaca($_SESSION['user_id']);
?>
<nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
    <h1 class="text-xl font-bold text-blue-700">Smart Study Planner</h1>
    <div class="flex items-center gap-4">
        <a href="/dashboard" class="<?= $halaman === 'dashboard' ? 'text-blue-700 font-medium' : 'text-gray-700 hover:underline' ?>">Dashboard</a>
        <a href="/matakuliah" class="<?= $halaman === 'matakuliah' ? 'text-blue-700 font-medium' : 'text-gray-700 hover:underline' ?>">Mata Kuliah</a>
        <a href="/jadwal" class="<?= $halaman === 'jadwal' ? 'text-blue-700 font-medium' : 'text-gray-700 hover:underline' ?>">Jadwal</a>
        <a href="/tugas" class="<?= $halaman === 'tugas' ? 'text-blue-700 font-medium' : 'text-gray-700 hover:underline' ?>">Tugas</a>
        <a href="/pomodoro" class="<?= $halaman === 'pomodoro' ? 'text-blue-700 font-medium' : 'text-gray-700 hover:underline' ?>">Pomodoro</a>
        <a href="/alokasi" class="<?= $halaman === 'alokasi' ? 'text-blue-700 font-medium' : 'text-gray-700 hover:underline' ?>">Alokasi</a>
        <a href="/grup" class="<?= $halaman === 'grup' ? 'text-blue-700 font-medium' : 'text-gray-700 hover:underline' ?>">Grup</a>
        <a href="/kalender" class="<?= $halaman === 'kalender' ? 'text-blue-700 font-medium' : 'text-gray-700 hover:underline' ?>">Kalender</a>

        <a href="/notifikasi" class="relative text-gray-700 hover:text-blue-700">
            🔔
            <?php if ($jumlahNotifBelumDibaca > 0): ?>
                <span class="absolute -top-2 -right-2 bg-red-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                    <?= $jumlahNotifBelumDibaca > 9 ? '9+' : $jumlahNotifBelumDibaca ?>
                </span>
            <?php endif; ?>
        </a>

        <span class="text-gray-300">|</span>
        <span class="text-gray-700">Halo, <?= htmlspecialchars($_SESSION['nama']) ?></span>
        <a href="/logout" class="text-red-600 hover:underline">Logout</a>
    </div>
</nav>