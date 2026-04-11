<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/kategori.php';

function getAllArticles($search = '', $categoryId = '')
{
    global $conn;

    $query = "
        SELECT articles.*, categories.name AS category_name, users.name AS author_name
        FROM articles
        JOIN categories ON articles.category_id = categories.id
        JOIN users ON articles.author_id = users.id
        WHERE articles.status = 'published'
    ";

    $params = [];
    $types = '';

    if ($search !== '') {
        $query .= " AND (articles.title LIKE ? OR articles.summary LIKE ?)";
        $searchLike = '%' . $search . '%';
        $params[] = $searchLike;
        $params[] = $searchLike;
        $types .= 'ss';
    }

    if ($categoryId !== '') {
        $query .= " AND articles.category_id = ?";
        $params[] = $categoryId;
        $types .= 'i';
    }

    $query .= " ORDER BY articles.created_at DESC";

    $stmt = mysqli_prepare($conn, $query);

    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $articles = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $articles[] = $row;
    }

    return $articles;
}

function getArticleById($id)
{
    global $conn;

    $query = "
        SELECT articles.*, categories.name AS category_name, users.name AS author_name
        FROM articles
        JOIN categories ON articles.category_id = categories.id
        JOIN users ON articles.author_id = users.id
        WHERE articles.id = ?
        LIMIT 1
    ";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_assoc($result);
}

function getArticleBySlug($slug)
{
    global $conn;

    $query = "
        SELECT articles.*, categories.name AS category_name, users.name AS author_name
        FROM articles
        JOIN categories ON articles.category_id = categories.id
        JOIN users ON articles.author_id = users.id
        WHERE articles.slug = ? AND articles.status = 'published'
        LIMIT 1
    ";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $slug);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_assoc($result);
}

function createArticle($data, $file = null)
{
    global $conn;

    $title = trim($data['title'] ?? '');
    $categoryId = (int)($data['category_id'] ?? 0);
    $summary = trim($data['summary'] ?? '');
    $content = trim($data['content'] ?? '');
    $status = trim($data['status'] ?? 'published');
    $authorId = (int)($data['author_id'] ?? 0);

    $errors = [];

    if ($title === '') {
        $errors[] = 'Judul wajib diisi.';
    }

    if ($categoryId <= 0) {
        $errors[] = 'Kategori wajib dipilih.';
    }

    if ($summary === '') {
        $errors[] = 'Ringkasan wajib diisi.';
    }

    if ($content === '') {
        $errors[] = 'Isi artikel wajib diisi.';
    }

    if ($authorId <= 0) {
        $errors[] = 'Author tidak valid.';
    }

    $thumbnailName = null;

    if ($file && isset($file['thumbnail']) && $file['thumbnail']['error'] !== 4) {
        $thumbnail = $file['thumbnail'];

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $fileName = $thumbnail['name'];
        $fileTmp = $thumbnail['tmp_name'];
        $fileSize = $thumbnail['size'];
        $fileError = $thumbnail['error'];

        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($fileError !== 0) {
            $errors[] = 'Terjadi kesalahan saat upload thumbnail.';
        } elseif (!in_array($fileExt, $allowedExtensions)) {
            $errors[] = 'Thumbnail harus berupa JPG, JPEG, PNG, atau WEBP.';
        } elseif ($fileSize > 2 * 1024 * 1024) {
            $errors[] = 'Ukuran thumbnail maksimal 2MB.';
        } else {
            $thumbnailName = time() . '_' . uniqid() . '.' . $fileExt;
            $destination = __DIR__ . '/../uploads/artikel/' . $thumbnailName;

            if (!move_uploaded_file($fileTmp, $destination)) {
                $errors[] = 'Gagal menyimpan thumbnail.';
            }
        }
    }

    if (!empty($errors)) {
        return [
            'success' => false,
            'errors' => $errors
        ];
    }

    $slug = generateSlug($title);

    $checkQuery = "SELECT id FROM articles WHERE slug = ?";
    $stmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($stmt, 's', $slug);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $slug .= '-' . time();
    }

    $query = "
        INSERT INTO articles (category_id, author_id, title, slug, summary, content, thumbnail, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param(
        $stmt,
        'iissssss',
        $categoryId,
        $authorId,
        $title,
        $slug,
        $summary,
        $content,
        $thumbnailName,
        $status
    );

    if (mysqli_stmt_execute($stmt)) {
        return [
            'success' => true,
            'message' => 'Artikel berhasil ditambahkan.'
        ];
    }

    return [
        'success' => false,
        'errors' => ['Gagal menambahkan artikel.']
    ];
}

