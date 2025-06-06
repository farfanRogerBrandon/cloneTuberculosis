<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mi Aplicación')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/nuevo-pacientes.css') }}">
</head>
<body>
    @include('partials.header') <!-- Incluye el header -->

    <main>
        @yield('content') <!-- Aquí va el contenido de cada página -->
    </main>

    @include('partials.footer') <!-- Incluye el footer -->
</body>
</html>
