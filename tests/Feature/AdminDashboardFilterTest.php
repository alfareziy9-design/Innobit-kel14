<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_articles_by_search_status_and_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = User::factory()->create(['role' => 'author']);
        $programming = Category::create(['name' => 'Programming', 'slug' => 'programming']);
        $design = Category::create(['name' => 'Design', 'slug' => 'design']);

        $matchingArticle = $this->createArticle(
            $author,
            $programming,
            'Laravel Query Builder',
            'Panduan filter data Laravel.',
            'published'
        );
        $this->createArticle($author, $programming, 'PHP Dasar', 'Materi pemula.', 'draft');
        $this->createArticle($author, $design, 'Laravel untuk Desainer', 'Kolaborasi antartim.', 'published');

        $response = $this->actingAs($admin)->get(route('admin.dashboard', [
            'search' => 'Query Builder',
            'status' => 'published',
            'category_id' => $programming->id,
        ]));

        $response->assertOk()
            ->assertSee($matchingArticle->title)
            ->assertDontSee('PHP Dasar')
            ->assertDontSee('Laravel untuk Desainer')
            ->assertSee('Menampilkan 1 dari 3 artikel.');
    }

    public function test_admin_search_matches_article_summary(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = User::factory()->create(['role' => 'author']);
        $category = Category::create(['name' => 'Programming', 'slug' => 'programming']);

        $this->createArticle($author, $category, 'Artikel Pertama', 'Membahas dependency injection.', 'draft');
        $this->createArticle($author, $category, 'Artikel Kedua', 'Membahas routing.', 'draft');

        $response = $this->actingAs($admin)->get(route('admin.dashboard', [
            'search' => 'dependency injection',
        ]));

        $response->assertOk()
            ->assertSee('Artikel Pertama')
            ->assertDontSee('Artikel Kedua');
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
