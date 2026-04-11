<?php
require_once __DIR__ . '/../functions/auth.php';
$user = getAuthUser();
?>

<nav class="bg-white shadow-sm border-b">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <a href="/Innobit-Kel14/index.php" class="text-xl font-bold text-blue-600">
            InnoBit
        </a>

        <div class="flex flex-wrap items-center gap-3 text-sm">
            <a href="/Innobit-Kel14/index.php" class="hover:text-blue-600">Home</a>

            <?php if ($user && $user['role'] === 'admin') : ?>
                <a href="/Innobit-Kel14/admin/dashboard.php" class="hover:text-blue-600">Dashboard</a>
                <a href="/Innobit-Kel14/artikel/create.php" class="hover:text-blue-600">Tambah Artikel</a>
                <a href="/Innobit-Kel14/kategori/index.php" class="hover:text-blue-600">Kategori</a>
            <?php endif; ?>

            <?php if ($user) : ?>
                <span class="text-slate-600">
                    Halo, <strong><?= htmlspecialchars($user['name']); ?></strong>
                </span>
                <a href="/Innobit-Kel14/logout.php" class="bg-red-500 text-white px-3 py-2 rounded-lg hover:bg-red-600">
                    Keluar
                </a>
            <?php else : ?>
                <a href="/Innobit-Kel14/login.php" class="hover:text-blue-600">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>