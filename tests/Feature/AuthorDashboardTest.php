<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleCollection;
use App\Models\ArticleView;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_dashboard_shows_only_own_articles_and_analytics(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $otherAuthor = User::factory()->create(['role' => 'author']);
        $reader = User::factory()->create(['role' => 'user']);
        $category = Category::create(['name' => 'Analytics', 'slug' => 'analytics']);
        $ownArticle = $this->article($author, $category, 'Artikel Author Sendiri');
        $otherArticle = $this->article($otherAuthor, $category, 'Artikel Author Lain');
        $quiz = Quiz::create(['article_id' => $ownArticle->id, 'title' => 'Quiz', 'is_active' => true]);

        ArticleView::create(['user_id' => $reader->id, 'article_id' => $ownArticle->id, 'viewed_at' => now()]);
        ArticleView::create(['user_id' => $reader->id, 'article_id' => $ownArticle->id, 'viewed_at' => now()]);
        ArticleView::create(['user_id' => $reader->id, 'article_id' => $otherArticle->id, 'viewed_at' => now()]);
        Favorite::create(['user_id' => $reader->id, 'article_id' => $ownArticle->id]);
        ArticleCollection::create(['user_id' => $reader->id, 'article_id' => $ownArticle->id]);
        QuizAttempt::create([
            'user_id' => $reader->id,
            'article_id' => $ownArticle->id,
            'quiz_id' => $quiz->id,
            'score' => 80,
            'submitted_at' => now(),
        ]);

        $this->actingAs($author)
            ->get(route('author.dashboard'))
            ->assertOk()
            ->assertSee('Artikel Author Sendiri')
            ->assertDontSee('Artikel Author Lain')
            ->assertSee('<strong>2</strong> views', false)
            ->assertSee('<strong>1</strong> favorit', false)
            ->assertSee('<strong>1</strong> koleksi', false)
            ->assertSee('avg 80%')
            ->assertSee('Total views')
            ->assertSee('Top Artikel');
    }

    public function test_author_dashboard_filters_by_search_status_and_category(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $programming = Category::create(['name' => 'Programming', 'slug' => 'programming']);
        $design = Category::create(['name' => 'Design', 'slug' => 'design']);

        $matching = $this->article($author, $programming, 'Laravel Author Filter', 'published');
        $this->article($author, $programming, 'Laravel Draft', 'draft');
        $this->article($author, $design, 'Design Published', 'published');

        $this->actingAs($author)
            ->get(route('author.dashboard', [
                'search' => 'Author Filter',
                'status' => 'published',
                'category_id' => $programming->id,
            ]))
            ->assertOk()
            ->assertSee($matching->title)
            ->assertSee('1 artikel ditemukan');
    }

    private function article(User $author, Category $category, string $title, string $status = 'published'): Article
    {
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
