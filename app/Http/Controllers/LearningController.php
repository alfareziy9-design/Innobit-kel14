<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCollection;
use App\Models\ArticleView;
use App\Models\Favorite;
use App\Models\LearningCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LearningController extends Controller
{
    public function history(Request $request)
    {
        $rows = ArticleView::query()
            ->selectRaw('article_id, max(viewed_at) as last_viewed_at')
            ->where('user_id', $request->user()->id)
            ->groupBy('article_id')
            ->latest('last_viewed_at')
            ->get();

        $articles = $this->articlesInOrder($rows->pluck('article_id')->all());
        $articleMeta = $rows->keyBy('article_id');

        return view('learning.index', [
            'title' => 'Histori Belajar',
            'subtitle' => 'Artikel yang terakhir kamu buka.',
            'emptyTitle' => 'Belum ada histori',
            'emptyMessage' => 'Buka artikel untuk mulai membangun histori belajar.',
            'articles' => $articles,
            'articleMeta' => $articleMeta,
            'metaKey' => 'last_viewed_at',
        ]);
    }

    public function favorites(Request $request)
    {
        $rows = Favorite::query()
            ->where('user_id', $request->user()->id)
            ->latest('created_at')
            ->get();

        return view('learning.index', [
            'title' => 'Favorit',
            'subtitle' => 'Artikel yang kamu tandai sebagai favorit.',
            'emptyTitle' => 'Belum ada favorit',
            'emptyMessage' => 'Tekan tombol hati di card atau detail artikel untuk menambah favorit.',
            'articles' => $this->articlesInOrder($rows->pluck('article_id')->all()),
            'articleMeta' => $rows->keyBy('article_id'),
            'metaKey' => 'created_at',
        ]);
    }

    public function collections(Request $request)
    {
        $articleRows = ArticleCollection::query()
            ->where('user_id', $request->user()->id)
            ->latest('created_at')
            ->get();
        $articles = $this->articlesInOrder($articleRows->pluck('article_id')->all());
        $articleMeta = $articleRows->keyBy('article_id');
        $articlesById = $articles->keyBy('id');

        $collectionGroups = LearningCollection::query()
            ->with(['items' => fn ($query) => $query->latest('created_at')])
            ->where('user_id', $request->user()->id)
            ->latest('updated_at')
            ->get()
            ->map(function (LearningCollection $collection) use ($articlesById) {
                $groupArticles = $collection->items
                    ->map(fn ($item) => $articlesById->get($item->article_id))
                    ->filter()
                    ->values();

                return [
                    'id' => $collection->id,
                    'name' => $collection->name,
                    'articles' => $groupArticles,
                    'latest_saved_at' => $collection->items->max('created_at'),
                    'is_empty' => $groupArticles->isEmpty(),
                ];
            })
            ->values();

        return view('learning.index', [
            'title' => 'Koleksi',
            'subtitle' => 'Artikel yang kamu simpan dalam album koleksi custom.',
            'emptyTitle' => 'Belum ada koleksi',
            'emptyMessage' => 'Gunakan tombol simpan di detail artikel untuk menambah koleksi.',
            'articles' => $articles,
            'articleMeta' => $articleMeta,
            'metaKey' => 'created_at',
            'collectionGroups' => $collectionGroups,
        ]);
    }

    public function storeCollection(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:80',
                Rule::unique('learning_collections', 'name')
                    ->where(fn ($query) => $query->where('user_id', $request->user()->id)),
            ],
        ], [
            'name.required' => 'Nama koleksi wajib diisi.',
            'name.max' => 'Nama koleksi maksimal 80 karakter.',
            'name.unique' => 'Nama koleksi sudah digunakan.',
        ]);

        LearningCollection::create([
            'user_id' => $request->user()->id,
            'name' => $data['name'],
        ]);

        return back()->with('success', 'Koleksi baru berhasil dibuat.');
    }

    public function updateCollection(Request $request, LearningCollection $collection)
    {
        abort_unless($collection->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:80',
                Rule::unique('learning_collections', 'name')
                    ->where(fn ($query) => $query->where('user_id', $request->user()->id))
                    ->ignore($collection->id),
            ],
        ], [
            'name.required' => 'Nama koleksi wajib diisi.',
            'name.max' => 'Nama koleksi maksimal 80 karakter.',
            'name.unique' => 'Nama koleksi sudah digunakan.',
        ]);

        $collection->update($data);

        return back()->with('success', 'Nama koleksi berhasil diperbarui.');
    }

    public function destroyCollection(Request $request, LearningCollection $collection)
    {
        abort_unless($collection->user_id === $request->user()->id, 403);

        DB::transaction(function () use ($collection): void {
            $collection->items()->delete();
            $collection->delete();
        });

        return back()->with('success', 'Koleksi berhasil dihapus.');
    }

    private function articlesInOrder(array $articleIds)
    {
        if ($articleIds === []) {
            return collect();
        }

        $articles = Article::with(['category', 'author', 'thumbnailMedia'])
            ->whereIn('id', $articleIds)
            ->where('status', 'published')
            ->get()
            ->keyBy('id');

        return collect($articleIds)
            ->map(fn ($articleId) => $articles->get($articleId))
            ->filter()
            ->values();
    }
}
