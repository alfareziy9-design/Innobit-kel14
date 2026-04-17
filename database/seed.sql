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
(
  1,
  1,
  'Dasar HTML untuk Pemula',
  'dasar-html-untuk-pemula',
  'Belajar struktur dasar HTML secara singkat.',
  'HTML (HyperText Markup Language) adalah bahasa standar yang digunakan untuk membuat struktur halaman web. Dengan HTML, kita bisa menyusun elemen seperti judul, paragraf, gambar, tautan, tabel, dan form. HTML bukan bahasa pemrograman, melainkan bahasa markup yang berfungsi memberi kerangka pada sebuah website.

Struktur dasar HTML biasanya terdiri dari <!DOCTYPE html>, <html>, <head>, dan <body>. Bagian <head> berisi informasi seperti judul halaman dan metadata, sedangkan bagian <body> berisi konten yang tampil di browser. Dengan memahami struktur ini, pemula akan lebih mudah membaca dan membuat halaman web sederhana.

Beberapa tag dasar yang sering digunakan antara lain <h1> sampai <h6> untuk heading, <p> untuk paragraf, <a> untuk link, <img> untuk gambar, dan <ul> atau <ol> untuk daftar. Setiap tag memiliki fungsi masing-masing dan dapat dikombinasikan untuk membentuk halaman yang terstruktur dengan baik.

Belajar HTML adalah langkah awal yang penting sebelum masuk ke CSS dan JavaScript. Dengan menguasai HTML dasar, kamu sudah bisa membuat kerangka website sederhana seperti profil pribadi, halaman artikel, atau landing page.',
  NULL,
  'published'
),
(
  1,
  1,
  'Mengenal CSS Dasar',
  'mengenal-css-dasar',
  'Pengenalan CSS untuk styling halaman web.',
  'CSS (Cascading Style Sheets) adalah bahasa yang digunakan untuk mengatur tampilan elemen HTML. Jika HTML berfungsi sebagai struktur, maka CSS berfungsi untuk mempercantik tampilan halaman, seperti mengatur warna, ukuran teks, jarak antar elemen, posisi, dan layout halaman.

CSS dapat ditulis dengan tiga cara, yaitu inline CSS, internal CSS, dan external CSS. Inline CSS ditulis langsung pada elemen HTML, internal CSS ditulis di dalam tag <style>, sedangkan external CSS disimpan di file terpisah dengan ekstensi .css. Cara yang paling disarankan adalah external CSS karena lebih rapi dan mudah dikelola.

Konsep dasar CSS meliputi selector, property, dan value. Misalnya, pada aturan p { color: blue; }, huruf p adalah selector, color adalah property, dan blue adalah value. Dengan konsep ini, kita bisa mengubah tampilan elemen secara fleksibel sesuai kebutuhan desain.

CSS sangat penting dalam pengembangan web modern karena membuat tampilan website menjadi lebih menarik dan nyaman digunakan. Setelah memahami CSS dasar, kamu bisa melanjutkan ke topik seperti flexbox, grid, responsive design, dan animasi sederhana.',
  NULL,
  'published'
),
(
  3,
  1,
  'Teknik Belajar 25 Menit',
  'teknik-belajar-25-menit',
  'Cara fokus belajar dengan metode pomodoro.',
  'Teknik belajar 25 menit dikenal sebagai metode Pomodoro. Metode ini membantu seseorang tetap fokus dengan membagi waktu belajar menjadi sesi singkat, biasanya 25 menit belajar penuh lalu diikuti 5 menit istirahat. Setelah empat sesi, kamu bisa mengambil istirahat lebih panjang sekitar 15 sampai 30 menit.

Metode ini cocok untuk pelajar maupun mahasiswa yang sering merasa sulit konsentrasi saat belajar lama. Dengan durasi yang pendek, otak terasa lebih ringan untuk memulai tugas. Fokus selama 25 menit juga membantu mengurangi kebiasaan menunda pekerjaan karena target belajar terasa lebih mudah dicapai.

Agar metode ini efektif, tentukan dulu materi yang ingin dipelajari, lalu matikan gangguan seperti notifikasi ponsel atau media sosial. Gunakan timer selama 25 menit dan usahakan tetap fokus pada satu tugas. Setelah waktu selesai, beri diri sendiri waktu istirahat singkat sebelum memulai sesi berikutnya.

Keuntungan metode Pomodoro adalah meningkatkan disiplin, menjaga energi belajar, dan membantu manajemen waktu. Jika dilakukan secara konsisten, teknik ini dapat membuat proses belajar menjadi lebih teratur, tidak melelahkan, dan lebih produktif.',
  NULL,
  'published'
);