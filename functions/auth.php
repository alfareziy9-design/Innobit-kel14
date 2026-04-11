<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

function cleanInput($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function registerUser($data)
{
    global $conn;

    $name = cleanInput($data['name'] ?? '');
    $email = cleanInput($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $confirmPassword = $data['confirm_password'] ?? '';

    $errors = [];

    if ($name === '') {
        $errors[] = 'Nama wajib diisi.';
    }

    if ($email === '') {
        $errors[] = 'Email wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }

    if ($password === '') {
        $errors[] = 'Password wajib diisi.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    }

    if ($confirmPassword === '') {
        $errors[] = 'Konfirmasi password wajib diisi.';
    } elseif ($password !== $confirmPassword) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }

    if (!empty($errors)) {
        return [
            'success' => false,
            'errors' => $errors
        ];
    }

    $checkQuery = "SELECT id FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        return [
            'success' => false,
            'errors' => ['Email sudah terdaftar.']
        ];
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $insertQuery = "INSERT INTO users (name, email, password, role, photo) VALUES (?, ?, ?, 'user', 'default.png')";
    $stmt = mysqli_prepare($conn, $insertQuery);
    mysqli_stmt_bind_param($stmt, 'sss', $name, $email, $hashedPassword);

    if (mysqli_stmt_execute($stmt)) {
        return [
            'success' => true,
            'message' => 'Registrasi berhasil. Silakan login.'
        ];
    }

    return [
        'success' => false,
        'errors' => ['Registrasi gagal. Coba lagi.']
    ];
}

function loginUser($data)
{
    global $conn;

    $email = cleanInput($data['email'] ?? '');
    $password = $data['password'] ?? '';

    $errors = [];

    if ($email === '') {
        $errors[] = 'Email wajib diisi.';
    }

    if ($password === '') {
        $errors[] = 'Password wajib diisi.';
    }

    if (!empty($errors)) {
        return [
            'success' => false,
            'errors' => $errors
        ];
    }

    $query = "SELECT id, name, email, password, role, photo FROM users WHERE email = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);

            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'photo' => $user['photo']
            ];

            return [
                'success' => true,
                'message' => 'Login berhasil.'
            ];
        }
    }

    return [
        'success' => false,
        'errors' => ['Email atau password salah.']
    ];
}

function logoutUser()
{
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
}

function isLoggedIn()
{
    return isset($_SESSION['user']);
}

function isAdmin()
{
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

function getAuthUser()
{
    return $_SESSION['user'] ?? null;
}

function setFlashMessage($type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }

    return null;
}