<nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
    <h1 class="text-xl font-bold text-blue-700">Smart Study Planner</h1>
    <div class="flex items-center gap-4">
        <a href="/dashboard" class="<?= $halaman === 'dashboard' ? 'text-blue-700 font-medium' : 'text-gray-700 hover:underline' ?>">Dashboard</a>
        <a href="/matakuliah" class="<?= $halaman === 'matakuliah' ? 'text-blue-700 font-medium' : 'text-gray-700 hover:underline' ?>">Mata Kuliah</a>
        <a href="/jadwal" class="<?= $halaman === 'jadwal' ? 'text-blue-700 font-medium' : 'text-gray-700 hover:underline' ?>">Jadwal</a>
        <span class="text-gray-300">|</span>
        <span class="text-gray-700">Halo, <?= htmlspecialchars($_SESSION['nama']) ?></span>
        <a href="/logout" class="text-red-600 hover:underline">Logout</a>
    </div>
</nav>