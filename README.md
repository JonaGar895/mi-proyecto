# Galería de Cuadros

Aplicación de galería de arte construida con **Laravel 12 + Inertia.js + Vue 3 + Tailwind CSS**, con base de datos **SQLite**.

Incluye usuarios, cuadros, categorías, favoritos, comentarios, publicaciones, exposiciones y un sistema de roles (administrador/usuario).

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

## Poblar la base de datos con datos de prueba

Si quieres cargar datos iniciales (categorías, usuarios, cuadros, exposiciones y publicaciones), puedes ejecutar los seeders:

```bash
php artisan db:seed
```

Esto ejecutará `DatabaseSeeder` que:

1. Crea 100 usuarios con `UserFactory`
2. Ejecuta `UserSeeder`, `CategorySeeder`, `PaintingSeeder`, `ExhibitionSeeder` y `PublicationSeeder`

### Usuarios de prueba

Tras ejecutar los seeders, tendrás estos usuarios disponibles:

| Email | Contraseña | Rol |
|---|---|---|
| `broderick14@example.org` | `password` | Administrador (`is_admin = true`) |
| `juan@example.com` | `12345678` | Usuario (`is_admin = false`) |

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

## Autenticación

- Solo existe **inicio de sesión** (`/login`). No hay registro público.
- Las credenciales se validan contra la base de datos.
- Las sesiones se guardan en la base de datos (`SESSION_DRIVER=database`), por lo que no se cierran al reiniciar el servidor.
- La sesión caduca tras `SESSION_LIFETIME` minutos de inactividad (120 por defecto).
- Cierre de sesión mediante `POST /logout`.

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

La carpeta `database/backups/` sí se versiona en Git, así que puedes recuperar los datos desde el repositorio.

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
 ├──< publications       (user_id = autor)
 ├──< publication_comments (user_id + publication_id)
 └──< paintings >──< exhibitions   (N:M)

users
 └── name, email, password, stripe_token (nullable), phone (nullable), is_admin (bool), timestamps

paintings
 ├── user_id     → users
 ├── category_id → categories (nullable)
 └── title, description, year, technique, emoji (default 🖼️), image_url, price, is_public (default true)

publications
 ├── user_id     → users        (autor)
 ├── category_id → categories   (opcional)
 ├── painting_id → paintings    (obra relacionada, opcional)
 └── title, slug, summary, content, is_published, published_at

publication_comments
 ├── user_id        → users
 └── publication_id → publications
```

## Roles y permisos

La aplicación cuenta con dos roles:

| Rol | Cómo se identifica | Privilegios |
|---|---|---|
| **Administrador** | `users.is_admin = true` | Puede crear/editar/eliminar cualquier usuario (excepto a sí mismo al eliminar), eliminar cualquier obra, ver cualquier perfil de usuario. |
| **Usuario** | `users.is_admin = false` | Puede gestionar su propio perfil, publicar obras, añadir favoritos/comentarios, crear/editar/eliminar sus propias publicaciones. Solo puede ver su propio perfil a menos que sea administrador. |

La autorización se gestiona mediante **Policies** (`app/Policies/UserPolicy.php`). Las rutas de usuarios están protegidas con `can()` en las rutas web. Los administradores también pueden eliminar cualquier pintura desde el detalle de la obra.

## Exposiciones

- Las **exposiciones** (`exhibitions`) agrupan obras de arte mediante una tabla pivot `exhibition_painting` (N:M).
- En el detalle de una pintura (`/cuadros/{painting}`) se muestran las exposiciones en las que participa la obra, con sus fechas de inicio y fin.
- Las exposiciones se cargan desde los seeders (`ExhibitionSeeder`) para datos de prueba.

## Rutas principales

| Ruta | Descripción |
|---|---|
| `/` | Inicio con las últimas obras públicas y últimas publicaciones publicadas |
| `/cuadros` | Galería con búsqueda, filtro por categoría y favoritos. Paginado (9 por página). |
| `/cuadros/create` | Publicar una obra (autenticado) |
| `/cuadros/{painting}` | Detalle de la obra, exposiciones, comentarios, opción de favorito |
| `/cuadros/{painting}` (DELETE) | Eliminar obra (solo dueño o administrador) |
| `/publicaciones` | Blog con búsqueda y filtro por categoría (solo publicaciones publicadas) |
| `/publicaciones/create` | Nueva publicación (autenticado) |
| `/publicaciones/{publication}` | Detalle y comentarios de la publicación |
| `/publicaciones/{publication}/edit` | Editar publicación |
| `/publicaciones/{publication}` (PUT/POST) | Actualizar publicación |
| `/publicaciones/{publication}` (DELETE) | Eliminar publicación |
| `/users/list` | Listado de usuarios (protegido por política) |
| `/users/view/{user}` | Detalle de un usuario (propio o administrador) |
| `/users/create` | Crear usuario (solo administrador) |
| `/users/edit/{user}` | Editar usuario (propio o administrador) |
| `/users/delete/{user}` | Eliminar usuario (solo administrador, no puede eliminarse a sí mismo) |
| `/settings` | Ajustes y estadísticas del usuario (obras, favoritos, comentarios, publicaciones) |

## Nota sobre el repositorio

`database/database.sqlite` está ignorado por Git, pero las copias de `database/backups/*.sqlite` sí se versionan. Si clonas el proyecto en otro equipo, puedes restaurar la copia más reciente con:

```powershell
Copy-Item database\backups\database-AAA-MM-DD_HH-mm-ss.sqlite database\database.sqlite -Force
```
