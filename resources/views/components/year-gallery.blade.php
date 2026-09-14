<section class="year-section">

    <h2 class="year-title">{{ $year }}</h2>

    <div class="gallery-container">

        <button class="gallery-arrow" data-prev aria-label="Foto precedenti">
            <i class="fa-solid fa-chevron-left"></i>
        </button>

        <div class="gallery-viewport">
            <div class="gallery-track" id="gallery-{{ $year }}" data-slider>
                {{ $slot }}
            </div>
        </div>

        <button class="gallery-arrow" data-next aria-label="Foto successive">
            <i class="fa-solid fa-chevron-right"></i>
        </button>

    </div>

    <div class="gallery-dots" data-dots></div>

</section>