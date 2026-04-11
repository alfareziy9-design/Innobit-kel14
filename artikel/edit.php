<?php
$pageTitle = 'Edit Artikel - InnoBit';
require_once '../includes/admin_check.php';
require_once '../functions/artikel.php';
require_once '../functions/kategori.php';
require_once '../includes/header.php';

$id = $_GET['id'] ?? 0;
$article = getArticleById((int)$id);
$categories = getAllCategories();
$errors = [];
$successMessage = '';

if (!$article) {
    header('Location: ../admin/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = updateArticle((int)$id, $_POST, $_FILES);

    if ($result['success']) {
        $successMessage = $result['message'];
        $article = getArticleById((int)$id);
    } else {
        $errors = $result['errors'];
    }
}

include '../includes/navbar.php';
?>

<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="bg-white rounded-2xl shadow-sm border p-6 md:p-8">
        <h1 class="text-2xl md:text-3xl font-bold mb-6">Edit Artikel</h1>

        <?php if (!empty($errors)) : ?>
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-xl p-4">
                <ul class="list-disc pl-5">
                    <?php foreach ($errors as $error) : ?>
                        <li><?= htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($successMessage) : ?>
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 rounded-xl p-4">
                <?= htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>

        <form id="articleForm" action="" method="POST" enctype="multipart/form-data" class="space-y-5">
            <div>
                <label for="title" class="block mb-2 font-medium">Judul Artikel</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="<?= htmlspecialchars($_POST['title'] ?? $article['title']); ?>"
                    class="w-full border rounded-xl px-4 py-3"
                >
            </div>

            <div>
                <label for="category_id" class="block mb-2 font-medium">Kategori</label>
                <select id="category_id" name="category_id" class="w-full border rounded-xl px-4 py-3">
                    <option value="">Pilih kategori</option>
                    <?php
                    $selectedCategory = $_POST['category_id'] ?? $article['category_id'];
                    foreach ($categories as $category) :
                    ?>
                        <option value="<?= $category['id']; ?>" <?= ($selectedCategory == $category['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="summary" class="block mb-2 font-medium">Ringkasan</label>
                <textarea
                    id="summary"
                    name="summary"
                    rows="3"
                    class="w-full border rounded-xl px-4 py-3"
                ><?= htmlspecialchars($_POST['summary'] ?? $article['summary']); ?></textarea>
            </div>

            <div>
                <label for="content" class="block mb-2 font-medium">Isi Artikel</label>
                <textarea
                    id="content"
                    name="content"
                    rows="8"
                    class="w-full border rounded-xl px-4 py-3"
                ><?= htmlspecialchars($_POST['content'] ?? $article['content']); ?></textarea>
            </div>

            <div>
                <label class="block mb-2 font-medium">Thumbnail Saat Ini</label>
                <?php if (!empty($article['thumbnail'])) : ?>
                    <img
                        src="../uploads/artikel/<?= htmlspecialchars($article['thumbnail']); ?>"
                        alt="<?= htmlspecialchars($article['title']); ?>"
                        class="w-full max-w-xs rounded-xl border mb-3"
                    >
                <?php else : ?>
                    <p class="text-slate-500 mb-3">Belum ada thumbnail.</p>
                <?php endif; ?>

                <label for="thumbnail" class="block mb-2 font-medium">Ganti Thumbnail</label>
                <input
                    type="file"
                    id="thumbnail"
                    name="thumbnail"
                    accept=".jpg,.jpeg,.png,.webp"
                    class="w-full border rounded-xl px-4 py-3 bg-white"
                >
            </div>

            <div>
                <label for="status" class="block mb-2 font-medium">Status</label>
                <?php $selectedStatus = $_POST['status'] ?? $article['status']; ?>
                <select id="status" name="status" class="w-full border rounded-xl px-4 py-3">
                    <option value="published" <?= ($selectedStatus === 'published') ? 'selected' : ''; ?>>Published</option>
                    <option value="draft" <?= ($selectedStatus === 'draft') ? 'selected' : ''; ?>>Draft</option>
                </select>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="bg-blue-600 text-white px-5 py-3 rounded-xl hover:bg-blue-700">
                    Update Artikel
                </button>
                <a href="../admin/dashboard.php" class="bg-slate-200 text-slate-700 px-5 py-3 rounded-xl hover:bg-slate-300">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>