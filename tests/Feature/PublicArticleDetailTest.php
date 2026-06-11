<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleView;
use App\Models\Category;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicArticleDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_guest_can_open_published_article_preview_only(): void
    {
        [$article, $media] = $this->publishedArticleWithThumbnail();

        $this->get(route('articles.show', $article->slug))
            ->assertOk()
            ->assertSee($article->title)
            ->assertSee($article->summary)
            ->assertSee($media->url, false)
            ->assertSee('Login untuk lanjut membaca')
            ->assertDontSee('Konten lengkap yang hanya untuk pengguna login.')
            ->assertDontSee('Quiz singkat')
            ->assertDontSee('Favourite')
            ->assertDontSee('Save Byte');

        $this->assertDatabaseCount('article_views', 0);
    }

    public function test_guest_article_preview_sets_intended_login_destination(): void
    {
        [$article] = $this->publishedArticleWithThumbnail();

        $this->get(route('articles.show', $article->slug))
            ->assertSessionHas('url.intended', route('articles.show', $article->slug).'#mulai-belajar');
    }

    public function test_user_returns_to_article_after_login_from_preview(): void
    {
        [$article] = $this->publishedArticleWithThumbnail();
        $user = User::factory()->create(['role' => 'user', 'email' => 'reader@example.com']);

        $this->get(route('articles.show', $article->slug));

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('articles.show', $article->slug).'#mulai-belajar');
    }

    public function test_authenticated_user_gets_full_article_and_learning_activity(): void
    {
        [$article] = $this->publishedArticleWithThumbnail();
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('articles.show', $article->slug))
            ->assertOk()
            ->assertSee('Konten lengkap yang hanya untuk pengguna login.')
            ->assertSee('Quiz singkat')
            ->assertSee('Favorit')
            ->assertSee('Simpan')
            ->assertSee('Bagikan')
            ->assertSee('Simpan PDF')
            ->assertSee('data-share-open', false)
            ->assertSee('data-save-pdf', false)
            ->assertSee('share-article-modal')
            ->assertSee('article-print-area');

        $this->assertDatabaseHas('article_views', [
            'user_id' => $user->id,
            'article_id' => $article->id,
        ]);
    }

    public function test_unpublished_articles_are_not_visible_publicly(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $category = Category::create(['name' => 'Status', 'slug' => 'status']);

        foreach (['draft', 'review', 'rejected'] as $status) {
            $article = $this->createArticle($author, $category, "Artikel {$status}", $status);

            $this->get(route('articles.show', $article->slug))
                ->assertOk()
                ->assertSee('Artikel tidak ditemukan.')
                ->assertDontSee($article->summary);
        }
    }

    private function publishedArticleWithThumbnail(): array
    {
        $author = User::factory()->create(['role' => 'author']);
        $category = Category::create(['name' => 'Microlearning', 'slug' => 'microlearning']);
        $article = $this->createArticle($author, $category, 'Artikel Publik', 'published');
        $path = 'article/thumbnails/public-preview.png';

        Storage::disk('public')->put($path, 'thumbnail');

        $media = Media::create([
            'user_id' => $author->id,
            'disk' => 'public',
            'folder' => 'article/thumbnails',
            'path' => $path,
            'original_name' => 'public-preview.png',
            'mime_type' => 'image/png',
            'size' => 9,
            'usage' => 'thumbnail',
        ]);

        $article->update(['thumbnail_media_id' => $media->id]);
        $quiz = $article->normalizedQuiz()->create([
            'title' => 'Quiz '.$article->title,
            'is_active' => true,
        ]);
        $question = $quiz->questions()->create([
            'question' => 'Apa langkah terbaik setelah membaca artikel ini?',
            'position' => 1,
        ]);

        foreach (['Membaca judul saja.', 'Merangkum ide utama.', 'Melewati materi.'] as $index => $option) {
            $question->options()->create([
                'option_text' => $option,
                'is_correct' => $index === 1,
                'position' => $index + 1,
            ]);
        }

        ArticleView::query()->delete();

        return [$article->refresh(), $media];
    }

    private function createArticle(User $author, Category $category, string $title, string $status): Article
    {
        return Article::create([
            'category_id' => $category->id,
            'author_id' => $author->id,
            'title' => $title,
            'slug' => str($title)->slug(),
            'summary' => 'Ringkasan artikel publik.',
            'content' => 'Konten lengkap yang hanya untuk pengguna login.',
            'status' => $status,
        ]);
    }
}
