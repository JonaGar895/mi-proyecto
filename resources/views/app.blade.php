<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <script>
        (() => {
            try {
                if (localStorage.getItem('theme') === 'dark') {
                    document.documentElement.classList.add('dark');
                }
            } catch (error) {
                // Si el navegador bloquea localStorage, se mantiene el tema claro.
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @inertiaHead
</head>

<body class="bg-gray-50 text-gray-800 dark:bg-gray-900 dark:text-gray-100">
    <section class="px-8">
        @inertia
    </section>
</body>
</html>