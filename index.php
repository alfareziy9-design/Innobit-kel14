<?php
// Menampilkan error jika ada masalah tersembunyi
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pageTitle = 'Home - Innobit';

require_once __DIR__ . '/functions/artikel.php';
require_once __DIR__ . '/functions/kategori.php';
require_once __DIR__ . '/includes/header.php';

$search = $_GET['search'] ?? '';
$categoryId = $_GET['category_id'] ?? '';

$categories = getAllCategories();
$articles = getAllArticles($search, $categoryId);

include __DIR__ . '/includes/navbar.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row items-center justify-between gap-8 mb-12">
        <div class="md:w-1/2">
            <h1 class="text-3xl md:text-4xl font-bold text-slate-800 mb-2">
                Naik Level Tanpa Drama
            </h1>
            <h2 class="text-xl md:text-2xl text-slate-600 mb-2">
                Platform Praktis untuk Kuasai Skill Digital
            </h2>
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
                    class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-lime-500"
                >
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Filter Kategori</label>
                <select
                    name="category_id"
                    id="categoryFilter"
                    class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-lime-500"
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
                    class="w-full bg-lime-500 text-white rounded-lg px-4 py-3 hover:bg-lime-600 transition font-bold"
                    > Cari Artikel
                </button>
            </div>
        </form>
    </div>

    <?php if (count($articles) > 0) : ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($articles as $article) : ?>
                <div class="bg-white rounded-2xl shadow-sm border overflow-hidden hover:shadow-lg transition-shadow flex flex-col">
                    
                    <div class="w-full h-52 bg-slate-100 flex items-center justify-center overflow-hidden">
                        <?php if (!empty($article['thumbnail'])) : ?>
                            <?php 
                                $thumbnail = $article['thumbnail'];
                                $imagePath = (strpos($thumbnail, 'http') === 0) 
                                    ? htmlspecialchars($thumbnail) 
                                    : "uploads/artikel/" . htmlspecialchars($thumbnail);
                            ?>
                            <img
                                src="<?= $imagePath; ?>"
                                alt="<?= htmlspecialchars($article['title']); ?>"
                                /* Menggunakan object-contain agar gambar tidak terpotong */
                                class="w-full h-full object-contain p-2" 
                            >
                        <?php else : ?>
                            <div class="text-slate-400 text-sm italic">Tidak ada gambar</div>
                        <?php endif; ?>
                    </div>

                    <div class="p-6 flex flex-col flex-grow">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-xs font-bold bg-lime-100 text-lime-700 px-3 py-1 rounded-full uppercase tracking-wider">
                                <?= htmlspecialchars($article['category_name']); ?>
                            </span>
                            <span class="text-xs text-slate-400">
                                <?= date('d M Y', strtotime($article['created_at'])); ?>
                            </span>
                        </div>

                        <h2 class="text-lg font-bold mb-3 text-slate-800 line-clamp-2">
                            <?= htmlspecialchars($article['title']); ?>
                        </h2>

                        <p class="text-slate-500 text-sm mb-6 line-clamp-3 leading-relaxed">
                            <?= htmlspecialchars($article['summary']); ?>
                        </p>

                        <div class="mt-auto pt-4 border-t border-slate-50 flex items-center justify-between">
                            <span class="text-xs font-medium text-slate-400">
                                Oleh <span class="text-slate-600"><?= htmlspecialchars($article['author_name']); ?></span>
                            </span>
                            <a
                                href="artikel/detail.php?slug=<?= urlencode($article['slug']); ?>"
                                class="text-lime-600 text-sm font-bold hover:text-lime-700 flex items-center gap-1"
                            >
                                Baca Selengkapnya 
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="bg-slate-50 border border-dashed border-slate-200 text-slate-500 rounded-2xl p-12 text-center">
            <h2 class="text-xl font-bold mb-1">Ops! Artikel tidak ditemukan</h2>
            <p>Coba gunakan kata kunci lain atau cek kategori lainnya.</p>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>