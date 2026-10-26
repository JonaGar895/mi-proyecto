# Galería de Cuadros

Aplicación de galería de arte construida con **Laravel 12 + Inertia.js + Vue 3 + Tailwind CSS**, con base de datos **SQLite**.

Incluye usuarios, cuadros, categorías, favoritos, comentarios y exposiciones.

## Requisitos

- PHP 8.2+
- Composer
- Node.js 20+ y npm

## Instalación

```bash
composer install
cp .env.example .env        # en PowerShell: Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

## Arrancar la aplicación

Opción rápida (servidor + colas + logs + Vite a la vez):

```bash
composer dev
```

O en dos terminales:

```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

La app queda en <http://127.0.0.1:8000>.

### Usuario de prueba

```
Email:    juan@example.com
Password: 12345678
```

## Persistencia de los datos

Los datos **ya se guardan de forma permanente** en un único archivo:

```
database/database.sqlite
```

Cada cambio (usuarios, cuadros, comentarios, favoritos, exposiciones y sesiones) se escribe ahí al instante. Apagar `php artisan serve`, cerrar Vite, reiniciar el PC o volver a arrancar la app **no borra nada**: al iniciar se lee el mismo archivo.

Las sesiones también viven en la base de datos (`SESSION_DRIVER=database`), así que reiniciar el servidor no cierra la sesión; esta caduca tras `SESSION_LIFETIME` minutos de inactividad (120 por defecto).

### Qué sí borra los datos

| Comando | Efecto |
|---|---|
| `php artisan migrate` | Seguro: solo aplica migraciones nuevas |
| `php artisan test` | Seguro: usa una BD SQLite en memoria (`:memory:`) |
| `composer setup` | Seguro: usa `migrate --force`, no `fresh` |
| `php artisan migrate:fresh` | **Borra todas las tablas y datos** |
| `php artisan migrate:refresh` | **Borra todas las tablas y datos** |
| `php artisan db:wipe` | **Borra todas las tablas** |
| Borrar `database/database.sqlite` | **Borra la base de datos** |

Regla: para conservar los datos usa siempre `php artisan migrate`, nunca `fresh`, `refresh` ni `db:wipe`.

## Copias de seguridad

Crear una copia (se guarda en `database/backups/` con fecha y hora):

```bash
composer db:backup
# o directamente:
php artisan db:backup
```

Por defecto conserva las **10 copias más recientes** y elimina las antiguas. Para cambiar cuántas se guardan:

```bash
php artisan db:backup --keep=30   # conservar 30
php artisan db:backup --keep=0    # no borrar nunca ninguna
```

La carpeta `database/backups/` está en `.gitignore`, así que las copias no se suben al repositorio.

### Restaurar una copia

1. Para el servidor (`Ctrl+C` en `php artisan serve`).
2. Copia el backup elegido sobre la base actual:

```powershell
Copy-Item database\backups\database-2026-10-26_11-06-45.sqlite database\database.sqlite -Force
```

3. Vuelve a arrancar la app.

## Tests

```bash
composer test
# o
php artisan test
```

## Estructura de datos

```
users
 ├──< paintings          (user_id = artista/publicador)
 ├──< comments           (user_id + painting_id)
 ├──< favorites          (user_id + painting_id, N:M)
 └──< paintings >──< exhibitions   (N:M)

paintings
 ├── user_id     → users
 ├── category_id → categories
 └── title, description, year, technique, emoji, image_url, price, is_public
```

## Rutas principales

| Ruta | Descripción |
|---|---|
| `/` | Inicio con las últimas obras |
| `/cuadros` | Galería con búsqueda, filtro y favoritos |
| `/cuadros/create` | Publicar una obra |
| `/cuadros/{id}` | Detalle, exposiciones y comentarios |
| `/users` | Listado de usuarios |
| `/users/create` | Crear usuario |
| `/users/{id}/edit` | Editar usuario |
| `/settings` | Ajustes y estadísticas del usuario |

## Nota sobre el repositorio

`database/database.sqlite` y `database/backups/` están ignorados por Git: la base de datos no se versiona. Si necesitas mover los datos a otro equipo, usa una copia de `database/database.sqlite` o un backup de `database/backups/`.
