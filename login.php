<?php
require_once 'functions/auth.php';

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
    <title>Login - InnoBit</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 400px;
            max-width: 90%;
            background: #fff;
            margin: 50px auto;
            padding: 24px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.08);
        }
        h2 {
            margin-bottom: 20px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 16px;
        }
        label {
            display: block;
            margin-bottom: 6px;
        }
        input {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
            border: none;
            background: #28a745;
            color: white;
            cursor: pointer;
            border-radius: 6px;
        }
        button:hover {
            background: #1e7e34;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
        }
        .alert-error {
            background: #ffe5e5;
            color: #b30000;
        }
        .text-center {
            text-align: center;
            margin-top: 15px;
        }
        ul {
            padding-left: 20px;
            margin: 0;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Login</h2>

    <?php if (!empty($errors)) : ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error) : ?>
                    <li><?= $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
        </div>

        <button type="submit">Login</button>
    </form>

    <div class="text-center">
        Belum punya akun? <a href="register.php">Daftar</a>
    </div>
</div>

</body>
</html>