<?php
require_once '../functions/artikel.php';
require_once '../includes/header.php';

$slug = $_GET['slug'] ?? '';
$article = getArticleBySlug($slug);

include '../includes/navbar.php';
?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <?php if ($article) : ?>
        <article class="bg-white rounded-2xl shadow-sm border overflow-hidden">
            <?php if (!empty($article['thumbnail'])) : ?>
                <img
                    src="../uploads/artikel/<?= htmlspecialchars($article['thumbnail']); ?>"
                    alt="<?= htmlspecialchars($article['title']); ?>"
                    class="w-full h-72 md:h-96 object-cover"
                >
            <?php endif; ?>

            <div class="p-6 md:p-8">
                <div class="flex flex-wrap items-center gap-3 mb-4">
                    <span class="text-sm font-semibold bg-blue-100 text-blue-700 px-3 py-1 rounded-full">
                        <?= htmlspecialchars($article['category_name']); ?>
                    </span>
                    <span class="text-sm text-slate-500">
                        <?= date('d M Y H:i', strtotime($article['created_at'])); ?>
                    </span>
                </div>

                <h1 class="text-3xl md:text-4xl font-bold mb-4">
                    <?= htmlspecialchars($article['title']); ?>
                </h1>

                <p class="text-slate-600 text-lg mb-6">
                    <?= htmlspecialchars($article['summary']); ?>
                </p>

                <div class="text-sm text-slate-500 mb-6">
                    Ditulis oleh <strong><?= htmlspecialchars($article['author_name']); ?></strong>
                </div>

                <div class="prose max-w-none text-slate-700 leading-8">
                    <?= nl2br(htmlspecialchars($article['content'])); ?>
                </div>

                <div class="mt-8">
                    <a
                        href="../index.php"
                        class="inline-block bg-slate-800 text-white px-5 py-3 rounded-xl hover:bg-slate-900"
                    >
                        Kembali ke Home
                    </a>
                </div>
            </div>
        </article>
    <?php else : ?>
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-2xl p-6">
            Artikel tidak ditemukan.
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>