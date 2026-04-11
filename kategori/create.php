<?php
$pageTitle = 'Tambah Kategori - InnoBit';
require_once '../includes/admin_check.php';
require_once '../functions/kategori.php';
require_once '../includes/header.php';

$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = createCategory($_POST);

    if ($result['success']) {
        $successMessage = $result['message'];
    } else {
        $errors = $result['errors'];
    }
}

include '../includes/navbar.php';
?>

<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="bg-white rounded-2xl shadow-sm border p-6">
        <h1 class="text-2xl md:text-3xl font-bold mb-6">Tambah Kategori</h1>

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

        <form id="categoryForm" action="" method="POST" class="space-y-5">
            <div>
                <label for="name" class="block mb-2 font-medium">Nama Kategori</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?= htmlspecialchars($_POST['name'] ?? ''); ?>"
                    class="w-full border rounded-xl px-4 py-3"
                >
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 text-white px-5 py-3 rounded-xl hover:bg-blue-700">
                    Simpan
                </button>
                <a href="index.php" class="bg-slate-200 text-slate-700 px-5 py-3 rounded-xl hover:bg-slate-300">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>