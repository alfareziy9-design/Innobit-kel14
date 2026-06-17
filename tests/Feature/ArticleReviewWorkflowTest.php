<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticleReviewWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_can_submit_draft_and_admin_can_approve_it(): void
    {
        [$admin, $author, $article] = $this->workflowFixtures('draft');

        $this->actingAs($author)
            ->post(route('articles.submit-review', $article))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('articles', ['id' => $article->id, 'status' => 'review']);

        $this->actingAs($admin)
            ->post(route('admin.articles.approve', $article), ['note' => 'Siap diterbitkan.'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('articles', ['id' => $article->id, 'status' => 'published']);
        $this->assertDatabaseHas('article_reviews', [
            'article_id' => $article->id,
            'reviewer_id' => $admin->id,
            'decision' => 'approved',
            'note' => 'Siap diterbitkan.',
        ]);
    }

    public function test_reject_requires_note_and_author_can_resubmit_revision(): void
    {
        [$admin, $author, $article] = $this->workflowFixtures('review');

        $this->actingAs($admin)
            ->post(route('admin.articles.reject', $article))
            ->assertSessionHasErrors('note');

        $this->assertDatabaseHas('articles', ['id' => $article->id, 'status' => 'review']);

        $this->actingAs($admin)
            ->post(route('admin.articles.reject', $article), ['note' => 'Tambahkan contoh praktik.'])
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Artikel dikembalikan kepada author untuk diperbaiki.');

        $this->actingAs($author)
            ->put(route('articles.update', $article), [
                'title' => 'Artikel Direvisi',
                'category_id' => $article->category_id,
                'summary' => 'Ringkasan hasil revisi.',
                'content' => 'Konten dengan contoh praktik.',
                'quizzes' => $this->quizPayload(),
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'title' => 'Artikel Direvisi',
            'status' => 'rejected',
        ]);

        $this->actingAs($author)
            ->post(route('articles.submit-review', $article))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('articles', ['id' => $article->id, 'status' => 'review']);
        $this->assertDatabaseHas('article_reviews', [
            'article_id' => $article->id,
            'decision' => 'rejected',
            'note' => 'Tambahkan contoh praktik.',
        ]);
    }

    public function test_author_dashboard_shows_rejection_reason_and_review_history(): void
    {
        [$admin, $author, $article] = $this->workflowFixtures('review');

        $this->actingAs($admin)->post(route('admin.articles.reject', $article), [
            'note' => 'Perjelas bagian kesimpulan.',
        ]);

        $this->actingAs($author)
            ->get(route('author.dashboard'))
            ->assertOk()
            ->assertSee('Rejected')
            ->assertSee('Alasan revisi:')
            ->assertSee('Perjelas bagian kesimpulan.')
            ->assertSee('Riwayat review (1)');
    }

    public function test_author_cannot_edit_article_during_review(): void
    {
        [, $author, $article] = $this->workflowFixtures('review');

        $this->actingAs($author)->get(route('articles.edit', $article))->assertForbidden();
        $this->actingAs($author)->put(route('articles.update', $article), [
            'title' => 'Perubahan Terlarang',
            'category_id' => $article->category_id,
            'summary' => 'Ringkasan.',
            'content' => 'Konten.',
            'quizzes' => $this->quizPayload(),
        ])->assertForbidden();
    }

    public function test_editing_published_article_by_author_creates_pending_revision_and_keeps_public_version(): void
    {
        [, $author, $article] = $this->workflowFixtures('published');

        $this->actingAs($author)
            ->put(route('articles.update', $article), [
                'title' => 'Artikel Published Direvisi',
                'category_id' => $article->category_id,
                'summary' => 'Ringkasan revisi.',
                'content' => 'Konten revisi.',
                'quizzes' => $this->quizPayload(),
            ])
            ->assertSessionHasNoErrors();

        $article->refresh();

        $this->assertSame('published', $article->status);
        $this->assertSame('Artikel Workflow', $article->title);
        $this->assertDatabaseHas('article_revisions', [
            'article_id' => $article->id,
            'author_id' => $author->id,
            'title' => 'Artikel Published Direvisi',
            'status' => 'review',
        ]);
        $this->actingAs(User::factory()->create(['role' => 'user']))
            ->get(route('articles.show', $article->slug))
            ->assertOk()
            ->assertSee('Artikel Workflow')
            ->assertDontSee('Artikel Published Direvisi');
    }

    public function test_admin_can_approve_published_article_revision(): void
    {
        [$admin, $author, $article] = $this->workflowFixtures('published');

        $this->actingAs($author)
            ->put(route('articles.update', $article), [
                'title' => 'Artikel Revisi Disetujui',
                'category_id' => $article->category_id,
                'summary' => 'Ringkasan revisi disetujui.',
                'content' => 'Konten revisi disetujui.',
                'quizzes' => $this->quizPayload(),
            ])
            ->assertSessionHasNoErrors();

        $revision = $article->revisions()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.articles.revisions.approve', [$article, $revision]), ['note' => 'Revisi siap tayang.'])
            ->assertSessionHasNoErrors();

        $article->refresh();
        $revision->refresh();

        $this->assertSame('published', $article->status);
        $this->assertSame('Artikel Revisi Disetujui', $article->title);
        $this->assertSame('approved', $revision->status);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'article.revision_approved',
            'subject_id' => $revision->id,
        ]);
    }

    public function test_admin_can_reject_published_article_revision_without_changing_public_article(): void
    {
        [$admin, $author, $article] = $this->workflowFixtures('published');

        $this->actingAs($author)
            ->put(route('articles.update', $article), [
                'title' => 'Artikel Revisi Ditolak',
                'category_id' => $article->category_id,
                'summary' => 'Ringkasan revisi ditolak.',
                'content' => 'Konten revisi ditolak.',
                'quizzes' => $this->quizPayload(),
            ])
            ->assertSessionHasNoErrors();

        $revision = $article->revisions()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.articles.revisions.reject', [$article, $revision]), ['note' => 'Perjelas contoh.'])
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Pembaruan dikembalikan kepada author untuk diperbaiki.');

        $article->refresh();
        $revision->refresh();

        $this->assertSame('published', $article->status);
        $this->assertSame('Artikel Workflow', $article->title);
        $this->assertSame('rejected', $revision->status);
        $this->assertSame('Perjelas contoh.', $revision->review_note);

        $this->actingAs($author)
            ->get(route('author.dashboard'))
            ->assertOk()
            ->assertSee('Pembaruan Perlu Perbaikan')
            ->assertSee('Alasan pembaruan perlu diperbaiki:')
            ->assertSee('Perjelas contoh.');
    }

    public function test_admin_can_edit_article_content_and_thumbnail_during_review(): void
    {
        Storage::fake('public');
        [$admin, , $article] = $this->workflowFixtures('review');

        $this->actingAs($admin)
            ->get(route('admin.articles.review.edit', $article))
            ->assertOk()
            ->assertSee('Edit Artikel dalam Review')
            ->assertSee('Thumbnail Saat Ini')
            ->assertSee('data-summernote-editor', false)
            ->assertSee('summernote@0.9.1/dist/summernote-lite.min.js', false)
            ->assertSee(route('admin.articles.review.update', $article), false);

        $this->actingAs($admin)
            ->put(route('admin.articles.review.update', $article), [
                'title' => 'Artikel Diperbaiki Admin',
                'category_id' => $article->category_id,
                'summary' => 'Ringkasan diperbaiki admin.',
                'content' => '<p>Konten diperbaiki admin.</p>',
                'quizzes' => $this->quizPayload(),
                'thumbnail' => $this->fakePng('review-thumbnail.png'),
            ])
            ->assertRedirect(route('admin.articles.review', $article))
            ->assertSessionHasNoErrors();

        $article->refresh()->load('thumbnailMedia');

        $this->assertSame('review', $article->status);
        $this->assertSame('Artikel Diperbaiki Admin', $article->title);
        $this->assertNotNull($article->thumbnailMedia);
        $this->assertStringStartsWith('/storage/article/thumbnails/', $article->thumbnailMedia->url);
        Storage::disk('public')->assertExists($article->thumbnailMedia->path);
    }

    public function test_admin_can_edit_pending_revision_without_changing_public_article(): void
    {
        Storage::fake('public');
        [$admin, $author, $article] = $this->workflowFixtures('published');
        $revision = $article->revisions()->create([
            'author_id' => $author->id,
            'category_id' => $article->category_id,
            'title' => 'Pembaruan Awal',
            'slug' => 'pembaruan-awal',
            'summary' => 'Ringkasan pembaruan awal.',
            'content' => '<p>Konten pembaruan awal.</p>',
            'quiz_data' => $this->quizPayload(),
            'status' => 'review',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.articles.revisions.review.edit', [$article, $revision]))
            ->assertOk()
            ->assertSee('Edit Pembaruan dalam Review')
            ->assertSee('Pembaruan Awal')
            ->assertSee(route('admin.articles.revisions.review.update', [$article, $revision]), false);

        $this->actingAs($admin)
            ->put(route('admin.articles.revisions.review.update', [$article, $revision]), [
                'title' => 'Pembaruan Diperbaiki Admin',
                'category_id' => $article->category_id,
                'summary' => 'Ringkasan pembaruan diperbaiki.',
                'content' => '<p>Konten pembaruan diperbaiki admin.</p>',
                'quizzes' => $this->quizPayload(),
                'thumbnail' => $this->fakePng('revision-thumbnail.png'),
            ])
            ->assertRedirect(route('admin.articles.revisions.review', [$article, $revision]))
            ->assertSessionHasNoErrors();

        $article->refresh();
        $revision->refresh()->load('thumbnailMedia');

        $this->assertSame('Artikel Workflow', $article->title);
        $this->assertSame('published', $article->status);
        $this->assertSame('Pembaruan Diperbaiki Admin', $revision->title);
        $this->assertSame('review', $revision->status);
        $this->assertNotNull($revision->thumbnailMedia);
        Storage::disk('public')->assertExists($revision->thumbnailMedia->path);
    }

    public function test_author_cannot_edit_while_published_revision_is_waiting_for_review(): void
    {
        [, $author, $article] = $this->workflowFixtures('published');

        $this->actingAs($author)
            ->put(route('articles.update', $article), [
                'title' => 'Pembaruan Pertama',
                'category_id' => $article->category_id,
                'summary' => 'Ringkasan pembaruan.',
                'content' => 'Konten pembaruan.',
                'quizzes' => $this->quizPayload(),
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($author)->get(route('articles.edit', $article))->assertForbidden();

        $this->actingAs($author)
            ->post(route('articles.revisions.store', $article), [
                'title' => 'Pembaruan Kedua',
                'category_id' => $article->category_id,
                'summary' => 'Tidak boleh ditimpa.',
                'content' => 'Tidak boleh ditimpa.',
                'quizzes' => $this->quizPayload(),
            ])
            ->assertStatus(422);

        $this->assertDatabaseCount('article_revisions', 1);
        $this->assertDatabaseHas('article_revisions', [
            'article_id' => $article->id,
            'title' => 'Pembaruan Pertama',
            'status' => 'review',
        ]);
    }

    public function test_author_cannot_delete_review_or_published_article(): void
    {
        [, $author, $reviewArticle] = $this->workflowFixtures('review');
        $publishedArticle = Article::create([
            'category_id' => $reviewArticle->category_id,
            'author_id' => $author->id,
            'title' => 'Artikel Terbit',
            'slug' => 'artikel-terbit',
            'summary' => 'Ringkasan.',
            'content' => 'Konten.',
            'status' => 'published',
        ]);

        $this->actingAs($author)->delete(route('articles.destroy', $reviewArticle))->assertStatus(422);
        $this->actingAs($author)->delete(route('articles.destroy', $publishedArticle))->assertStatus(422);

        $this->assertDatabaseHas('articles', ['id' => $reviewArticle->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('articles', ['id' => $publishedArticle->id, 'deleted_at' => null]);
    }

    public function test_author_dashboard_keeps_publication_and_revision_status_separate(): void
    {
        [, $author, $article] = $this->workflowFixtures('published');

        $article->revisions()->create([
            'author_id' => $author->id,
            'category_id' => $article->category_id,
            'title' => 'Pembaruan Menunggu Review',
            'slug' => 'pembaruan-menunggu-review',
            'summary' => 'Ringkasan.',
            'content' => 'Konten.',
            'status' => 'review',
        ]);

        $this->actingAs($author)
            ->get(route('author.dashboard'))
            ->assertOk()
            ->assertSee('Terbit')
            ->assertSee('Pembaruan Menunggu Review')
            ->assertDontSee('Buat pembaruan');
    }

    public function test_non_admin_cannot_approve_or_reject_articles(): void
    {
        [, $author, $article] = $this->workflowFixtures('review');

        $this->actingAs($author)
            ->post(route('admin.articles.approve', $article))
            ->assertForbidden();
        $this->actingAs($author)
            ->post(route('admin.articles.reject', $article), ['note' => 'Tidak valid.'])
            ->assertForbidden();
    }

    public function test_invalid_review_transitions_are_rejected(): void
    {
        [$admin, $author, $article] = $this->workflowFixtures('draft');

        $this->actingAs($admin)
            ->post(route('admin.articles.approve', $article))
            ->assertStatus(422);

        $article->update(['status' => 'published']);

        $this->actingAs($author)
            ->post(route('articles.submit-review', $article))
            ->assertStatus(422);
    }

    private function workflowFixtures(string $status): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $author = User::factory()->create(['role' => 'author']);
        $category = Category::create(['name' => 'Workflow', 'slug' => 'workflow']);
        $article = Article::create([
            'category_id' => $category->id,
            'author_id' => $author->id,
            'title' => 'Artikel Workflow',
            'slug' => 'artikel-workflow',
            'summary' => 'Ringkasan artikel workflow.',
            'content' => 'Konten artikel workflow.',
            'status' => $status,
        ]);

        return [$admin, $author, $article];
    }

    private function quizPayload(): array
    {
        return [[
            'question' => 'Apa langkah terbaik setelah membaca artikel ini?',
            'options' => [
                'Membaca judulnya saja.',
                'Merangkum ide utama dan mencoba contoh sederhana.',
                'Melewati materi tanpa praktik.',
            ],
            'correct_option' => 1,
        ]];
    }

    private function fakePng(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl2nS8AAAAASUVORK5CYII=')
        );
    }
}
