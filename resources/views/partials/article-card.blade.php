@php
    $articleImage = $article->thumbnailMedia?->url
        ?? $fallbackImages[($cardIndex - 1) % count($fallbackImages)];
    $readMinutes = $article->reading_minutes;
@endphp

<article class="group">
    <div class="relative">
        <a href="{{ route('articles.show', $article->slug) }}" class="block">
            <div class="aspect-[4/3] overflow-hidden rounded-lg bg-lime-100 shadow-sm">
                <img src="{{ $articleImage }}" alt="{{ $article->title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                <div class="absolute bottom-2 left-2 rounded-full bg-lime-100 px-2.5 py-1 text-[11px] font-bold text-lime-900 shadow-sm">{{ $readMinutes }} Menit</div>
            </div>
        </a>

        @auth
            <form action="{{ route('articles.favorite.toggle', $article) }}" method="POST" class="absolute bottom-2 right-2 z-10">
                @csrf
                <button
                    type="submit"
                    class="flex h-7 w-7 items-center justify-center rounded-full {{ $article->is_favorited ? 'bg-lime-700 text-white' : 'bg-white/80 text-lime-800' }} text-lg leading-none shadow-sm backdrop-blur-sm transition hover:bg-lime-100 hover:text-lime-900 focus:outline-none focus:ring-4 focus:ring-lime-200"
                    aria-label="{{ $article->is_favorited ? 'Hapus dari favorit' : 'Tambah ke favorit' }}"
                    title="{{ $article->is_favorited ? 'Hapus dari favorit' : 'Tambah ke favorit' }}"
                >
                    <span aria-hidden="true">{!! $article->is_favorited ? '&hearts;' : '&#9825;' !!}</span>
                </button>
            </form>
        @else
            <a
                href="{{ route('login') }}"
                class="absolute bottom-2 right-2 z-10 flex h-7 w-7 items-center justify-center rounded-full bg-white/80 text-lg leading-none text-lime-800 shadow-sm backdrop-blur-sm transition hover:bg-lime-100 hover:text-lime-900 focus:outline-none focus:ring-4 focus:ring-lime-200"
                aria-label="Login untuk menambahkan favorit"
                title="Login untuk menambahkan favorit"
            >
                <span aria-hidden="true">&#9825;</span>
            </a>
        @endauth
    </div>

    <a href="{{ route('articles.show', $article->slug) }}" class="block">
        <h3 class="mt-2.5 text-base font-black leading-snug tracking-tight group-hover:text-lime-800">{{ $article->title }}</h3>
        <p class="mt-1 text-sm font-medium text-slate-600">{{ $article->category->name }}</p>
    </a>
</article>
