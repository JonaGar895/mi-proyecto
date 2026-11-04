<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <script>
        (() => {
            try {
                const userId = @json(auth()->id());
                const key = userId ? `theme:user:${userId}` : 'theme:guest';

                let theme = localStorage.getItem(key);

                // Si venimos de la versión anterior (clave global "theme"),
                // la preferencia existente se asigna al usuario actual una sola vez.
                if (!theme && localStorage.getItem('theme')) {
                    theme = localStorage.getItem('theme');
                    localStorage.setItem(key, theme);
                    localStorage.removeItem('theme');
                }

                document.documentElement.classList.toggle('dark', theme === 'dark');
            } catch (error) {
                // Si el navegador bloquea localStorage, se mantiene el tema claro.
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @inertiaHead
</head>

<body class="bg-gray-50 text-gray-800 dark:bg-gray-900 dark:text-gray-100">
    <section>
        @inertia
    </section>
</body>
</html>