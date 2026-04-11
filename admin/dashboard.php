<?php
$pageTitle = 'Panel Admin - InnoBit';
require_once '../includes/admin_check.php';
require_once '../functions/auth.php';
require_once '../functions/artikel.php';
require_once '../config/database.php';
require_once '../includes/header.php';

$flash = getFlashMessage();
$user = getAuthUser();
$articles = getAllArticlesForAdmin();

$articleCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM articles"))['total'];
$categoryCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM categories"))['total'];
$userCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"))['total'];

include '../includes/navbar.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2">Dashboard Admin</h1>
        <p class="text-slate-600">Halo, <?= htmlspecialchars($user['name']); ?>. Kelola artikel microlearning dari sini.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-2xl border shadow-sm p-6">
            <h2 class="text-sm text-slate-500 mb-2">Total Artikel</h2>
            <p class="text-3xl font-bold text-blue-600"><?= $articleCount; ?></p>
        </div>

        <div class="bg-white rounded-2xl border shadow-sm p-6">
            <h2 class="text-sm text-slate-500 mb-2">Total Kategori</h2>
            <p class="text-3xl font-bold text-green-600"><?= $categoryCount; ?></p>
        </div>

        <div class="bg-white rounded-2xl border shadow-sm p-6">
            <h2 class="text-sm text-slate-500 mb-2">Total User</h2>
            <p class="text-3xl font-bold text-purple-600"><?= $userCount; ?></p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border shadow-sm p-6 mb-8">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <h2 class="text-xl font-semibold">Daftar Artikel</h2>
            <div class="flex gap-3">
                <a href="../artikel/create.php" class="bg-blue-600 text-white px-4 py-3 rounded-xl hover:bg-blue-700">
                    Tambah Artikel
                </a>
                <a href="../kategori/index.php" class="bg-green-600 text-white px-4 py-3 rounded-xl hover:bg-green-700">
                    Kelola Kategori
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-left">
                        <th class="p-3">No</th>
                        <th class="p-3">Judul</th>
                        <th class="p-3">Kategori</th>
                        <th class="p-3">Author</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Tanggal</th>
                        <th class="p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($flash) : ?>
    <div class="mb-4 rounded-xl p-4 border <?= $flash['type'] === 'success' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700'; ?>">
        <?= htmlspecialchars($flash['message']); ?>
    </div>
<?php endif; ?>
                    <?php if (!empty($articles)) : ?>
                        <?php foreach ($articles as $index => $article) : ?>
                            <tr class="border-b">
                                <td class="p-3"><?= $index + 1; ?></td>
                                <td class="p-3 font-medium"><?= htmlspecialchars($article['title']); ?></td>
                                <td class="p-3"><?= htmlspecialchars($article['category_name']); ?></td>
                                <td class="p-3"><?= htmlspecialchars($article['author_name']); ?></td>
                                <td class="p-3">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $article['status'] === 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                                        <?= htmlspecialchars($article['status']); ?>
                                    </span>
                                </td>
                                <td class="p-3"><?= date('d M Y', strtotime($article['created_at'])); ?></td>
                                <td class="p-3">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="../artikel/detail.php?slug=<?= urlencode($article['slug']); ?>" class="bg-slate-200 text-slate-700 px-3 py-2 rounded-lg hover:bg-slate-300">
                                            Detail
                                        </a>
                                        <a href="../artikel/edit.php?id=<?= $article['id']; ?>" class="bg-yellow-400 text-white px-3 py-2 rounded-lg hover:bg-yellow-500">
                                            Edit
                                        </a>
                                        <a href="../artikel/delete.php?id=<?= $article['id']; ?>" class="btn-delete bg-red-500 text-white px-3 py-2 rounded-lg hover:bg-red-600">
                                            Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7" class="p-6 text-center text-slate-500">Belum ada artikel. Silakan tambahkan artikel pertama.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>