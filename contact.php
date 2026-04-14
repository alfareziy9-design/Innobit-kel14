<?php
$pageTitle = 'Kontak Kami - Innobit';
require_once 'includes/header.php';
include 'includes/navbar.php';

$successMessage = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name)) $errors[] = 'Nama harus diisi.';
    if (empty($email)) $errors[] = 'Email harus diisi.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    if (empty($message)) $errors[] = 'Pesan harus diisi.';

    if (empty($errors)) {
        // Di sini Anda bisa tambahkan logika kirim email atau simpan ke database
        $successMessage = 'Pesan Anda telah terkirim. Kami akan menghubungi Anda segera.';
        // Reset form
        $_POST = [];
    }
}
?>

<div class="max-w-7xl mx-auto px-4 py-12">
    <div class="grid md:grid-cols-2 gap-8">
        <!-- Form Kontak -->
        <div class="bg-white rounded-2xl shadow-sm border p-6 md:p-8">
            <h1 class="text-2xl md:text-3xl font-bold text-slate-800 mb-2">Hubungi Kami</h1>
            <p class="text-slate-600 mb-6">Ada pertanyaan atau saran? Silakan isi form di bawah.</p>

            <?php if (!empty($errors)) : ?>
                <div class="mb-5 p-4 rounded-xl bg-red-100 text-red-700">
                    <ul class="list-disc pl-5">
                        <?php foreach ($errors as $error) : ?>
                            <li><?= htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($successMessage) : ?>
                <div class="mb-5 p-4 rounded-xl bg-green-100 text-green-700 font-medium">
                    <?= htmlspecialchars($successMessage); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-4">
                    <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="Nama Lengkap" class="w-full border rounded-none px-4 py-3 focus:outline-none focus:ring-2 focus:ring-lime-500">
                </div>
                <div class="mb-4">
                    <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="Alamat Email" class="w-full border rounded-none px-4 py-3 focus:outline-none focus:ring-2 focus:ring-lime-500">
                </div>
                <div class="mb-6">
                    <textarea name="message" rows="5" placeholder="Pesan Anda..." class="w-full border rounded-none px-4 py-3 focus:outline-none focus:ring-2 focus:ring-lime-500"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="bg-lime-500 text-white rounded-none px-6 py-3 hover:bg-lime-600 transition w-full md:w-auto">Kirim Pesan</button>
            </form>
        </div>

        <!-- Info Kontak -->
        <div class="bg-white rounded-2xl shadow-sm border p-6 md:p-8">
            <h2 class="text-xl font-bold text-slate-800 mb-4">Informasi Kontak</h2>
            <div class="space-y-4 text-slate-600">
                <div>
                    <p class="font-semibold">📍 Alamat</p>
                    <p>Jl. Rungkut Madya, Gn. Anyar, Kec. Gunung Anyar, Surabaya, Jawa Timur 60294</p>
                </div>
                <div>
                    <p class="font-semibold">📧 Email</p>
                    <p>innobit@outlook.com</p>
                </div>
                <div>
                    <p class="font-semibold">📞 Telepon</p>
                    <p>+62 812 3456 7890</p>
                </div>
                <div>
                    <p class="font-semibold">🕒 Jam Operasional</p>
                    <p>Senin - Jumat, 09.00 - 17.00 WIB</p>
                </div>
            </div>

            <hr class="my-6 border-slate-200">

            <h2 class="text-xl font-bold text-slate-800 mb-3">Ikuti Kami</h2>
            <div class="flex gap-4">
                <a href="#" class="text-slate-500 hover:text-lime-500 transition">Instagram</a>
                <a href="#" class="text-slate-500 hover:text-lime-500 transition">LinkedIn</a>
                <a href="#" class="text-slate-500 hover:text-lime-500 transition">YouTube</a>
                <a href="#" class="text-slate-500 hover:text-lime-500 transition">TikTok</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>