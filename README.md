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

Para cargar datos demo en **todas las entidades** puedes ejecutar:

```bash
php artisan db:seed
```

Los seeders son **idempotentes**: puedes ejecutarlos varias veces sin duplicar los registros principales. Esto genera:

1. Usuarios demo con roles, saldo y credenciales conocidas (`UserSeeder`).
2. La cuenta separada del super administrador (`AccountSeeder`).
3. Categorías artísticas (`CategorySeeder`).
4. Cuadros con precios, categorías y autores (`PaintingSeeder`).
5. Exposiciones y sus obras (`ExhibitionSeeder`).
6. Publicaciones y comentarios de publicaciones (`PublicationSeeder`).
7. Favoritos de los usuarios demo (`FavoriteSeeder`).
8. Comentarios de cuadros (`CommentSeeder`).
9. Recargas iniciales y cargos de compras en el libro de movimientos de saldo (`BalanceTransactionSeeder`).
10. Ventas demo reales con descuento al comprador y movimiento en la cuenta del super administrador (`SaleSeeder`).
11. Solicitudes de compra en estados `pending`, `contacted`, `rejected` y `closed` (`InquirySeeder`).

### Usuarios de prueba

| Email | Contraseña | Rol |
|---|---|---|
| `broderick14@example.org` | `password` | Super administrador (ID 1) |
| `ana.admin@galeria.test` | `password` | Administradora |
| `javier.vendedor@galeria.test` | `password` | Vendedor |
| `lucia.vendedora@galeria.test` | `password` | Vendedora |
| `juan@example.com` | `12345678` | Usuario / comprador |
| `maria.compradora@galeria.test` | `password` | Usuario / compradora |
| `carlos.coleccionista@galeria.test` | `password` | Usuario / comprador |
| `sofia.visitante@galeria.test` | `password` | Usuario / compradora |

> Los usuarios demo reciben un saldo inicial de `100000.00` al crearse. Si necesitas una base totalmente limpia para una demostración, puedes usar `php artisan migrate:fresh --seed` (**esto borra todos los datos actuales**).

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

accounts
 ├── user_id → users (titular: super administrador, nullable)
 └── name, type (super_admin), balance

account_transactions
 ├── account_id → accounts
 ├── inquiry_id → inquiries (opcional)
 ├── painting_id → paintings (opcional)
 ├── buyer_id / seller_id → users (opcional)
 └── type (sale_income), amount, description

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
| **Super administrador** | `users.id = 1` y `users.is_admin = true` | Todo lo de administrador y, además, es el titular de la **cuenta de ventas** donde se ingresan los importes de las obras vendidas. |
| **Administrador** | `users.is_admin = true` | Puede crear/editar/eliminar cualquier usuario (excepto a sí mismo al eliminar), eliminar cualquier obra, ver cualquier perfil de usuario y aprobar/rechazar solicitudes de venta. |
| **Usuario** | `users.is_admin = false` | Puede gestionar su propio perfil, publicar obras, añadir favoritos/comentarios, crear/editar/eliminar sus propias publicaciones. Solo puede ver su propio perfil a menos que sea administrador. |

La autorización se gestiona mediante **Policies** (`app/Policies/UserPolicy.php`). Las rutas de usuarios están protegidas con `can()` en las rutas web. Los administradores también pueden eliminar cualquier pintura desde el detalle de la obra.

## Añadir saldo (recarga)

Cada usuario puede añadir saldo desde **`/mi-cuenta`**:

1. Se introduce un importe entre `1` y `100000` €.
2. El importe se suma a `users.balance` dentro de una transacción.
3. Se guarda un movimiento de tipo `deposit` en `balance_transactions` con el saldo resultante.
4. La página muestra los últimos movimientos (recargas y compras) con su importe y saldo posterior.

La recarga es **simulada**, pensada para la demo: no interviene ninguna pasarela de pago real.

Además, cada compra completada registra un movimiento de tipo `purchase` en `balance_transactions`, de modo que el usuario puede consultar en qué se ha gastado el saldo.

## Ventas y cuenta del super administrador

Cuando un administrador **aprueba una solicitud de compra** (o marca la venta como realizada):

1. Se comprueba que el comprador tenga saldo suficiente.
2. Se **descuenta el precio de la obra** del saldo personal del comprador (`users.balance`).
3. El mismo importe se **ingresa en una cuenta separada del super administrador** (`accounts`, tipo `super_admin`), que es independiente de su saldo personal.
4. Se guarda un movimiento en `account_transactions` para dejar constancia de la venta.
5. La obra queda marcada como vendida y las demás solicitudes se cierran.

Todo el proceso se ejecuta dentro de una transacción de base de datos (`app/Services/SaleService.php`), por lo que el descuento y el ingreso se realizan juntos o no se realizan.

El super administrador puede consultar esa cuenta en **`/cuenta-super-admin`**, donde ve el saldo acumulado, los ingresos totales y el historial de movimientos.

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
| `/mi-cuenta` | Saldo, formulario de recarga, movimientos, compras y solicitudes del usuario |
| `/mi-cuenta/saldo` (POST) | Añadir saldo a la cuenta del usuario autenticado |
| `/cuenta-super-admin` | Saldo e histórico de la cuenta de ventas (solo super administrador, ID 1) |
| `/inquiries` | Solicitudes de compra recibidas/enviadas; panel de aprobación para administradores |
| `/inquiries/history` | Histórico de compras y ventas (solo administradores) |

## Nota sobre el repositorio

`database/database.sqlite` está ignorado por Git, pero las copias de `database/backups/*.sqlite` sí se versionan. Si clonas el proyecto en otro equipo, puedes restaurar la copia más reciente con:

```powershell
Copy-Item database\backups\database-AAA-MM-DD_HH-mm-ss.sqlite database\database.sqlite -Force
```
