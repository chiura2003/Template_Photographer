<nav class="navbar navbar-expand-lg site-navbar">
  <div class="container">
    <a class="navbar-brand" href="{{ route('homepage') }}" aria-label="Homepage">
      <img class="navbar-brand-logo" src="{{ asset('images/logo-transparent.png') }}" alt="" aria-hidden="true">
      <span>Nome Fotografo</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
      <ul class="navbar-nav mb-2 mb-lg-0">

        <a class="nav-link {{ request()->routeIs('homepage') ? 'active' : '' }}" href="{{ route('homepage') }}">
          <span>Homepage</span>
        </a>

        <a class="nav-link {{ request()->routeIs('work') || request()->routeIs('work.album') ? 'active' : '' }}" href="{{ route('work') }}">
          <span>Work</span>
        </a>

        <a class="nav-link {{ request()->routeIs('personal') || request()->routeIs('personal.album') ? 'active' : '' }}" href="{{ route('personal') }}">
          <span>Personal</span>
        </a>

        <a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">
          <span>About</span>
        </a>

        <a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">
          <span>Contact</span>
        </a>

      </ul>
    </div>
  </div>
</nav>
