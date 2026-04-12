<?php
require_once __DIR__ . '/../functions/auth.php';
$user = getAuthUser();
?>

<nav class="bg-white shadow-sm border-b">
     <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">

        <div class="flex items-center gap-2">
            <img src="/Innobit-Kel14/assets/img/logo_Innobit.png" alt="logo" class="w-10 h-10">
            <a href="/Innobit-Kel14/index.php" class="text-2xl font-bold text-lime-500">
                InnoBit
            </a>
        </div>

        <div class="flex flex-wrap items-center gap-3 text-sm">
            <a href="/Innobit-Kel14/index.php" class="hover:underline hover:text-lime-500">Home</a>

            <?php if ($user && $user['role'] === 'admin') : ?>
                <a href="/Innobit-Kel14/admin/dashboard.php" class="hover:underline hover:text-lime-500">Dashboard Admin</a>
            <?php endif; ?>

            <?php if ($user) : ?>
                <span class="text-slate-600">
                    Halo, <strong><?= htmlspecialchars($user['name']); ?></strong>
                </span>
                <a href="/Innobit-Kel14/logout.php" class="bg-red-500 text-white px-3 py-2 rounded-lg hover:bg-red-600">
                    Keluar
                </a>
            <?php else : ?>
                <a href="/Innobit-Kel14/login.php" class="hover:underline hover:text-lime-500">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>