<section class="year-section">

    <h2 class="year-title">{{ $year }}</h2>

    <x-carousel :id="$year">
        {{ $slot }}
    </x-carousel>

</section>