<?php
$pageTitle = 'Home - Innobit';
require_once 'functions/artikel.php';
require_once 'functions/kategori.php';
require_once 'includes/header.php';

$search = $_GET['search'] ?? '';
$categoryId = $_GET['category_id'] ?? '';

$categories = getAllCategories();
$articles = getAllArticles($search, $categoryId);

include 'includes/navbar.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row items-center justify-between gap-8">
        <div class="md:w-1/2">
            <h1 class="text-3xl md:text-4xl font-bold text-slate-800 mb-2">
                Naik Level Tanpa Drama
            </h1>
            <h1 class="text-3xl md:text-2xl text-slate-800 mb-2">
                Platform Praktis untuk Kuasai Skill Digital
            </h1>
        </div>
    </div>
</div>


    <div class="bg-white rounded-2xl shadow-sm border p-4 md:p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">Cari Artikel</label>
                <input
                    type="text"
                    name="search"
                    value="<?= htmlspecialchars($search); ?>"
                    placeholder="Cari judul artikel"
                    class="w-full border rounded-none px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Filter Kategori</label>
                <select
                    name="category_id"
                    id="categoryFilter"
                    class="w-full border rounded-none px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $category) : ?>
                        <option value="<?= $category['id']; ?>" <?= ($categoryId == $category['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-end">
                <button
                    type="submit"
                    class="w-full bg-lime-500 text-white rounded-none px-4 py-3 hover:bg-lime-600 transition"
                    > Cari Artikel
                </button>
            </div>
        </form>
    </div>

    <?php if (count($articles) > 0) : ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($articles as $article) : ?>
                <div class="bg-white rounded-2xl shadow-sm border overflow-hidden hover:shadow-md transition">
                    <?php if (!empty($article['thumbnail'])) : ?>
                        <img
                            src="uploads/artikel/<?= htmlspecialchars($article['thumbnail']); ?>"
                            alt="<?= htmlspecialchars($article['title']); ?>"
                            class="w-full h-48 object-cover"
                        >
                    <?php else : ?>
                        <div class="w-full h-48 bg-slate-200 flex items-center justify-center text-slate-500">
                            Tidak ada gambar
                        </div>
                    <?php endif; ?>

                    <div class="p-5">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold bg-lime-500 text-white px-3 py-1 rounded-full">
                                <?= htmlspecialchars($article['category_name']); ?>
                            </span>
                            <span class="text-xs text-slate-500">
                                <?= date('d M Y', strtotime($article['created_at'])); ?>
                            </span>
                        </div>

                        <h2 class="text-xl font-bold mb-2 line-clamp-2">
                            <?= htmlspecialchars($article['title']); ?>
                        </h2>

                        <p class="text-slate-600 text-sm mb-4 line-clamp-3">
                            <?= htmlspecialchars($article['summary']); ?>
                        </p>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-500">
                                Oleh <?= htmlspecialchars($article['author_name']); ?>
                            </span>
                            <a
                                href="artikel/detail.php?slug=<?= urlencode($article['slug']); ?>"
                                class="text-lime-500 font-medium hover:underline"
                            >
                                Baca artikel
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-2xl p-6 text-center">
        <h2 class="text-lg font-semibold mb-2">Artikel tidak ditemukan</h2>
        <p>Coba gunakan kata kunci lain atau pilih kategori yang berbeda.</p>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>