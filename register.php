<?php
$pageTitle = 'Register - Innobit';
require_once 'functions/auth.php';
require_once 'includes/header.php';
include 'includes/navbar.php';

$errors = [];
$successMessage = '';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = registerUser($_POST);

    if ($result['success']) {
        $successMessage = $result['message'];
    } else {
        $errors = $result['errors'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - InnoBit</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-slate-800 font-sans">

    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="w-full max-w-[700px] bg-white rounded-[28px] shadow-[0_20px_40px_rgba(15,23,42,0.12)] p-8 sm:p-[56px_60px]">
            
            <h1 class="text-center text-4xl sm:text-[44px] font-bold mb-2.5 text-slate-800">Daftar</h1>

            <div class="text-center text-base sm:text-lg mb-9 text-slate-600">
                Sudah punya akun? <a href="login.php" class="text-[#361dec] font-bold hover:underline">Masuk</a>
            </div>

            <?php if (!empty($errors)) : ?>
                <div class="mb-5 p-4 rounded-xl bg-red-100 text-red-700">
                    <ul class="list-disc pl-5">
                        <?php foreach ($errors as $error) : ?>
                            <li><?= htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (isset($successMessage) && $successMessage) : ?>
                <div class="mb-5 p-4 rounded-xl bg-[#361dec]-100 text-[#361dec]-700 font-medium">
                    <?= htmlspecialchars($successMessage); ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-0">
                    <input
                        type="text"
                        name="name"
                        placeholder="Nama Lengkap"
                        value="<?= htmlspecialchars($_POST['name'] ?? ''); ?>"
                        class="w-full h-[60px] sm:h-[68px] border border-slate-300 px-5 text-base sm:text-lg text-slate-600 outline-none focus:border-indigo-500 rounded-t-xl"
                        required
                    >
                </div>

                <div class="mb-0">
                    <input
                        type="email"
                        name="email"
                        placeholder="Alamat Email"
                        value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>"
                        class="w-full h-[60px] sm:h-[68px] border-x border-b border-slate-300 px-5 text-base sm:text-lg text-slate-600 outline-none focus:border-indigo-500"
                        required
                    >
                </div>

                <div class="mb-0">
                    <input
                        type="password"
                        name="password"
                        placeholder="Password"
                        class="w-full h-[60px] sm:h-[68px] border-x border-b border-slate-300 px-5 text-base sm:text-lg text-slate-600 outline-none focus:border-indigo-500"
                        required
                    >
                </div>

                <div class="mb-8">
                    <input
                        type="password"
                        name="confirm_password"
                        placeholder="Konfirmasi Password"
                        class="w-full h-[60px] sm:h-[68px] border-x border-b border-slate-300 px-5 text-base sm:text-lg text-slate-600 outline-none focus:border-indigo-500 rounded-b-xl"
                        required
                    >
                </div>

                <button type="submit" class="w-full bg-[#361dec] hover:bg-[#361dec] text-white py-4 sm:py-[18px] rounded-xl text-lg sm:text-xl font-bold transition-all duration-200 transform active:scale-[0.98]">
                    Daftar Sekarang
                </button>
            </form>
            
        </div>
    </div>

</body>
</html>