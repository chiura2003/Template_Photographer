<x-layout title="Contact">

<section class="contact-page">

    <div class="content-wrapper">

        <div class="contact-header">

            <h1>CONTACT</h1>

        </div>

        <div class="contact-card">

            <div class="contact-item">

                <span class="contact-label">EMAIL</span>

                <a href="mailto:fiogallery@gmail.com">
                    fiogallery@gmail.com
                </a>

            </div>

            @if (filled($setting?->instagram_url))
                <div class="contact-item">

                    <span class="contact-label">INSTAGRAM</span>

                    <a href="{{ $setting->instagram_url }}" target="_blank" rel="noopener noreferrer">
                        {{ str_replace(['https://', 'http://', '@', 'www.instagram.com/'], ['', '', '', ''], $setting->instagram_url) }}
                    </a>

                </div>
            @endif

            @if (filled($setting?->vimeo))
                <div class="contact-item">

                    <span class="contact-label">VIMEO</span>

                    <a href="{{ $setting->vimeo }}" target="_blank" rel="noopener noreferrer">
                        {{ str_replace(['https://', 'http://', 'www.vimeo.com/', 'vimeo.com/'], ['', '', '', ''], $setting->vimeo) }}
                    </a>

                </div>
            @endif

            @if (filled($setting?->phone))
                <div class="contact-item">

                    <span class="contact-label">PHONE</span>

                    <a href="tel:{{ str_replace(' ', '', $setting->phone) }}">
                        {{ $setting->phone }}
                    </a>

                </div>
            @endif

            <div class="contact-item">

                <span class="contact-label">LOCATION</span>

                <span>Modena, Italy</span>

            </div>

        </div>

        <div class="contact-form">

            <h2>Send a message</h2>

            @if (session('status') === 'success-message')
                <div class="contact-form-status" role="status" data-success>
                    Grazie per il tuo messaggio. Ti risponderò al più presto.
                </div>
            @endif

            @if ($errors->any())
                <div class="contact-form-status contact-form-status--error" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('contact.submit') }}">

                @csrf

                <input
                    type="text"
                    name="name"
                    placeholder="Your name"
                    value="{{ old('name') }}"
                    required
                >
                @error('name')
                    <span class="contact-form-field-error">{{ $message }}</span>
                @enderror

                <input
                    type="email"
                    name="email"
                    placeholder="Email address"
                    value="{{ old('email') }}"
                    required
                >
                @error('email')
                    <span class="contact-form-field-error">{{ $message }}</span>
                @enderror

                <textarea
                    name="message"
                    rows="7"
                    placeholder="Tell me about your project..."
                    required
                >{{ old('message') }}</textarea>
                @error('message')
                    <span class="contact-form-field-error">{{ $message }}</span>
                @enderror

                <input
                    type="text"
                    name="website"
                    class="contact-form-honeypot"
                    tabindex="-1"
                    autocomplete="off"
                    aria-hidden="true"
                >

                <button type="submit">

                    SEND MESSAGE

                </button>

            </form>

        </div>

    </div>

</section>

</x-layout>
