<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCategoryPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_category_by_slug_and_only_see_published_articles(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $programming = Category::create(['name' => 'Programming', 'slug' => 'programming']);
        $design = Category::create(['name' => 'Design', 'slug' => 'design']);

        $published = $this->createArticle($author, $programming, 'Artikel Published', 'published');
        $this->createArticle($author, $programming, 'Artikel Draft', 'draft');
        $this->createArticle($author, $programming, 'Artikel Review', 'review');
        $this->createArticle($author, $programming, 'Artikel Rejected', 'rejected');
        $this->createArticle($author, $design, 'Artikel Design', 'published');

        $response = $this->get(route('categories.show', $programming->slug));

        $response->assertOk()
            ->assertSee('Programming')
            ->assertSee('1 artikel published dalam kategori ini.')
            ->assertSee($published->title)
            ->assertDontSee('Artikel Draft')
            ->assertDontSee('Artikel Review')
            ->assertDontSee('Artikel Rejected')
            ->assertDontSee('Artikel Design')
            ->assertSee(route('login'), false);
    }

    public function test_unknown_category_slug_returns_not_found(): void
    {
        $this->get(route('categories.show', 'tidak-ada'))->assertNotFound();
    }

    public function test_empty_category_shows_category_specific_empty_state(): void
    {
        $category = Category::create(['name' => 'Kosong', 'slug' => 'kosong']);

        $this->get(route('categories.show', $category->slug))
            ->assertOk()
            ->assertSee('Belum ada artikel')
            ->assertSee('Belum ada artikel published dalam kategori Kosong.');
    }

    public function test_category_page_paginates_sixteen_articles(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $category = Category::create(['name' => 'Programming', 'slug' => 'programming']);

        foreach (range(1, 17) as $index) {
            $this->createArticle($author, $category, "Artikel {$index}", 'published');
        }

        $this->get(route('categories.show', $category->slug))
            ->assertOk()
            ->assertSee('17 artikel published dalam kategori ini.')
            ->assertSee('Halaman 1 dari 2')
            ->assertSee('page=2', false);
    }

    public function test_home_category_links_use_slug_while_filter_dropdown_remains_available(): void
    {
        $category = Category::create(['name' => 'Programming', 'slug' => 'programming']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('categories.show', $category->slug), false)
            ->assertSee('name="category_id"', false)
            ->assertSee('value="'.$category->id.'"', false);
    }

    public function test_public_category_route_does_not_override_admin_category_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('kategori.create'))->assertOk();
    }

    private function createArticle(
        User $author,
        Category $category,
        string $title,
        string $status
    ): Article {
        return Article::create([
            'category_id' => $category->id,
            'author_id' => $author->id,
            'title' => $title,
            'slug' => str($title)->slug(),
            'summary' => 'Ringkasan artikel.',
            'content' => 'Konten artikel.',
            'status' => $status,
        ]);
    }
}
