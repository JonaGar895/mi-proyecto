<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <script>
        (() => {
            try {
                const userId = @json(auth()->id());
                const userTheme = @json(auth()->user()?->theme);
                const key = userId ? `theme:user:${userId}` : 'theme:guest';
                const legacyTheme = localStorage.getItem('theme');

                // La base de datos es la fuente principal. La caché local solo
                // se usa cuando la cuenta todavía no tiene un tema guardado.
                const theme = userTheme || localStorage.getItem(key) || legacyTheme || 'light';

                if (userTheme) {
                    localStorage.setItem(key, userTheme);
                } else if (!localStorage.getItem(key) && legacyTheme) {
                    localStorage.setItem(key, legacyTheme);
                }

                if (legacyTheme) {
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