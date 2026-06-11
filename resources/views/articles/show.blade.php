@extends('layouts.app')

@section('title', ($article?->title ?? 'Artikel tidak ditemukan').' - InnoBit')

@section('content')
@php
    $fallbackImages = [
        asset('assets/img/microlearning-data-dashboard.png'),
        asset('assets/img/microlearning-clean-code.png'),
        asset('assets/img/microlearning-time-focus.png'),
    ];
    $coverImage = $article
        ? ($article->thumbnailMedia?->url ?? $fallbackImages[($article->id - 1) % count($fallbackImages)])
        : null;
    $isRichContent = $article && preg_match('/<(p|h2|h3|h4|ul|ol|figure|table|blockquote)\b[^>]*>.*<\/\1>|<img\b[^>]+\/uploads\/artikel\/content\//is', $article->content);
    $contentLines = $article ? preg_split('/\R{2,}|\R/', trim($article->content)) : [];
    $contentBlocks = collect($contentLines)
        ->map(fn ($line) => trim($line))
        ->filter()
        ->map(function ($line) {
            $imagePattern = '/\.(gif|jpe?g|png|webp)(\?.*)?$/i';
            $markdownImagePattern = '/^!\[(?<alt>[^\]]*)\]\((?<src>[^)]+)\)$/';
            $htmlImagePattern = '/^<img\s+[^>]*src=["\'](?<src>[^"\']+)["\'][^>]*>$/i';

            if (preg_match($markdownImagePattern, $line, $match) || preg_match($htmlImagePattern, $line, $match)) {
                $src = trim($match['src']);
                if (preg_match($imagePattern, parse_url($src, PHP_URL_PATH) ?? $src)) {
                    return [
                        'type' => 'image',
                        'src' => \Illuminate\Support\Str::startsWith($src, ['http://', 'https://', '/']) ? $src : asset(ltrim($src, '/')),
                        'alt' => trim($match['alt'] ?? 'Gambar artikel'),
                    ];
                }
            }

            if (preg_match($imagePattern, parse_url($line, PHP_URL_PATH) ?? $line)) {
                return [
                    'type' => 'image',
                    'src' => \Illuminate\Support\Str::startsWith($line, ['http://', 'https://', '/']) ? $line : asset(ltrim($line, '/')),
                    'alt' => 'Gambar artikel',
                ];
            }

            return ['type' => 'text', 'content' => $line];
        })
        ->values();
    $quizQuestions = $article?->normalizedQuiz?->questions?->map(function ($question) {
        $options = $question->options->values();
        $correctOption = $options->firstWhere('is_correct', true);

        return [
            'question' => $question->question,
            'options' => $options->map(fn ($option) => [
                'id' => $option->id,
                'text' => $option->option_text,
            ])->all(),
            'correct_option' => $correctOption?->id,
            'is_normalized' => true,
        ];
    }) ?? collect();

    if ($quizQuestions->isEmpty() && $article?->quiz) {
        $quizQuestions = collect([[
            'question' => $article->quiz['question'],
            'options' => collect($article->quiz['options'] ?? [])->take(3)->map(fn ($option, $index) => [
                'id' => $index,
                'text' => $option,
            ])->all(),
            'correct_option' => $article->quiz['correct_option'] ?? 0,
            'is_normalized' => false,
        ]]);
    }
    $readMinutes = $article?->reading_minutes ?? 1;
    $articleUrl = $article ? route('articles.show', $article->slug) : url()->current();
    $shareText = $article ? $article->title.' - InnoBit' : 'InnoBit';
@endphp

