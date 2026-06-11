<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = collect([
            ['name' => 'admin', 'label' => 'Administrator'],
            ['name' => 'author', 'label' => 'Author'],
            ['name' => 'user', 'label' => 'User'],
        ])->mapWithKeys(fn ($role) => [
            $role['name'] => Role::updateOrCreate(['name' => $role['name']], $role),
        ]);

        $admin = User::updateOrCreate(
            ['email' => 'admin@microlearning.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'photo' => 'default.png',
                'role' => 'admin',
                'role_id' => $roles['admin']->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'author@microlearning.com'],
            [
                'name' => 'Author Demo',
                'password' => Hash::make('password'),
                'photo' => 'default.png',
                'role' => 'author',
                'role_id' => $roles['author']->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@microlearning.com'],
            [
                'name' => 'User Demo',
                'password' => Hash::make('password'),
                'photo' => 'default.png',
                'role' => 'user',
                'role_id' => $roles['user']->id,
            ]
        );

        $categories = collect([
            ['name' => 'Programming', 'slug' => 'programming'],
            ['name' => 'Design', 'slug' => 'design'],
            ['name' => 'Productivity', 'slug' => 'productivity'],
        ])->mapWithKeys(fn ($category) => [
            $category['slug'] => Category::updateOrCreate(['slug' => $category['slug']], $category),
        ]);

        Article::updateOrCreate(
            ['slug' => 'dasar-html-untuk-pemula'],
            [
                'category_id' => $categories['programming']->id,
                'author_id' => $admin->id,
                'title' => 'Dasar HTML untuk Pemula',
                'summary' => 'Belajar struktur dasar HTML secara singkat.',
                'content' => "HTML (HyperText Markup Language) adalah bahasa standar yang digunakan untuk membuat struktur halaman web. Dengan HTML, kita bisa menyusun elemen seperti judul, paragraf, gambar, tautan, tabel, dan form. HTML bukan bahasa pemrograman, melainkan bahasa markup yang berfungsi memberi kerangka pada sebuah website.\n\nStruktur dasar HTML biasanya terdiri dari <!DOCTYPE html>, <html>, <head>, dan <body>. Bagian <head> berisi informasi seperti judul halaman dan metadata, sedangkan bagian <body> berisi konten yang tampil di browser. Dengan memahami struktur ini, pemula akan lebih mudah membaca dan membuat halaman web sederhana.\n\nBeberapa tag dasar yang sering digunakan antara lain <h1> sampai <h6> untuk heading, <p> untuk paragraf, <a> untuk link, <img> untuk gambar, dan <ul> atau <ol> untuk daftar. Setiap tag memiliki fungsi masing-masing dan dapat dikombinasikan untuk membentuk halaman yang terstruktur dengan baik.\n\nBelajar HTML adalah langkah awal yang penting sebelum masuk ke CSS dan JavaScript. Dengan menguasai HTML dasar, kamu sudah bisa membuat kerangka website sederhana seperti profil pribadi, halaman artikel, atau landing page.",
                'status' => 'published',
            ]
        );

        Article::updateOrCreate(
            ['slug' => 'mengenal-css-dasar'],
            [
                'category_id' => $categories['programming']->id,
                'author_id' => $admin->id,
                'title' => 'Mengenal CSS Dasar',
                'summary' => 'Pengenalan CSS untuk styling halaman web.',
                'content' => "CSS (Cascading Style Sheets) adalah bahasa yang digunakan untuk mengatur tampilan elemen HTML. Jika HTML berfungsi sebagai struktur, maka CSS berfungsi untuk mempercantik tampilan halaman, seperti mengatur warna, ukuran teks, jarak antar elemen, posisi, dan layout halaman.\n\nCSS dapat ditulis dengan tiga cara, yaitu inline CSS, internal CSS, dan external CSS. Inline CSS ditulis langsung pada elemen HTML, internal CSS ditulis di dalam tag <style>, sedangkan external CSS disimpan di file terpisah dengan ekstensi .css. Cara yang paling disarankan adalah external CSS karena lebih rapi dan mudah dikelola.\n\nKonsep dasar CSS meliputi selector, property, dan value. Misalnya, pada aturan p { color: blue; }, huruf p adalah selector, color adalah property, dan blue adalah value. Dengan konsep ini, kita bisa mengubah tampilan elemen secara fleksibel sesuai kebutuhan desain.\n\nCSS sangat penting dalam pengembangan web modern karena membuat tampilan website menjadi lebih menarik dan nyaman digunakan. Setelah memahami CSS dasar, kamu bisa melanjutkan ke topik seperti flexbox, grid, responsive design, dan animasi sederhana.",
                'status' => 'published',
            ]
        );

        Article::updateOrCreate(
            ['slug' => 'teknik-belajar-25-menit'],
            [
                'category_id' => $categories['productivity']->id,
                'author_id' => $admin->id,
                'title' => 'Teknik Belajar 25 Menit',
                'summary' => 'Cara fokus belajar dengan metode pomodoro.',
                'content' => "Teknik belajar 25 menit dikenal sebagai metode Pomodoro. Metode ini membantu seseorang tetap fokus dengan membagi waktu belajar menjadi sesi singkat, biasanya 25 menit belajar penuh lalu diikuti 5 menit istirahat. Setelah empat sesi, kamu bisa mengambil istirahat lebih panjang sekitar 15 sampai 30 menit.\n\nMetode ini cocok untuk pelajar maupun mahasiswa yang sering merasa sulit konsentrasi saat belajar lama. Dengan durasi yang pendek, otak terasa lebih ringan untuk memulai tugas. Fokus selama 25 menit juga membantu mengurangi kebiasaan menunda pekerjaan karena target belajar terasa lebih mudah dicapai.\n\nAgar metode ini efektif, tentukan dulu materi yang ingin dipelajari, lalu matikan gangguan seperti notifikasi ponsel atau media sosial. Gunakan timer selama 25 menit dan usahakan tetap fokus pada satu tugas. Setelah waktu selesai, beri diri sendiri waktu istirahat singkat sebelum memulai sesi berikutnya.\n\nKeuntungan metode Pomodoro adalah meningkatkan disiplin, menjaga energi belajar, dan membantu manajemen waktu. Jika dilakukan secara konsisten, teknik ini dapat membuat proses belajar menjadi lebih teratur, tidak melelahkan, dan lebih produktif.",
                'status' => 'published',
            ]
        );
    }
}
