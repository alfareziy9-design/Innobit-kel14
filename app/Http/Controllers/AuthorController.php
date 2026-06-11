<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCollection;
use App\Models\ArticleView;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function dashboard(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:draft,review,published,rejected,revision_review,revision_rejected'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'sort' => ['nullable', 'in:newest,oldest,title,views'],
        ]);

        $authorId = $request->user()->id;
        $baseArticleQuery = Article::where('author_id', $authorId);
        $articleCount = (clone $baseArticleQuery)->count();
        $publishedCount = (clone $baseArticleQuery)->where('status', 'published')->count();
        $draftCount = (clone $baseArticleQuery)->where('status', 'draft')->count();
        $reviewCount = (clone $baseArticleQuery)->where('status', 'review')->count();
        $rejectedCount = (clone $baseArticleQuery)->where('status', 'rejected')->count();
        $pendingRevisionCount = (clone $baseArticleQuery)->whereHas('pendingRevision')->count();

        $articles = Article::with([
                'category',
                'latestReview.reviewer',
                'reviews.reviewer',
                'pendingRevision',
                'latestRevision.reviewer',
            ])
            ->withCount(['views', 'favorites', 'collections', 'quizAttempts'])
            ->withAvg('quizAttempts', 'score')
            ->where('author_id', $authorId)
            ->when($filters['search'] ?? null, function ($query, $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('summary', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, function ($query, $status): void {
                match ($status) {
                    'revision_review' => $query->whereHas('revisions', fn ($query) => $query->where('status', 'review')),
                    'revision_rejected' => $query->whereHas('revisions', fn ($query) => $query->where('status', 'rejected')),
                    default => $query->where('status', $status),
                };
            })
            ->when($filters['category_id'] ?? null, fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->when(
                ($filters['sort'] ?? 'newest') === 'oldest',
                fn ($query) => $query->oldest(),
                fn ($query) => match ($filters['sort'] ?? 'newest') {
                    'title' => $query->orderBy('title'),
                    'views' => $query->orderByDesc('views_count'),
                    default => $query->latest(),
                }
            )
            ->paginate(20)
            ->withQueryString();

        $articleIds = (clone $baseArticleQuery)->pluck('id');
        $performance = [
            'views' => ArticleView::whereIn('article_id', $articleIds)->count(),
            'favorites' => Favorite::whereIn('article_id', $articleIds)->count(),
            'collections' => ArticleCollection::whereIn('article_id', $articleIds)->count(),
            'quiz_attempts' => QuizAttempt::whereIn('article_id', $articleIds)->count(),
            'average_quiz_score' => round((float) QuizAttempt::whereIn('article_id', $articleIds)->avg('score')),
            'views_30_days' => ArticleView::whereIn('article_id', $articleIds)->where('viewed_at', '>=', now()->subDays(30))->count(),
            'quiz_attempts_30_days' => QuizAttempt::whereIn('article_id', $articleIds)->where('submitted_at', '>=', now()->subDays(30))->count(),
        ];

        $topArticles = Article::with('category')
            ->withCount('views')
            ->where('author_id', $authorId)
            ->orderByDesc('views_count')
            ->take(5)
            ->get();
        $categories = Category::orderBy('name')->get();

        return view('author.dashboard', compact(
            'articles',
            'articleCount',
            'publishedCount',
            'draftCount',
            'reviewCount',
            'rejectedCount',
            'pendingRevisionCount',
            'performance',
            'topArticles',
            'categories'
        ));
    }
}
