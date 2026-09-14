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
                        class="work-album-card"
                        aria-label="Apri l'album {{ $album->title }}"
                    >
                        @if ($album->coverPhoto?->image_url)
                            <div class="work-album-image">
                                <img
                                    src="{{ $album->coverPhoto->image_url }}"
                                    alt="{{ $album->coverPhoto->alt_text ?: $album->title . ' - work photography project' }}"
                                >
                            </div>
                        @else
                            <div class="work-album-image work-album-image--empty" aria-hidden="true">
                                <span>Cover image coming soon</span>
                            </div>
                        @endif

                        <div class="work-album-info">
                            <h3>{{ $album->title }}</h3>
                            <span>{{ $album->year }}</span>
                        </div>
                    </a>
                @endforeach
            </x-year-gallery>
        @empty
            <p class="work-empty">No work projects are currently available.</p>
        @endforelse
    </div>
</x-layout>
