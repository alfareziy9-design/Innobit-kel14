<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeArticleFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_without_filters_shows_main_sections(): void
    {
        [$author, $category] = $this->createAuthorAndCategory();
        $this->createArticle($author, $category, 'Artikel Utama', 'Ringkasan.', 'published');

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('Baca Setiap Hari')
            ->assertSee('data-typing-words=\'["Jadi Lebih Tau","Jadi Lebih Jago","Jadi Lebih Baik"]\'', false)
            ->assertSee('Rekomendasi Untukmu')
            ->assertSee('Baru Terbit')
            ->assertSee('Sedang Populer')
            ->assertSee('Yuk Cari Tau Hal Baru')
            ->assertDontSee('Microlearning harian')
            ->assertDontSee('Hasil Artikel');
    }

    public function test_search_shows_only_article_results_section(): void
    {
        [$author, $programming] = $this->createAuthorAndCategory();
        $this->createArticle($author, $programming, 'Laravel Query Builder', 'Panduan query.', 'published');
        $this->createArticle($author, $programming, 'Belajar CSS', 'Panduan tampilan.', 'published');

        $response = $this->get(route('home', ['search' => 'Query Builder']));

        $response->assertOk()
            ->assertSee('Hasil Artikel')
            ->assertSee('Laravel Query Builder')
            ->assertDontSee('Belajar CSS')
            ->assertDontSee('Rekomendasi Untukmu')
            ->assertDontSee('Sedang Populer');
    }

    public function test_category_filter_shows_only_articles_from_selected_category(): void
    {
        [$author, $programming] = $this->createAuthorAndCategory();
        $design = Category::create(['name' => 'Design', 'slug' => 'design']);
        $this->createArticle($author, $programming, 'Artikel Programming', 'Ringkasan.', 'published');
        $this->createArticle($author, $design, 'Artikel Design', 'Ringkasan.', 'published');

        $response = $this->get(route('home', ['category_id' => $programming->id]));

        $response->assertOk()
            ->assertSee('Hasil Artikel')
            ->assertSee('Artikel Programming')
            ->assertDontSee('Artikel Design');
    }

    public function test_search_and_category_filters_are_combined(): void
    {
        [$author, $programming] = $this->createAuthorAndCategory();
        $design = Category::create(['name' => 'Design', 'slug' => 'design']);
        $this->createArticle($author, $programming, 'Laravel Patterns', 'Ringkasan.', 'published');
        $this->createArticle($author, $programming, 'PHP Patterns', 'Ringkasan.', 'published');
        $this->createArticle($author, $design, 'Laravel Design', 'Ringkasan.', 'published');

        $response = $this->get(route('home', [
            'search' => 'Laravel',
            'category_id' => $programming->id,
        ]));

        $response->assertOk()
            ->assertSee('Laravel Patterns')
            ->assertDontSee('PHP Patterns')
            ->assertDontSee('Laravel Design');
    }

    public function test_result_pagination_preserves_active_filters(): void
    {
        [$author, $programming] = $this->createAuthorAndCategory();

        foreach (range(1, 17) as $index) {
            $this->createArticle(
                $author,
                $programming,
                "Laravel Article {$index}",
                'Ringkasan Laravel.',
                'published'
            );
        }

        $response = $this->get(route('home', [
            'search' => 'Laravel',
            'category_id' => $programming->id,
        ]));

        $response->assertOk()
            ->assertSee('Halaman 1 dari 2')
            ->assertSee('search=Laravel', false)
            ->assertSee('category_id='.$programming->id, false);
    }

    public function test_empty_search_with_category_shows_contextual_actions(): void
    {
        [, $programming] = $this->createAuthorAndCategory();

        $response = $this->get(route('home', [
            'search' => 'Tidak Ditemukan',
            'category_id' => $programming->id,
        ]));

        $response->assertOk()
            ->assertSee('Tidak ada hasil')
            ->assertSee('Tidak Ditemukan')
            ->assertSee($programming->name)
            ->assertSee('Periksa kembali ejaan kata kunci.')
            ->assertSee('Hapus kata kunci')
            ->assertSee(route('home', ['category_id' => $programming->id]), false)
            ->assertSee('Cari di semua kategori')
            ->assertSee(route('home', ['search' => 'Tidak Ditemukan']), false)
            ->assertSee('Reset semua');
    }

    public function test_empty_keyword_search_shows_reset_search_action(): void
    {
        $this->createAuthorAndCategory();

        $this->get(route('home', ['search' => 'Tidak Ditemukan']))
            ->assertOk()
            ->assertSee('Reset pencarian')
            ->assertDontSee('Hapus kata kunci')
            ->assertDontSee('Cari di semua kategori')
            ->assertDontSee('Lihat semua artikel');
    }

    public function test_empty_category_filter_shows_all_articles_action(): void
    {
        $category = Category::create(['name' => 'Kategori Kosong', 'slug' => 'kategori-kosong']);

        $this->get(route('home', ['category_id' => $category->id]))
            ->assertOk()
            ->assertSee('Kategori Kosong')
            ->assertSee('Lihat semua artikel')
            ->assertDontSee('Reset pencarian')
            ->assertDontSee('Cari di semua kategori');
    }

    public function test_non_empty_search_does_not_show_actionable_empty_state(): void
    {
        [$author, $programming] = $this->createAuthorAndCategory();
        $this->createArticle($author, $programming, 'Laravel Ditemukan', 'Ringkasan.', 'published');

        $this->get(route('home', ['search' => 'Laravel']))
            ->assertOk()
            ->assertSee('Laravel Ditemukan')
            ->assertDontSee('Tidak ada hasil')
            ->assertDontSee('Reset pencarian');
    }

    private function createAuthorAndCategory(): array
    {
        return [
            User::factory()->create(['role' => 'author']),
            Category::create(['name' => 'Programming', 'slug' => 'programming']),
        ];
    }

    private function createArticle(
        User $author,
        Category $category,
        string $title,
        string $summary,
        string $status
    ): Article {
        return Article::create([
            'category_id' => $category->id,
            'author_id' => $author->id,
            'title' => $title,
            'slug' => str($title)->slug(),
            'summary' => $summary,
            'content' => 'Konten artikel.',
            'status' => $status,
        ]);
    }
}
