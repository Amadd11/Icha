<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Meta / SEO Defaults -->
        <meta name="theme-color" content="#52026d">
        <meta name="author" content="PIP MARSI & UMSURA">
        <meta name="robots" content="index, follow">

        <!-- Favicons & Icons -->
        <link rel="icon" type="image/png" href="/assets/logo/logo-umsura.png">
        <link rel="apple-touch-icon" href="/assets/logo/logo-umsura.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        <title inertia>ICHA - International Conference on Healthcare Administration</title>
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
