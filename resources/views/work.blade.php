<x-layout title="Work">
    @php
        $albumsByYear = \Illuminate\Support\Facades\Schema::hasTable('albums')
            ? \App\Models\Album::query()
                ->with([
                    'coverPhoto' => fn ($query) => $query->where('is_published', true),
                ])
                ->where('type', \App\Enums\AlbumType::Work->value)
                ->where('is_published', true)
                ->orderByDesc('year')
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get()
                ->groupBy('year')
            : collect();

        $placeholderCover = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 2 3'%3E%3Crect width='2' height='3' fill='%23e7e1d8'/%3E%3C/svg%3E";
    @endphp

    <div class="content-wrapper">
        <div class="work-header">
            <h1>WORK</h1>
        </div>

        @forelse ($albumsByYear as $year => $albums)
            <x-year-gallery :year="$year">
                @foreach ($albums as $album)
                    <a
                        href="{{ route('work.album', $album) }}"
                        class="work-album-link"
                        aria-label="Apri l'album {{ $album->title }}"
                    >
                        <img
                            src="{{ $album->coverPhoto?->image_url ?: $placeholderCover }}"
                            alt="{{ $album->coverPhoto?->alt_text ?: $album->title . ' - work photography project' }}"
                        >
                    </a>
                @endforeach
            </x-year-gallery>
        @empty
            <p class="work-empty">No work projects are currently available.</p>
        @endforelse
    </div>
</x-layout>
