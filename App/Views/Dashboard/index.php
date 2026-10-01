<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Smart Study Planner</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
        <h1 class="text-xl font-bold text-blue-700">Smart Study Planner</h1>
        <div class="flex items-center gap-4">
            <span class="text-gray-700">Halo, <?= htmlspecialchars($nama) ?></span>
            <a href="/logout" class="text-red-600 hover:underline">Logout</a>
        </div>
    </nav>

    <main class="p-6">
        <h2 class="text-2xl font-semibold mb-4">Dashboard</h2>
        <p class="text-gray-600">Selamat datang kembali! Modul tugas, jadwal, dan statistik akan muncul di sini.</p>
    </main>
</body>
</html>