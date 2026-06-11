<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertSessionHasNoErrors();

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
            ->assertSessionHasNoErrors();

        $article->refresh();
        $revision->refresh();

        $this->assertSame('published', $article->status);
        $this->assertSame('Artikel Workflow', $article->title);
        $this->assertSame('rejected', $revision->status);
        $this->assertSame('Perjelas contoh.', $revision->review_note);

        $this->actingAs($author)
            ->get(route('author.dashboard'))
            ->assertOk()
            ->assertSee('Revisi published ditolak:')
            ->assertSee('Perjelas contoh.');
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
}