function updateArticle($id, $data, $file = null)
{
    global $conn;

    $article = getArticleById($id);

    if (!$article) {
        return [
            'success' => false,
            'errors' => ['Artikel tidak ditemukan.']
        ];
    }

    $title = trim($data['title'] ?? '');
    $categoryId = (int)($data['category_id'] ?? 0);
    $summary = trim($data['summary'] ?? '');
    $content = trim($data['content'] ?? '');
    $status = trim($data['status'] ?? 'published');

    $errors = [];

    if ($title === '') {
        $errors[] = 'Judul wajib diisi.';
    }

    if ($categoryId <= 0) {
        $errors[] = 'Kategori wajib dipilih.';
    }

    if ($summary === '') {
        $errors[] = 'Ringkasan wajib diisi.';
    }

    if ($content === '') {
        $errors[] = 'Isi artikel wajib diisi.';
    }

    $thumbnailName = $article['thumbnail'];

    if ($file && isset($file['thumbnail']) && $file['thumbnail']['error'] !== 4) {
        $thumbnail = $file['thumbnail'];

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $fileName = $thumbnail['name'];
        $fileTmp = $thumbnail['tmp_name'];
        $fileSize = $thumbnail['size'];
        $fileError = $thumbnail['error'];

        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($fileError !== 0) {
            $errors[] = 'Terjadi kesalahan saat upload thumbnail.';
        } elseif (!in_array($fileExt, $allowedExtensions)) {
            $errors[] = 'Thumbnail harus berupa JPG, JPEG, PNG, atau WEBP.';
        } elseif ($fileSize > 2 * 1024 * 1024) {
            $errors[] = 'Ukuran thumbnail maksimal 2MB.';
        } else {
            $newThumbnailName = time() . '_' . uniqid() . '.' . $fileExt;
            $destination = __DIR__ . '/../uploads/artikel/' . $newThumbnailName;

            if (move_uploaded_file($fileTmp, $destination)) {
                if (!empty($article['thumbnail']) && file_exists(__DIR__ . '/../uploads/artikel/' . $article['thumbnail'])) {
                    unlink(__DIR__ . '/../uploads/artikel/' . $article['thumbnail']);
                }
                $thumbnailName = $newThumbnailName;
            } else {
                $errors[] = 'Gagal menyimpan thumbnail baru.';
            }
        }
    }

    if (!empty($errors)) {
        return [
            'success' => false,
            'errors' => $errors
        ];
    }

    $slug = generateSlug($title);

    $checkQuery = "SELECT id FROM articles WHERE slug = ? AND id != ?";
    $stmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($stmt, 'si', $slug, $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $slug .= '-' . time();
    }

    $query = "
        UPDATE articles
        SET category_id = ?, title = ?, slug = ?, summary = ?, content = ?, thumbnail = ?, status = ?
        WHERE id = ?
    ";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param(
        $stmt,
        'issssssi',
        $categoryId,
        $title,
        $slug,
        $summary,
        $content,
        $thumbnailName,
        $status,
        $id
    );

    if (mysqli_stmt_execute($stmt)) {
        return [
            'success' => true,
            'message' => 'Artikel berhasil diperbarui.'
        ];
    }

    return [
        'success' => false,
        'errors' => ['Gagal memperbarui artikel.']
    ];
}

function deleteArticle($id)
{
    global $conn;

    $article = getArticleById($id);

    if (!$article) {
        return false;
    }

    if (!empty($article['thumbnail'])) {
        $thumbnailPath = __DIR__ . '/../uploads/artikel/' . $article['thumbnail'];
        if (file_exists($thumbnailPath)) {
            unlink($thumbnailPath);
        }
    }

    $query = "DELETE FROM articles WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id);

    return mysqli_stmt_execute($stmt);
}

function getAllArticlesForAdmin()
{
    global $conn;

    $query = "
        SELECT articles.*, categories.name AS category_name, users.name AS author_name
        FROM articles
        JOIN categories ON articles.category_id = categories.id
        JOIN users ON articles.author_id = users.id
        ORDER BY articles.created_at DESC
    ";

    $result = mysqli_query($conn, $query);

    $articles = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $articles[] = $row;
    }

    return $articles;
}