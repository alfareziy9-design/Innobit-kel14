<?php
$pageTitle = 'Kategori - InnoBit';
require_once '../includes/admin_check.php';
require_once '../functions/kategori.php';
require_once '../includes/header.php';

$categories = getAllCategories();

include '../includes/navbar.php';
?>

<div class="max-w-5xl mx-auto px-4 py-8">
    <div class="bg-white rounded-2xl shadow-sm border p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl md:text-3xl font-bold">Kelola Kategori</h1>
            <a href="create.php" class="bg-blue-600 text-white px-4 py-3 rounded-xl hover:bg-blue-700">
                Tambah Kategori
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-left">
                        <th class="p-3">No</th>
                        <th class="p-3">Nama</th>
                        <th class="p-3">Slug</th>
                        <th class="p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($categories)) : ?>
                        <?php foreach ($categories as $index => $category) : ?>
                            <tr class="border-b">
                                <td class="p-3"><?= $index + 1; ?></td>
                                <td class="p-3"><?= htmlspecialchars($category['name']); ?></td>
                                <td class="p-3"><?= htmlspecialchars($category['slug']); ?></td>
                                <td class="p-3">
                                    <div class="flex gap-2">
                                        <a href="edit.php?id=<?= $category['id']; ?>" class="bg-yellow-400 text-white px-3 py-2 rounded-lg hover:bg-yellow-500">
                                            Edit
                                        </a>
                                        <a href="delete.php?id=<?= $category['id']; ?>" class="btn-delete bg-red-500 text-white px-3 py-2 rounded-lg hover:bg-red-600">
                                            Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4" class="p-4 text-center text-slate-500">Belum ada kategori.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<?php include '../includes/footer.php'; ?>