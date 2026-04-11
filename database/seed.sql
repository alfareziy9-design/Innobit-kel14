USE microlearning;

-- Admin default
INSERT INTO users (name, email, password, photo, role)
VALUES
('Administrator', 'admin@microlearning.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png', 'admin');

-- User biasa default
INSERT INTO users (name, email, password, photo, role)
VALUES
('User Demo', 'user@microlearning.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png', 'user');

-- Categories
INSERT INTO categories (name, slug)
VALUES
('Programming', 'programming'),
('Design', 'design'),
('Productivity', 'productivity');

-- Articles
INSERT INTO articles (category_id, author_id, title, slug, summary, content, thumbnail, status)
VALUES
(1, 1, 'Dasar HTML untuk Pemula', 'dasar-html-untuk-pemula', 'Belajar struktur dasar HTML secara singkat.', 'HTML adalah bahasa markup untuk membuat struktur halaman web...', NULL, 'published'),
(1, 1, 'Mengenal CSS Dasar', 'mengenal-css-dasar', 'Pengenalan CSS untuk styling halaman web.', 'CSS digunakan untuk mengatur tampilan elemen HTML...', NULL, 'published'),
(3, 1, 'Teknik Belajar 25 Menit', 'teknik-belajar-25-menit', 'Cara fokus belajar dengan metode pomodoro.', 'Metode pomodoro membagi waktu belajar menjadi 25 menit fokus...', NULL, 'published');