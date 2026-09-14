<x-layout title="About">
    <section class="about-page">
        <div class="content-wrapper">
            <div class="about-grid {{ $hasProfileImage ? '' : 'about-grid--without-image' }}">
                @if ($hasProfileImage)
                    <aside class="about-image">
                        <img src="{{ route('about.profile-image') }}" alt="{{ $setting->photographer_name ?: 'Fotografo' }}">
                    </aside>
                @endif

                <div class="about-content">
                    <h1>{{ $setting?->photographer_name ?: 'About' }}</h1>

                    @if (filled($setting?->bio))
                        <div class="about-section">
                            <p>{!! nl2br(e($setting->bio)) !!}</p>
                        </div>
                    @else
                        <p class="about-empty">La biografia sarà disponibile a breve.</p>
                    @endif

                    @if (filled($setting?->phone) || filled($setting?->instagram_url))
                        <div class="about-section about-contact">
                            @if (filled($setting?->phone))
                                <div class="about-contact-item">
                                    <i class="fa-solid fa-phone" aria-hidden="true"></i>
                                    <span class="about-contact-label">TELEFONO</span>
                                    <a href="tel:{{ str_replace(' ', '', $setting->phone) }}">{{ $setting->phone }}</a>
                                </div>
                            @endif

                            @if (filled($setting?->instagram_url))
                                <div class="about-contact-item">
                                    <i class="fa-brands fa-instagram" aria-hidden="true"></i>
                                    <span class="about-contact-label">INSTAGRAM</span>
                                    <a href="{{ $setting->instagram_url }}" target="_blank" rel="noopener noreferrer">
                                        {{ str_replace(['https://', 'http://', '@', 'www.instagram.com/'], ['', '', '', ''], $setting->instagram_url) }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</x-layout>
