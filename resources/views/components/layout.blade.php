<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @props([
        'title' => config('app.name', 'Laravel')
    ])

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="15">

    <title>{{ $title }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet"/>

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css"/>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5/themes.css" rel="stylesheet" type="text/css"/>

    <style>
        body {
            background-image: url("/images/background.jpg") !important;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            margin: 0;
            min-height: 100vh;
        }
    </style>
</head>
<body class="bg-base-100 text-base-content">

@auth
    <!-- DaisyUI Drawer Container -->
    <div class="drawer lg:drawer-open">
        <input id="my-drawer-2" type="checkbox" class="drawer-toggle" />

        <!-- RECHTE SEITE: Navbar und Content -->
        <div class="drawer-content flex flex-col">
            <header>
                <x-nav />
            </header>
            <main class="p-6">
                {{ $slot }}
            </main>
        </div>

        <!-- LINKE SEITE: Sidebar -->
        <x-sidebar />
    </div>
@else
    <!-- Fallback, wenn der User NICHT eingeloggt ist (z.B. Login-Screen) -->
    <main>
        {{ $slot }}
    </main>
@endauth

</body>
</html>
