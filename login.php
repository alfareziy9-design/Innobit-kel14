<?php
$pageTitle = 'Login - Innobit';
require_once 'functions/auth.php';
require_once 'includes/header.php';
include 'includes/navbar.php';

$errors = [];

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = loginUser($_POST);

    if ($result['success']) {
        if (isAdmin()) {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: index.php');
        }
        exit;
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
    <title>Masuk - InnoBit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom accent color for checkbox */
        .accent-custom {
            accent-color: #361dec;
        }
    </style>
</head>
<body class="bg-gray-100 text-slate-800 font-sans">

    <div class="min-height-screen flex items-center justify-center p-6 min-h-screen">
        <div class="w-full max-w-[700px] bg-white rounded-[28px] shadow-[0_20px_40px_rgba(15,23,42,0.12)] p-8 sm:p-[56px_60px]">
            
            <h1 class="text-center text-4xl sm:text-[44px] font-bold mb-2.5 text-slate-800">Login</h1>

            <div class="text-center text-base sm:text-lg mb-9 text-slate-600">
                Belum punya akun? <a href="register.php" class="text-[#361dec] font-bold hover:underline">Daftar</a>
            </div>

            <?php if (!empty($errors)) : ?>
                <div class="mb-5 p-3.5 px-4 rounded-xl bg-red-100 text-red-700">
                    <ul class="list-disc pl-4.5 m-0">
                        <?php foreach ($errors as $error) : ?>
                            <li><?= htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-0">
                    <input
                        type="email"
                        name="email"
                        placeholder="Alamat Email"
                        value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>"
                        class="w-full h-[60px] sm:h-[68px] border border-slate-300 px-5 text-base sm:text-lg text-slate-600 outline-none focus:border-blue-500 rounded-t-none border-b-0"
                        required
                    >
                </div>

                <div class="mb-0">
                    <input
                        type="password"
                        name="password"
                        placeholder="Password"
                        class="w-full h-[60px] sm:h-[68px] border border-slate-300 px-5 text-base sm:text-lg text-slate-600 outline-none focus:border-blue-500 rounded-b-none"
                        required
                    >
                </div>

                <div class="mt-5.5 mb-7 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <label class="flex items-center gap-3 text-base sm:text-lg text-slate-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-6 h-6 accent-custom">
                        <span>Ingat saya</span>
                    </label>

                    <a href="#" class="text-[#361dec] font-semibold text-base sm:text-lg hover:underline">Lupa password?</a>
                </div>

                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white py-4 sm:py-[18px] rounded-xl text-lg sm:text-xl font-bold transition-colors duration-200">
                    Masuk
                </button>
            </form>
            
        </div>
    </div>

</body>
</html>