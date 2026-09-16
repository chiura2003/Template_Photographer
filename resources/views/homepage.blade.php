<x-layout>
    <div class="photo-sections">
        @php
            $cellClasses = ['big', 'v1', 'v2', 'v3', 'v4', 'v5', 'v6', 'v7', 'h'];
        @endphp

        @foreach ($homepagePhotos->chunk(9)->values() as $sectionIndex => $photos)
            <div class="photo-composition {{ $sectionIndex % 2 === 1 ? 'reversed' : '' }}">
                @foreach ($photos->values() as $index => $photo)
                    <div class="cell {{ $cellClasses[$index] ?? 'v1' }}" data-reveal>
                        <img
                            class="lightbox-trigger"
                            src="{{ $photo->image_url }}"
                            alt="{{ $photo->filename }}"
                            role="button"
                            tabindex="0"
                            @if ($sectionIndex === 0 && $index === 0)
                                fetchpriority="high"
                            @else
                                loading="lazy"
                                decoding="async"
                            @endif
                        >
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
    <x-contact-icons :setting="$setting" />
    <x-lightbox />
</x-layout>
