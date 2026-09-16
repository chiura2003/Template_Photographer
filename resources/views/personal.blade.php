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
                <x-carousel id="personal">
                    @foreach ($albums as $album)
                        <article id="{{ $album->slug }}" class="personal-album">
                            <a href="{{ route('personal.album', $album) }}" class="personal-album-link" aria-label="Apri l'album {{ $album->title }}">
                                @if ($album->coverPhoto?->image_url)
                                    <div class="personal-album-image" data-reveal>
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
                </x-carousel>
            @else
                <p class="personal-empty">No personal projects are currently available.</p>
            @endif

            <x-contact-icons :setting="$setting" />
        </div>
    </section>
</x-layout>
