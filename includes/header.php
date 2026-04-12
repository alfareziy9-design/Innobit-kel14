<?php
require_once __DIR__ . '/../functions/auth.php';
$user = getAuthUser();
$pageTitle = $pageTitle ?? 'InnoBit';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/png" href="/Innobit-Kel14/assets/img/logo_Innobit.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">