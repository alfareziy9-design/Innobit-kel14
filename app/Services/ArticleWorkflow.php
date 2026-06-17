<?php

namespace App\Services;

use App\Enums\ArticleRevisionStatus;
use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleRevision;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\DB;

class ArticleWorkflow
{
    public function submit(Article $article): Article
    {
        return DB::transaction(function () use ($article): Article {
            $lockedArticle = Article::whereKey($article->id)->lockForUpdate()->firstOrFail();

            abort_unless(
                in_array($lockedArticle->status, [ArticleStatus::Draft->value, ArticleStatus::Rejected->value], true),
                422,
                'Artikel hanya dapat dikirim dari status Draft atau Perlu Perbaikan.'
            );

            $lockedArticle->update(['status' => ArticleStatus::Review->value]);

            return $lockedArticle;
        });
    }

    public function approve(Article $article, User $reviewer, ?string $note): Article
    {
        return DB::transaction(function () use ($article, $reviewer, $note): Article {
            $lockedArticle = Article::whereKey($article->id)->lockForUpdate()->firstOrFail();
            $this->ensureReviewableArticle($lockedArticle, $reviewer);

            $lockedArticle->reviews()->create([
                'reviewer_id' => $reviewer->id,
                'decision' => 'approved',
                'note' => $note,
            ]);
            $lockedArticle->update(['status' => ArticleStatus::Published->value]);

            return $lockedArticle;
        });
    }

    public function reject(Article $article, User $reviewer, string $note): Article
    {
        return DB::transaction(function () use ($article, $reviewer, $note): Article {
            $lockedArticle = Article::whereKey($article->id)->lockForUpdate()->firstOrFail();
            $this->ensureReviewableArticle($lockedArticle, $reviewer);

            $lockedArticle->reviews()->create([
                'reviewer_id' => $reviewer->id,
                'decision' => 'rejected',
                'note' => $note,
            ]);
            $lockedArticle->update(['status' => ArticleStatus::Rejected->value]);

            return $lockedArticle;
        });
    }

    public function approveRevision(
        Article $article,
        ArticleRevision $revision,
        User $reviewer,
        ?string $note,
        Closure $applyRevision
    ): ArticleRevision {
        return DB::transaction(function () use ($article, $revision, $reviewer, $note, $applyRevision): ArticleRevision {
            $lockedRevision = ArticleRevision::whereKey($revision->id)->lockForUpdate()->firstOrFail();
            $lockedArticle = Article::whereKey($article->id)->lockForUpdate()->firstOrFail();
            $this->ensureReviewableRevision($lockedArticle, $lockedRevision, $reviewer);

            $applyRevision($lockedArticle, $lockedRevision);

            $lockedRevision->update([
                'status' => ArticleRevisionStatus::Approved->value,
                'reviewer_id' => $reviewer->id,
                'review_note' => $note,
                'reviewed_at' => now(),
            ]);

            return $lockedRevision;
        });
    }

    public function rejectRevision(
        Article $article,
        ArticleRevision $revision,
        User $reviewer,
        string $note
    ): ArticleRevision {
        return DB::transaction(function () use ($article, $revision, $reviewer, $note): ArticleRevision {
            $lockedRevision = ArticleRevision::whereKey($revision->id)->lockForUpdate()->firstOrFail();
            $lockedArticle = Article::whereKey($article->id)->lockForUpdate()->firstOrFail();
            $this->ensureReviewableRevision($lockedArticle, $lockedRevision, $reviewer);

            $lockedRevision->update([
                'status' => ArticleRevisionStatus::Rejected->value,
                'reviewer_id' => $reviewer->id,
                'review_note' => $note,
                'reviewed_at' => now(),
            ]);

            return $lockedRevision;
        });
    }

    private function ensureReviewableArticle(Article $article, User $reviewer): void
    {
        abort_unless(
            $article->status === ArticleStatus::Review->value,
            422,
            'Hanya artikel yang Menunggu Review yang dapat diputuskan.'
        );
        abort_if(
            $article->author_id === $reviewer->id,
            422,
            'Admin tidak dapat mereview artikel miliknya sendiri melalui antrean author.'
        );
    }

    private function ensureReviewableRevision(Article $article, ArticleRevision $revision, User $reviewer): void
    {
        abort_unless($revision->article_id === $article->id, 404);
        abort_unless(
            $revision->status === ArticleRevisionStatus::Review->value,
            422,
            'Hanya pembaruan yang Menunggu Review yang dapat diputuskan.'
        );
        abort_if(
            $article->author_id === $reviewer->id,
            422,
            'Admin tidak dapat mereview pembaruan artikelnya sendiri.'
        );
    }
}