<div class="bg-lime-50/45">
    @if ($article)
        <article>
            <header class="article-print-hidden border-b border-lime-200 bg-gradient-to-br from-lime-100 via-white to-lime-50 text-slate-950">
                <div class="mx-auto max-w-5xl px-4 py-10 md:py-14">
                    <a href="{{ route('home') }}" class="mb-8 inline-flex items-center gap-2 text-sm font-bold text-lime-800 underline underline-offset-4 hover:text-lime-900">
                        <span aria-hidden="true">&larr;</span>
                        <span>Kembali ke Beranda</span>
                    </a>
                    <div class="max-w-3xl">
                        <div class="mb-4 flex flex-wrap items-center gap-3">
                            <span class="rounded-full bg-lime-500 px-3 py-1 text-xs font-black uppercase tracking-wider text-white shadow-sm">{{ $article->category->name }}</span>
                            <span class="text-sm font-semibold text-slate-600">{{ $article->created_at->format('d M Y') }}</span>
                            <span class="text-sm font-semibold text-slate-600">{{ $readMinutes }} Menit</span>
                        </div>
                        <h1 class="text-4xl font-black leading-tight tracking-tight md:text-6xl">{{ $article->title }}</h1>
                        <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-700">{{ $article->summary }}</p>
                    </div>
                </div>
            </header>

            <div class="mx-auto max-w-7xl px-4 py-8 pb-12">
                @auth
                <aside class="article-print-hidden fixed right-0 top-24 z-20 hidden w-[136px] md:block">
                    <nav class="space-y-3 text-slate-900">
                        <form action="{{ route('articles.favorite.toggle', $article) }}" method="POST">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2 rounded-l-lg {{ $article->is_favorited ? 'bg-lime-200' : 'bg-lime-50' }} px-3 py-3 text-left text-sm font-medium shadow-sm ring-1 ring-lime-200 transition hover:bg-lime-100">
                                <span class="text-2xl leading-none text-lime-800">{{ $article->is_favorited ? '♥' : '♡' }}</span>
                                <span>{{ $article->is_favorited ? 'Difavoritkan' : 'Favorit' }}</span>
                            </button>
                        </form>
                        @if ($article->is_collected)
                            <form action="{{ route('articles.collection.toggle', $article) }}" method="POST">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2 rounded-l-lg bg-lime-200 px-3 py-3 text-left text-sm font-medium shadow-sm ring-1 ring-lime-200 transition hover:bg-lime-100">
                                    <span class="text-2xl leading-none text-lime-800">■</span>
                                    <span>Tersimpan</span>
                                </button>
                            </form>
                        @else
                            <button type="button" data-save-byte-open class="flex w-full items-center gap-2 rounded-l-lg bg-lime-50 px-3 py-3 text-left text-sm font-medium shadow-sm ring-1 ring-lime-200 transition hover:bg-lime-100">
                                <span class="text-2xl leading-none text-lime-800">□</span>
                                <span>Simpan</span>
                            </button>
                        @endif
                        <button type="button" data-share-open class="flex w-full items-center gap-2 rounded-l-lg bg-lime-50 px-3 py-3 text-left text-sm font-medium shadow-sm ring-1 ring-lime-200 transition hover:bg-lime-100">
                            <span class="text-2xl leading-none text-lime-800">⌯</span>
                            <span>Bagikan</span>
                        </button>
                        <button type="button" class="hidden w-full items-center gap-2 rounded-l-lg bg-lime-50 px-3 py-3 text-left text-sm font-medium shadow-sm ring-1 ring-lime-200 transition hover:bg-lime-100">
                            <span class="text-2xl leading-none text-lime-800">♡</span>
                            <span>Favorit</span>
                        </button>
                        <button type="button" class="hidden w-full items-center gap-2 rounded-l-lg bg-lime-50 px-3 py-3 text-left text-sm font-medium shadow-sm ring-1 ring-lime-200 transition hover:bg-lime-100">
                            <span class="text-2xl leading-none text-lime-800">▤</span>
                            <span>Simpan</span>
                        </button>
                        <button type="button" data-save-pdf class="flex w-full items-center gap-2 rounded-l-lg bg-lime-50 px-3 py-3 text-left text-sm font-medium shadow-sm ring-1 ring-lime-200 transition hover:bg-lime-100">
                            <span class="text-2xl leading-none text-lime-800">▧</span>
                            <span>Simpan PDF</span>
                        </button>
                    </nav>
                </aside>

                <div class="article-print-hidden mb-6 grid grid-cols-2 gap-2 text-sm font-bold md:hidden">
                    <form action="{{ route('articles.favorite.toggle', $article) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full rounded-lg {{ $article->is_favorited ? 'bg-lime-200 text-lime-900' : 'bg-lime-50 text-slate-700' }} px-3 py-3 ring-1 ring-lime-200">{{ $article->is_favorited ? 'Difavoritkan' : 'Favorit' }}</button>
                    </form>
                    @if ($article->is_collected)
                        <form action="{{ route('articles.collection.toggle', $article) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full rounded-lg bg-lime-200 px-3 py-3 text-lime-900 ring-1 ring-lime-200">Tersimpan</button>
                        </form>
                    @else
                        <button type="button" data-save-byte-open class="w-full rounded-lg bg-lime-50 px-3 py-3 text-slate-700 ring-1 ring-lime-200">Simpan</button>
                    @endif
                    <button type="button" data-share-open class="rounded-lg bg-lime-50 px-3 py-3 text-slate-700 ring-1 ring-lime-200">Bagikan</button>
                    <button type="button" data-save-pdf class="rounded-lg bg-lime-50 px-3 py-3 text-slate-700 ring-1 ring-lime-200">Simpan PDF</button>
                </div>

                <div id="share-article-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 px-4 py-6">
                    <div class="w-full max-w-md rounded-lg bg-white p-5 text-slate-900 shadow-xl">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.14em] text-lime-800">Bagikan</p>
                                <h2 class="mt-1 text-2xl font-black">Bagikan artikel</h2>
                                <p class="mt-1 text-sm leading-6 text-slate-600">Pilih kanal bagikan atau salin tautan artikel.</p>
                            </div>
                            <button type="button" data-share-close class="rounded-lg border border-lime-200 px-3 py-2 text-sm font-black text-slate-600 hover:border-lime-500">Tutup</button>
                        </div>

                        <div class="mt-5 grid gap-3">
                            <input id="share-article-url" type="text" value="{{ $articleUrl }}" readonly class="min-h-12 rounded-lg border border-lime-200 bg-lime-50/45 px-4 text-sm font-bold text-slate-900 outline-none">
                            <button type="button" data-copy-share-link class="rounded-lg bg-lime-700 px-4 py-3 text-sm font-black text-white transition hover:bg-lime-800">Salin tautan</button>
                            <p data-share-feedback class="hidden rounded-lg bg-lime-50 px-4 py-3 text-sm font-bold text-lime-800"></p>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3 text-center text-sm font-black">
                            <a href="https://wa.me/?text={{ urlencode($shareText.' '.$articleUrl) }}" target="_blank" rel="noopener" class="rounded-lg border border-lime-200 px-4 py-3 text-slate-700 transition hover:border-lime-500 hover:bg-lime-50">WhatsApp</a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($articleUrl) }}" target="_blank" rel="noopener" class="rounded-lg border border-lime-200 px-4 py-3 text-slate-700 transition hover:border-lime-500 hover:bg-lime-50">Facebook</a>
                            <a href="https://twitter.com/intent/tweet?text={{ urlencode($shareText) }}&url={{ urlencode($articleUrl) }}" target="_blank" rel="noopener" class="rounded-lg border border-lime-200 px-4 py-3 text-slate-700 transition hover:border-lime-500 hover:bg-lime-50">X</a>
                            <a href="mailto:?subject={{ rawurlencode($shareText) }}&body={{ rawurlencode($articleUrl) }}" class="rounded-lg border border-lime-200 px-4 py-3 text-slate-700 transition hover:border-lime-500 hover:bg-lime-50">Email</a>
                        </div>
                    </div>
                </div>

                @unless ($article->is_collected)
                    <div id="save-byte-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 px-4 py-6">
                        <div class="w-full max-w-md rounded-lg bg-white p-5 text-slate-900 shadow-xl">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.14em] text-lime-800">Simpan</p>
                                    <h2 class="mt-1 text-2xl font-black">Simpan ke koleksi</h2>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">Pilih album koleksi untuk artikel ini.</p>
                                </div>
                                <button type="button" data-save-byte-close class="rounded-lg border border-lime-200 px-3 py-2 text-sm font-black text-slate-600 hover:border-lime-500">Tutup</button>
                            </div>

                            <form action="{{ route('articles.collection.toggle', $article) }}" method="POST" class="mt-5 grid gap-3">
                                @csrf
                                <label class="text-sm font-black text-slate-700" for="save-byte-collection-id">Koleksi</label>
                                <select id="save-byte-collection-id" name="collection_id" class="min-h-12 rounded-lg border border-lime-200 bg-lime-50/45 px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-lime-600 focus:bg-white focus:ring-4 focus:ring-lime-100">
                                    <option value="">Koleksi Utama</option>
                                    @foreach ($learningCollections as $collection)
                                        <option value="{{ $collection->id }}">{{ $collection->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="rounded-lg bg-lime-700 px-4 py-3 text-sm font-black text-white transition hover:bg-lime-800">Simpan artikel</button>
                            </form>
                        </div>
                    </div>

                    <script>
                        document.querySelectorAll('[data-save-byte-open]').forEach((button) => {
                            button.addEventListener('click', () => {
                                const modal = document.getElementById('save-byte-modal');
                                modal.classList.remove('hidden');
                                modal.classList.add('flex');
                            });
                        });

                        document.querySelectorAll('[data-save-byte-close]').forEach((button) => {
                            button.addEventListener('click', () => {
                                const modal = document.getElementById('save-byte-modal');
                                modal.classList.add('hidden');
                                modal.classList.remove('flex');
                            });
                        });

                        document.getElementById('save-byte-modal')?.addEventListener('click', (event) => {
                            if (event.target === event.currentTarget) {
                                event.currentTarget.classList.add('hidden');
                                event.currentTarget.classList.remove('flex');
                            }
                        });
                    </script>
                @endunless

                <script>
                    (() => {
                        const shareModal = document.getElementById('share-article-modal');
                        const shareInput = document.getElementById('share-article-url');
                        const shareFeedback = document.querySelector('[data-share-feedback]');

                        const openShareModal = () => {
                            shareModal?.classList.remove('hidden');
                            shareModal?.classList.add('flex');
                            shareInput?.select();
                        };

                        const closeShareModal = () => {
                            shareModal?.classList.add('hidden');
                            shareModal?.classList.remove('flex');
                        };

                        document.querySelectorAll('[data-share-open]').forEach((button) => {
                            button.addEventListener('click', openShareModal);
                        });

                        document.querySelectorAll('[data-share-close]').forEach((button) => {
                            button.addEventListener('click', closeShareModal);
                        });

                        shareModal?.addEventListener('click', (event) => {
                            if (event.target === event.currentTarget) {
                                closeShareModal();
                            }
                        });

                        document.querySelector('[data-copy-share-link]')?.addEventListener('click', async () => {
                            try {
                                await navigator.clipboard.writeText(shareInput.value);
                            } catch (error) {
                                shareInput.select();
                                document.execCommand('copy');
                            }

                            shareFeedback.textContent = 'Tautan berhasil disalin.';
                            shareFeedback.classList.remove('hidden');
                        });

                        document.querySelectorAll('[data-save-pdf]').forEach((button) => {
                            button.addEventListener('click', () => {
                                document.body.classList.add('article-pdf-mode');
                                window.print();
                            });
                        });

                        window.addEventListener('afterprint', () => {
                            document.body.classList.remove('article-pdf-mode');
                        });
                    })();
                </script>

                @endauth

                <main class="mx-auto max-w-4xl">
                    <section id="mulai-belajar" class="article-print-area rounded-lg border border-lime-200 bg-white p-6 shadow-sm md:p-8">
                        <div class="article-print-title mb-8 hidden">
                            <p class="text-sm font-bold text-lime-800">{{ $article->category->name }} | {{ $article->created_at->format('d M Y') }} | {{ $readMinutes }} Menit</p>
                            <h1 class="mt-2 text-3xl font-black leading-tight text-slate-950">{{ $article->title }}</h1>
                            <p class="mt-3 text-base leading-7 text-slate-700">{{ $article->summary }}</p>
                        </div>

                        <div class="mb-7 flex flex-wrap items-center gap-4 border-b border-lime-100 pb-6">
                            <div>
                                <p class="text-sm font-bold text-slate-950">{{ $article->author->name }}</p>
                                <p class="text-sm text-slate-500">Mentor InnoBit</p>
                            </div>
                            <div class="h-8 w-px bg-lime-200"></div>
                            <div class="text-sm text-slate-600">
                                <p class="font-bold text-slate-950">{{ $readMinutes }} Menit</p>
                                <p>Microlearning session</p>
                            </div>
                        </div>

                        @if ($coverImage)
                            <figure class="mb-8 overflow-hidden rounded-lg border border-lime-200 bg-lime-50">
                                <img src="{{ $coverImage }}" alt="{{ $article->title }}" class="max-h-[520px] w-full object-cover">
                                <figcaption class="border-t border-lime-100 px-5 py-3 text-xs font-medium text-slate-500">Thumbnail artikel {{ $article->title }}.</figcaption>
                            </figure>
                        @endif

                        @auth
                            @if ($isRichContent)
                                <div class="article-body">
                                    {!! $article->content !!}
                                </div>
                            @else
                                <div class="space-y-6 text-[17px] leading-8 text-slate-800">
                                    @forelse ($contentBlocks as $block)
                                        @if ($block['type'] === 'image')
                                            <figure class="overflow-hidden rounded-lg border border-lime-200 bg-lime-50">
                                                <img src="{{ $block['src'] }}" alt="{{ $block['alt'] }}" class="max-h-[520px] w-full object-contain">
                                            </figure>
                                        @else
                                            <p>{{ $block['content'] }}</p>
                                        @endif
                                    @empty
                                        <p>{{ $article->summary }}</p>
                                    @endforelse
                                </div>
                            @endif
                        @else
                            <div class="rounded-lg border border-lime-200 bg-lime-50 p-5 text-slate-800 md:p-6">
                                <p class="text-sm font-black uppercase tracking-wider text-lime-800">Preview artikel</p>
                                <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">Login untuk lanjut membaca</h2>
                                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-700">{{ $article->summary }}</p>
                                <a href="{{ route('login') }}" class="mt-5 inline-flex rounded-lg bg-lime-700 px-5 py-3 text-sm font-black text-white transition hover:bg-lime-800">
                                    Login untuk lanjut membaca
                                </a>
                            </div>
                        @endauth
                    </section>

                    @auth
                    @if ($quizQuestions->isNotEmpty())
                    <section id="quiz-pemahaman" class="article-print-hidden mt-6 rounded-lg border border-lime-200 bg-white p-6 shadow-sm md:p-8">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-black uppercase tracking-wider text-lime-800">Quiz singkat</p>
                            </div>
                            <span class="rounded-full bg-lime-100 px-3 py-1 text-xs font-black text-lime-900">{{ $quizQuestions->count() }} soal</span>
                        </div>

                        <div class="mt-4 space-y-5">
                            @foreach ($quizQuestions as $quiz)
                                <form class="space-y-4 rounded-lg border border-lime-100 bg-lime-50/40 p-4" data-quiz-card data-correct-option="{{ $quiz['correct_option'] ?? 0 }}" data-attempt-url="{{ $quiz['is_normalized'] ? route('articles.quiz-attempts.store', $article) : '' }}">
                                    <fieldset>
                                        <legend class="text-lg font-black leading-7 text-slate-950">Quiz {{ $loop->iteration }}. {{ $quiz['question'] }}</legend>

                                        <div class="mt-4 grid gap-3">
                                            @foreach (collect($quiz['options']) as $option)
                                                <label class="flex cursor-pointer gap-3 rounded-lg border border-lime-200 bg-white p-4 text-sm font-semibold text-slate-700 transition hover:border-lime-500 hover:bg-lime-100">
                                                    <input type="radio" name="quiz_answer_{{ $article->id }}_{{ $loop->parent->iteration }}" value="{{ $option['id'] }}" class="mt-1 h-4 w-4 border-slate-300 text-lime-700 focus:ring-lime-600">
                                                    <span>{{ $option['text'] }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </fieldset>

                                    <div class="flex flex-wrap items-center gap-3 pt-2">
                                        <button type="submit" class="rounded-lg bg-lime-700 px-5 py-3 text-sm font-black text-white transition hover:bg-lime-800">Periksa jawaban</button>
                                        <p class="hidden rounded-lg px-4 py-3 text-sm font-bold" data-quiz-result></p>
                                    </div>
                                </form>
                            @endforeach
                        </div>
                    </section>
                    @endif

                    <script>
                        document.querySelectorAll('[data-quiz-card]').forEach((quizCard) => {
                            quizCard.addEventListener('submit', (event) => {
                                event.preventDefault();

                                const result = quizCard.querySelector('[data-quiz-result]');
                                const selected = quizCard.querySelector('input[type="radio"]:checked');

                                result.classList.remove('hidden', 'bg-red-50', 'text-red-700', 'bg-lime-50', 'text-lime-800');

                                if (!selected) {
                                    result.textContent = 'Pilih salah satu jawaban dulu.';
                                    result.classList.add('bg-red-50', 'text-red-700');
                                    return;
                                }

                                if (quizCard.dataset.attemptUrl) {
                                    fetch(quizCard.dataset.attemptUrl, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                        },
                                        body: JSON.stringify({ quiz_option_id: selected.value }),
                                    })
                                        .then((response) => response.json())
                                        .then((data) => {
                                            result.textContent = data.message;
                                            result.classList.add(data.correct ? 'bg-lime-50' : 'bg-red-50', data.correct ? 'text-lime-800' : 'text-red-700');
                                        });
                                    return;
                                }

                                if (selected.value === quizCard.dataset.correctOption) {
                                    result.textContent = 'Jawaban anda benar';
                                    result.classList.add('bg-lime-50', 'text-lime-800');
                                    return;
                                }

                                result.textContent = 'Jawaban anda salah';
                                result.classList.add('bg-red-50', 'text-red-700');
                            });
                        });
                    </script>

                    <section id="artikel-terkait" class="article-print-hidden mt-8">
                        <div class="mb-4 flex items-end justify-between gap-4">
                            <div>
                                <h2 class="text-2xl font-black tracking-tight text-slate-950">Baca juga</h2>
                                <p class="mt-1 text-sm text-slate-600">Lanjutkan ritme belajar dengan materi serupa.</p>
                            </div>
                            <a href="{{ route('home') }}" class="text-sm font-bold text-lime-800 hover:text-lime-900">Semua artikel</a>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            @forelse ($relatedArticles as $related)
                                <a href="{{ route('articles.show', $related->slug) }}" class="overflow-hidden rounded-lg border border-lime-200 bg-white/90 shadow-sm transition hover:-translate-y-0.5 hover:border-lime-400 hover:shadow-md">
                                    <img src="{{ $related->thumbnailMedia?->url ?? $fallbackImages[$loop->index % count($fallbackImages)] }}" alt="{{ $related->title }}" class="h-32 w-full object-cover">
                                    <div class="p-4">
                                        <p class="text-xs font-bold text-lime-800">{{ $related->category->name }}</p>
                                        <h3 class="mt-2 line-clamp-2 text-sm font-black leading-5 text-slate-950">{{ $related->title }}</h3>
                                    </div>
                                </a>
                            @empty
                                @foreach ($fallbackImages as $image)
                                    <a href="{{ route('home') }}" class="overflow-hidden rounded-lg border border-lime-200 bg-white/90 shadow-sm transition hover:-translate-y-0.5 hover:border-lime-400 hover:shadow-md">
                                        <img src="{{ $image }}" alt="Artikel InnoBit" class="h-32 w-full object-cover">
                                        <div class="p-4">
                                            <p class="text-xs font-bold text-lime-800">Microlearning</p>
                                            <h3 class="mt-2 line-clamp-2 text-sm font-black leading-5 text-slate-950">Temukan materi harian lainnya di InnoBit</h3>
                                        </div>
                                    </a>
                                @endforeach
                            @endforelse
                        </div>
                    </section>
                    @endauth
                </main>
            </div>
        </article>
    @else
        <div class="mx-auto max-w-4xl px-4 py-12">
            <div class="rounded-lg border border-red-200 bg-red-50 p-6 text-red-700">Artikel tidak ditemukan.</div>
        </div>
    @endif
</div>
@endsection
