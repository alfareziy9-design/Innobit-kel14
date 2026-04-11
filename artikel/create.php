<?php
$pageTitle = 'Tambah Artikel - InnoBit';
require_once '../includes/admin_check.php';
require_once '../functions/artikel.php';
require_once '../functions/kategori.php';
require_once '../functions/auth.php';
require_once '../includes/header.php';

$user = getAuthUser();
$categories = getAllCategories();
$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_POST['author_id'] = $user['id'];

     if (!isset($_FILES['thumbnail']) || $_FILES['thumbnail']['error'] === 4) {
        $errors[] = 'Thumbnail wajib diunggah.';
    }

    if (empty($errors)) {
        $result = createArticle($_POST, $_FILES);

    if ($result['success']) {
    setFlashMessage('success', $result['message']);
    header('Location: ../admin/dashboard.php');
    exit;
} else {
    $errors = $result['errors'];
        }
    }
}

include '../includes/navbar.php';
?>

<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="bg-white rounded-2xl shadow-sm border p-6 md:p-8">
        <h1 class="text-2xl md:text-3xl font-bold mb-6">Tambah Artikel</h1>

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
                    value="<?= htmlspecialchars($_POST['title'] ?? ''); ?>"
                    class="w-full border rounded-xl px-4 py-3"
                >
            </div>

            <div>
                <label for="category_id" class="block mb-2 font-medium">Kategori</label>
                <select id="category_id" name="category_id" class="w-full border rounded-xl px-4 py-3">
                    <option value="">Pilih kategori</option>
                    <?php foreach ($categories as $category) : ?>
                        <option value="<?= $category['id']; ?>" <?= (($_POST['category_id'] ?? '') == $category['id']) ? 'selected' : ''; ?>>
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
                ><?= htmlspecialchars($_POST['summary'] ?? ''); ?></textarea>
            </div>

            <div>
                <label for="content" class="block mb-2 font-medium">Isi Artikel</label>
                <textarea
                    id="content"
                    name="content"
                    rows="8"
                    class="w-full border rounded-xl px-4 py-3"
                ><?= htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
            </div>

            <div>
                <label for="thumbnail" class="block mb-2 font-medium">Thumbnail</label>
                <input
                    type="file"
                    id="thumbnail"
                    name="thumbnail"
                    accept=".jpg,.jpeg,.png,.webp"
                    class="w-full border rounded-xl px-4 py-3 bg-white"
                >
                <p class="text-sm text-slate-500 mt-2">Format: JPG, JPEG, PNG, WEBP. Maksimal 2MB.</p>
            </div>

            <div>
                <label for="status" class="block mb-2 font-medium">Status</label>
                <select id="status" name="status" class="w-full border rounded-xl px-4 py-3">
                    <option value="published" <?= (($_POST['status'] ?? '') === 'published') ? 'selected' : ''; ?>>Published</option>
                    <option value="draft" <?= (($_POST['status'] ?? '') === 'draft') ? 'selected' : ''; ?>>Draft</option>
                </select>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="bg-blue-600 text-white px-5 py-3 rounded-xl hover:bg-blue-700">
                    Simpan Artikel
                </button>
                <a href="../admin/dashboard.php" class="bg-slate-200 text-slate-700 px-5 py-3 rounded-xl hover:bg-slate-300">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>