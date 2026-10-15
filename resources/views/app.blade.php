<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximun-scale=1.0"/>
    <Link href="http://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet" />
    <script src="{{ mix('/js/app-js') }}" defer></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @inertiaHead
</head>

<body>
    <section class="px-8">
        @inertia
    </section>
</body>
</html>