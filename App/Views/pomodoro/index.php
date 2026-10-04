<!DOCTYPE html>
<html>
<head>
    <title>Pomodoro - Smart Study Planner</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php $halaman = 'pomodoro'; require __DIR__ . '/../layouts/nav.php'; ?>

    <main class="p-6 max-w-md mx-auto">
        <h2 class="text-2xl font-semibold mb-4">Pomodoro</h2>

        <div class="bg-white rounded shadow p-6 text-center">
            <select id="mataKuliahId" class="border rounded px-3 py-2 w-full mb-2">
                <option value="">(Tanpa mata kuliah)</option>
                <?php foreach ($mataKuliah as $mk): ?>
                    <option value="<?= (int) $mk['id'] ?>"><?= htmlspecialchars($mk['nama_mk']) ?></option>
                <?php endforeach; ?>
            </select>

            <select id="tugasId" class="border rounded px-3 py-2 w-full mb-4">
                <option value="">(Tanpa tugas)</option>
                <?php foreach ($tugas as $t): ?>
                    <option value="<?= (int) $t['id'] ?>"><?= htmlspecialchars($t['judul']) ?></option>
                <?php endforeach; ?>
            </select>

            <div id="timerDisplay" class="text-5xl font-bold text-blue-700 mb-2">
                <?= str_pad($pengaturan['durasi_fokus_menit'], 2, '0', STR_PAD_LEFT) ?>:00
            </div>
            <p id="statusLabel" class="text-gray-500 mb-6">Sesi Fokus</p>

            <div class="flex gap-3 justify-center">
                <button id="btnMulai" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Mulai
                </button>
                <button id="btnStop" class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700 hidden">
                    Stop
                </button>
            </div>

            <p id="infoSesi" class="text-sm text-gray-400 mt-4"></p>
        </div>
    </main>

    <script>
        // Data dari PHP dikirim ke JavaScript sebagai variabel biasa
        const durasiFokusMenit = <?= (int) $pengaturan['durasi_fokus_menit'] ?>;
        const durasiPendekMenit = <?= (int) $pengaturan['durasi_istirahat_pendek_menit'] ?>;
        const durasiPanjangMenit = <?= (int) $pengaturan['durasi_istirahat_panjang_menit'] ?>;

        let sisaDetik = durasiFokusMenit * 60;
        let totalDetikAwal = sisaDetik;
        let timerInterval = null;
        let sesiId = null;
        let jenisSekarang = 'FOKUS';

        const timerDisplay = document.getElementById('timerDisplay');
        const statusLabel = document.getElementById('statusLabel');
        const btnMulai = document.getElementById('btnMulai');
        const btnStop = document.getElementById('btnStop');
        const infoSesi = document.getElementById('infoSesi');

        function formatWaktu(detik) {
            const menit = Math.floor(detik / 60);
            const sisa = detik % 60;
            return String(menit).padStart(2, '0') + ':' + String(sisa).padStart(2, '0');
        }

        function updateDisplay() {
            timerDisplay.textContent = formatWaktu(sisaDetik);
        }

        // Mulai sesi: kirim request ke server (AJAX), simpan sesi_id yang dikembalikan
        async function mulaiSesi() {
            const mataKuliahId = document.getElementById('mataKuliahId').value;
            const tugasId = document.getElementById('tugasId').value;

            const durasiMenit = jenisSekarang === 'FOKUS' ? durasiFokusMenit
                               : jenisSekarang === 'ISTIRAHAT_PENDEK' ? durasiPendekMenit
                               : durasiPanjangMenit;

            sisaDetik = durasiMenit * 60;
            totalDetikAwal = sisaDetik;

            const formData = new FormData();
            formData.append('mata_kuliah_id', mataKuliahId);
            formData.append('tugas_id', tugasId);
            formData.append('jenis', jenisSekarang);
            formData.append('durasi_menit', durasiMenit);

            const response = await fetch('/pomodoro/mulai', {
                method: 'POST',
                body: formData,
            });
            const data = await response.json();

            if (data.sukses) {
                sesiId = data.sesi_id;
                btnMulai.classList.add('hidden');
                btnStop.classList.remove('hidden');
                statusLabel.textContent = jenisSekarang === 'FOKUS' ? 'Sesi Fokus' : 'Sesi Istirahat';

                timerInterval = setInterval(tick, 1000);
            }
        }

        function tick() {
            sisaDetik--;
            updateDisplay();

            if (sisaDetik <= 0) {
                selesaiSesi();
            }
        }

        // Timer habis secara normal -> beri tahu server sesi SELESAI
        async function selesaiSesi() {
            clearInterval(timerInterval);

            const formData = new FormData();
            formData.append('sesi_id', sesiId);
            formData.append('detik', totalDetikAwal);

            const response = await fetch('/pomodoro/selesai', {
                method: 'POST',
                body: formData,
            });
            const data = await response.json();

            btnMulai.classList.remove('hidden');
            btnStop.classList.add('hidden');

            if (jenisSekarang === 'FOKUS') {
                infoSesi.textContent = 'Sesi fokus ke-' + data.jumlah_sesi_hari_ini + ' hari ini selesai!';
                jenisSekarang = data.saran_istirahat; // server yang tentukan: istirahat pendek/panjang
            } else {
                infoSesi.textContent = 'Istirahat selesai. Lanjut fokus lagi?';
                jenisSekarang = 'FOKUS';
            }

            statusLabel.textContent = 'Klik Mulai untuk sesi berikutnya (' + jenisSekarang + ')';
        }

        // User klik Stop sebelum waktu habis
        async function stopSesi() {
            clearInterval(timerInterval);

            const detikBerjalan = totalDetikAwal - sisaDetik;

            const formData = new FormData();
            formData.append('sesi_id', sesiId);
            formData.append('detik', detikBerjalan);

            await fetch('/pomodoro/stop', {
                method: 'POST',
                body: formData,
            });

            btnMulai.classList.remove('hidden');
            btnStop.classList.add('hidden');
            statusLabel.textContent = 'Dihentikan';

            sisaDetik = totalDetikAwal;
            updateDisplay();
        }

        btnMulai.addEventListener('click', mulaiSesi);
        btnStop.addEventListener('click', stopSesi);

        updateDisplay();
    </script>
</body>
</html>