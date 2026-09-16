<x-layout :title="$album->title . ' | Personal'">
    <section class="album-page">
        <div class="content-wrapper">
            <header class="album-page-header">
                <a class="album-back-link" href="{{ route('personal') }}">&larr; Personal</a>
                <h1 class="album-page-title">{{ $album->title }}</h1>
                <span class="album-page-year">{{ $album->year }}</span>

                @if ($album->description)
                    <p class="album-page-description">{{ $album->description }}</p>
                @endif
            </header>

            @forelse ($album->photos as $photo)
                @if ($loop->first)
                    <div class="album-photo-grid">
                @endif

                <figure data-reveal>
                    <img
                        class="lightbox-trigger"
                        src="{{ $photo->image_url }}"
                        alt="{{ $photo->alt_text ?: $photo->title ?: $album->title }}"
                        role="button"
                        tabindex="0"
                    >
                </figure>

                @if ($loop->last)
                    </div>
                @endif
            @empty
                <p class="album-empty">Nessuna foto disponibile per questo album.</p>
            @endforelse
        </div>
    </section>
    <x-lightbox />
</x-layout>
