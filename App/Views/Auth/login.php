<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Study Planner</title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="min-h-screen bg-navy-deep flex items-center justify-center px-4 py-10">

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-cream">Smart Study Planner</h1>
            <p class="text-cream/60 text-sm mt-1">Masuk untuk melanjutkan rencana belajarmu</p>
        </div>

        <div class="bg-navy-dark rounded-2xl shadow-xl border border-navy p-8">
            <h2 class="text-xl font-semibold text-cream mb-6">Login</h2>

            <?php if (!empty($error)): ?>
                <div class="mb-5 rounded-lg bg-navy border border-cream/30 px-4 py-3">
                    <p class="text-cream text-sm"><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" action="/login" class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-cream/80 mb-1.5">Email</label>
                    <input
                        type="email" id="email" name="email" required
                        class="w-full rounded-lg bg-navy border border-navy px-4 py-2.5 text-cream placeholder-cream/30 focus:outline-none focus:ring-2 focus:ring-cream/50 focus:border-cream/50 transition"
                        placeholder="nama@email.com">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-cream/80 mb-1.5">Password</label>
                    <input
                        type="password" id="password" name="password" required
                        class="w-full rounded-lg bg-navy border border-navy px-4 py-2.5 text-cream placeholder-cream/30 focus:outline-none focus:ring-2 focus:ring-cream/50 focus:border-cream/50 transition"
                        placeholder="Masukkan password">
                </div>

                <button
                    type="submit"
                    class="w-full mt-2 rounded-lg bg-cream text-navy-deep font-semibold py-2.5 hover:bg-cream/90 active:scale-[0.99] transition">
                    Login
                </button>
            </form>

            <p class="text-center text-sm text-cream/60 mt-6">
                Belum punya akun?
                <a href="/register" class="text-cream font-medium hover:underline">Daftar di sini</a>
            </p>
        </div>
    </div>

</body>
</html>
