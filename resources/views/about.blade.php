<x-layout title="About">
    <section class="about-page">
        <div class="content-wrapper">
            <header class="about-header">
                <h1>{{ $setting?->photographer_name ?: 'About' }}</h1>
            </header>

            <div class="about-grid {{ $hasProfileImage ? '' : 'about-grid--without-image' }}">
                @if ($hasProfileImage)
                    <aside class="about-image">
                        <img
                            src="{{ route('about.profile-image') }}"
                            alt="{{ $setting->photographer_name ?: 'Fotografo' }}"
                            data-reveal
                        >

                        <x-contact-icons :setting="$setting" />
                    </aside>
                @endif

                <div class="about-content">
                    @if (filled($setting?->bio))
                        <div class="about-section">
                            <p>{!! nl2br(e($setting->bio)) !!}</p>
                        </div>
                    @else
                        <p class="about-empty">La biografia sarà disponibile a breve.</p>
                    @endif

                    @if (!$hasProfileImage)
                        <x-contact-icons :setting="$setting" />
                    @endif
                </div>
            </div>
        </div>
    </section>
</x-layout>