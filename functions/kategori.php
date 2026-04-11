<?php

require_once __DIR__ . '/../config/database.php';

function generateSlug($text)
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return $text;
}

function getAllCategories()
{
    global $conn;

    $query = "SELECT * FROM categories ORDER BY name ASC";
    $result = mysqli_query($conn, $query);

    $categories = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }

    return $categories;
}

function getCategoryById($id)
{
    global $conn;

    $query = "SELECT * FROM categories WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_assoc($result);
}

function createCategory($data)
{
    global $conn;

    $name = trim($data['name'] ?? '');
    $errors = [];

    if ($name === '') {
        $errors[] = 'Nama kategori wajib diisi.';
    }

    if (!empty($errors)) {
        return [
            'success' => false,
            'errors' => $errors
        ];
    }

    $slug = generateSlug($name);

    $checkQuery = "SELECT id FROM categories WHERE name = ? OR slug = ?";
    $stmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($stmt, 'ss', $name, $slug);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        return [
            'success' => false,
            'errors' => ['Kategori sudah ada.']
        ];
    }

    $insertQuery = "INSERT INTO categories (name, slug) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $insertQuery);
    mysqli_stmt_bind_param($stmt, 'ss', $name, $slug);

    if (mysqli_stmt_execute($stmt)) {
        return [
            'success' => true,
            'message' => 'Kategori berhasil ditambahkan.'
        ];
    }

    return [
        'success' => false,
        'errors' => ['Gagal menambahkan kategori.']
    ];
}

function updateCategory($id, $data)
{
    global $conn;

    $name = trim($data['name'] ?? '');
    $errors = [];

    if ($name === '') {
        $errors[] = 'Nama kategori wajib diisi.';
    }

    if (!empty($errors)) {
        return [
            'success' => false,
            'errors' => $errors
        ];
    }

    $slug = generateSlug($name);

    $checkQuery = "SELECT id FROM categories WHERE (name = ? OR slug = ?) AND id != ?";
    $stmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($stmt, 'ssi', $name, $slug, $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        return [
            'success' => false,
            'errors' => ['Kategori dengan nama tersebut sudah ada.']
        ];
    }

    $updateQuery = "UPDATE categories SET name = ?, slug = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt, 'ssi', $name, $slug, $id);

    if (mysqli_stmt_execute($stmt)) {
        return [
            'success' => true,
            'message' => 'Kategori berhasil diperbarui.'
        ];
    }

    return [
        'success' => false,
        'errors' => ['Gagal memperbarui kategori.']
    ];
}

function deleteCategory($id)
{
    global $conn;

    $checkQuery = "SELECT COUNT(*) AS total FROM articles WHERE category_id = ?";
    $stmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    if ($data['total'] > 0) {
        return [
            'success' => false,
            'message' => 'Kategori tidak bisa dihapus karena masih digunakan oleh artikel.'
        ];
    }

    $query = "DELETE FROM categories WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id);

    if (mysqli_stmt_execute($stmt)) {
        return [
            'success' => true,
            'message' => 'Kategori berhasil dihapus.'
        ];
    }

    return [
        'success' => false,
        'message' => 'Gagal menghapus kategori.'
    ];
}