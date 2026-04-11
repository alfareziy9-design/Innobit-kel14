<?php
require_once '../includes/admin_check.php';
require_once '../functions/artikel.php';

$id = $_GET['id'] ?? 0;

if ($id) {
    deleteArticle((int)$id);
}

header('Location: ../admin/dashboard.php');
exit;