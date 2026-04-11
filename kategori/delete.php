<?php
require_once '../includes/admin_check.php';
require_once '../functions/kategori.php';
require_once '../functions/auth.php';

$id = $_GET['id'] ?? 0;

if ($id) {
    $result = deleteCategory((int)$id);
    setFlashMessage($result['success'] ? 'success' : 'error', $result['message']);
} else {
    setFlashMessage('error', 'ID kategori tidak valid.');
}

header('Location: index.php');
exit;