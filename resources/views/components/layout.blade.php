<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portfolio fotografico di Tommaso Fiorini">
    <title>{{ $title ?? 'Nome Fotografo' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/tommaso-fiorini-favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/tommaso-fiorini-favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="d-flex flex-column min-vh-100">
    <x-navbar />

    <main class="flex-grow-1">
        {{ $slot }}
    </main>
  
    <x-footer />
</body>
</html>
