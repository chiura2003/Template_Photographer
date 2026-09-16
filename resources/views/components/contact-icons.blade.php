@if (filled($setting?->email) || filled($setting?->phone) || filled($setting?->instagram_url) || filled($setting?->vimeo))
    <nav class="contact-icons" aria-label="Contatti">

        @if (filled($setting?->email))
            <a class="contact-icons-link" href="{{ route('contact') }}" aria-label="Email">
                <i class="fa-solid fa-envelope" aria-hidden="true"></i>
            </a>
        @endif

        @if (filled($setting?->phone))
            <a class="contact-icons-link" href="tel:{{ str_replace(' ', '', $setting->phone) }}" aria-label="Telefono">
                <i class="fa-solid fa-phone" aria-hidden="true"></i>
            </a>
        @endif

        @if (filled($setting?->instagram_url))
            <a class="contact-icons-link" href="{{ $setting->instagram_url }}" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                <i class="fa-brands fa-instagram" aria-hidden="true"></i>
            </a>
        @endif

        @if (filled($setting?->vimeo))
            <a class="contact-icons-link" href="{{ $setting->vimeo }}" target="_blank" rel="noopener noreferrer" aria-label="Vimeo">
                <i class="fa-brands fa-vimeo-v" aria-hidden="true"></i>
            </a>
        @endif

    </nav>
@endif