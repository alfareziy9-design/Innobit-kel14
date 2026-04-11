<?php
require_once __DIR__ . '/../functions/auth.php';

if (!isAdmin()) {
    header('Location: /Innobit-Kel14/login.php');
    exit;
}