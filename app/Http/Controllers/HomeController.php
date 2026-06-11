<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);
        $hasActiveFilters = filled($filters['search'] ?? null) || filled($filters['category_id'] ?? null);
        $categories = Category::withCount([
            'articles as published_articles_count' => fn ($query) => $query->where('status', 'published'),
        ])->orderBy('name')->get();

        $articles = Article::with(['category', 'author', 'thumbnailMedia'])
            ->when($user, fn ($query) => $query->withExists([
                'favorites as is_favorited' => fn ($query) => $query->where('user_id', $user->id),
            ]))
            ->where('status', 'published')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('summary', 'like', "%{$search}%");
                });
            })
            ->when($filters['category_id'] ?? null, fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->latest()
            ->when(
                $hasActiveFilters,
                fn ($query) => $query->paginate(16)->withQueryString(),
                fn ($query) => $query->take(7)->get()
            );

        $recommendedArticles = collect();
        $popularArticles = collect();
        $discoveryArticles = collect();

        if (! $hasActiveFilters) {
            $recommendedArticles = Article::with(['category', 'author', 'thumbnailMedia'])
                ->when($user, fn ($query) => $query->withExists([
                    'favorites as is_favorited' => fn ($query) => $query->where('user_id', $user->id),
                ]))
                ->where('status', 'published')
                ->latest()
                ->take(7)
                ->get();

            $popularArticles = Article::with(['category', 'author', 'thumbnailMedia'])
                ->withCount('views')
                ->when($user, fn ($query) => $query->withExists([
                    'favorites as is_favorited' => fn ($query) => $query->where('user_id', $user->id),
                ]))
                ->where('status', 'published')
                ->orderByDesc('views_count')
                ->latest()
                ->take(7)
                ->get();

            $discoveryArticles = Article::with(['category', 'author', 'thumbnailMedia'])
                ->when($user, fn ($query) => $query->withExists([
                    'favorites as is_favorited' => fn ($query) => $query->where('user_id', $user->id),
                ]))
                ->where('status', 'published')
                ->inRandomOrder()
                ->take(7)
                ->get();
        }

        return view('home', compact(
            'articles',
            'categories',
            'filters',
            'hasActiveFilters',
            'recommendedArticles',
            'popularArticles',
            'discoveryArticles'
        ));
    }

    public function explore(Request $request, string $section)
    {
        abort_unless(in_array($section, ['recommended', 'latest', 'popular', 'discovery'], true), 404);

        $user = $request->user();
        $sectionDetails = [
            'recommended' => [
                'title' => 'Rekomendasi Untukmu',
                'description' => 'Pilihan artikel terbaru untuk menemani proses belajarmu.',
            ],
            'latest' => [
                'title' => 'Baru Terbit',
                'description' => 'Semua artikel terbaru dari InnoBit.',
            ],
            'popular' => [
                'title' => 'Sedang Populer',
                'description' => 'Artikel yang paling banyak dibaca oleh pengguna InnoBit.',
            ],
            'discovery' => [
                'title' => 'Yuk Cari Tau Hal Baru',
                'description' => 'Jelajahi topik lain yang mungkin belum pernah kamu baca.',
            ],
        ];

        $articles = Article::with(['category', 'author', 'thumbnailMedia'])
            ->when($section === 'popular', fn ($query) => $query->withCount('views'))
            ->when($user, fn ($query) => $query->withExists([
                'favorites as is_favorited' => fn ($query) => $query->where('user_id', $user->id),
            ]))
            ->where('status', 'published')
            ->when(
                $section === 'popular',
                fn ($query) => $query->orderByDesc('views_count')->latest(),
                fn ($query) => $query->latest()
            )
            ->paginate(16);

        return view('articles.explore', [
            'articles' => $articles,
            'sectionTitle' => $sectionDetails[$section]['title'],
            'sectionDescription' => $sectionDetails[$section]['description'],
        ]);
    }

    public function about()
    {
        return view('about');
    }

    public function contact()
    {
        return view('contact');
    }

    public function sendContact(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'message' => ['required', 'string', 'max:5000'],
            'website' => ['nullable', 'string', 'max:0'],
        ], [
            'name.required' => 'Nama harus diisi.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'message.required' => 'Pesan harus diisi.',
            'message.max' => 'Pesan maksimal 5000 karakter.',
            'website.max' => 'Pesan tidak dapat diproses.',
        ]);

        ContactMessage::create([
            'user_id' => $request->user()?->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'message' => $data['message'],
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
        ]);

        return back()->with('success', 'Pesan Anda telah terkirim. Kami akan menghubungi Anda segera.');
    }
}
