<?php
require_once 'functions/auth.php';

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
    <title>Register - InnoBit</title>
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
            background: #007bff;
            color: white;
            cursor: pointer;
            border-radius: 6px;
        }
        button:hover {
            background: #0056b3;
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
        .alert-success {
            background: #e6ffed;
            color: #146c2e;
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
    <h2>Register</h2>

    <?php if (!empty($errors)) : ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error) : ?>
                    <li><?= $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($successMessage) : ?>
        <div class="alert alert-success">
            <?= $successMessage; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label for="name">Nama</label>
            <input type="text" name="name" id="name" value="<?= htmlspecialchars($_POST['name'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
        </div>

        <div class="form-group">
            <label for="confirm_password">Konfirmasi Password</label>
            <input type="password" name="confirm_password" id="confirm_password" required>
        </div>

        <button type="submit">Daftar</button>
    </form>

    <div class="text-center">
        Sudah punya akun? <a href="login.php">Login</a>
    </div>
</div>

</body>
</html>