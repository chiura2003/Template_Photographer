<x-layout title="Personal">
    @php
        $albums = \Illuminate\Support\Facades\Schema::hasTable('albums')
            ? \App\Models\Album::query()
                ->with([
                    'coverPhoto' => fn ($query) => $query->where('is_published', true),
                ])
                ->where('type', \App\Enums\AlbumType::Personal->value)
                ->where('is_published', true)
                ->orderBy('sort_order')
                ->orderByDesc('year')
                ->orderBy('title')
                ->get()
            : collect();
    @endphp

    <section class="personal-page">
        <div class="content-wrapper">
            <header class="personal-header">
                <h1>PERSONAL</h1>
            </header>

            @if ($albums->isNotEmpty())
                <div id="personal-projects" class="personal-grid">
                    @foreach ($albums as $album)
                        <article id="{{ $album->slug }}" class="personal-album">
                            <a href="{{ route('personal.album', $album) }}" class="personal-album-link">
                                @if ($album->coverPhoto?->image_url)
                                    <div class="personal-album-image">
                                        <img
                                            src="{{ $album->coverPhoto->image_url }}"
                                            alt="{{ $album->coverPhoto->alt_text ?: $album->title . ' - personal photography project' }}"
                                        >
                                    </div>
                                @else
                                    <div class="personal-album-image personal-album-image--empty" aria-hidden="true">
                                        <span>Cover image coming soon</span>
                                    </div>
                                @endif

                                <div class="personal-album-info">
                                    <h2>{{ $album->title }}</h2>
                                    <span>{{ $album->year }}</span>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="personal-empty">No personal projects are currently available.</p>
            @endif
        </div>
    </section>
</x-layout>
