
# 🎨 Galería de Cuadros

Aplicación web de gestión y exposición de obras de arte desarrollada con **Laravel 12, Inertia.js, Vue 3 y Tailwind CSS**, utilizando SQLite como sistema de base de datos.

La plataforma permite publicar obras, gestionar usuarios, organizar exposiciones, interactuar mediante comentarios y favoritos, y administrar solicitudes de compra y venta.

## 📋 Índice

- [🎨 Galería de Cuadros](#-galería-de-cuadros)
  - [📋 Índice](#-índice)
  - [Descripción](#descripción)
    - [Características principales](#características-principales)
  - [Tecnologías](#tecnologías)
  - [Requisitos](#requisitos)
  - [Instalación](#instalación)
    - [1. Clonar el repositorio](#1-clonar-el-repositorio)
    - [2. Instalar dependencias](#2-instalar-dependencias)
    - [3. Configurar el entorno](#3-configurar-el-entorno)
    - [4. Generar la clave](#4-generar-la-clave)
    - [5. Configurar SQLite](#5-configurar-sqlite)
    - [6. Ejecutar migraciones](#6-ejecutar-migraciones)
    - [7. Instalar dependencias frontend](#7-instalar-dependencias-frontend)
    - [8. Compilar recursos](#8-compilar-recursos)
  - [Configuración](#configuración)
  - [Ejecución](#ejecución)
    - [Opción 1: iniciar todos los servicios](#opción-1-iniciar-todos-los-servicios)
    - [Opción 2: iniciar por separado](#opción-2-iniciar-por-separado)
  - [Funcionalidades](#funcionalidades)
    - [Gestión de usuarios](#gestión-de-usuarios)
    - [Obras de arte](#obras-de-arte)
    - [Favoritos](#favoritos)
    - [Comentarios](#comentarios)
    - [Valoraciones de obras](#valoraciones-de-obras)
    - [Publicaciones](#publicaciones)
    - [Imágenes de obras y publicaciones](#imágenes-de-obras-y-publicaciones)
    - [Detección de imágenes duplicadas](#detección-de-imágenes-duplicadas)
    - [Validación anti-duplicados](#validación-anti-duplicados)
    - [Solicitudes de compra](#solicitudes-de-compra)
    - [Notificaciones](#notificaciones)
  - [Roles y permisos](#roles-y-permisos)
    - [Super administrador](#super-administrador)
    - [Administrador](#administrador)
    - [Usuario](#usuario)
  - [Sistema de compras y ventas](#sistema-de-compras-y-ventas)
    - [Proceso de compra](#proceso-de-compra)
    - [Registro de operaciones](#registro-de-operaciones)
  - [Gestión de saldo](#gestión-de-saldo)
    - [Cuenta central de ventas](#cuenta-central-de-ventas)
  - [Exposiciones](#exposiciones)
  - [Panel de administración](#panel-de-administración)
  - [Base de datos](#base-de-datos)
    - [Entidades principales](#entidades-principales)
    - [Imágenes en la base de datos](#imágenes-en-la-base-de-datos)
  - [Estructura del proyecto](#estructura-del-proyecto)
  - [Rutas principales](#rutas-principales)
  - [Navegación y cabecera](#navegación-y-cabecera)
  - [Tema claro y oscuro](#tema-claro-y-oscuro)
  - [Persistencia de datos](#persistencia-de-datos)
    - [Comandos de migración](#comandos-de-migración)
  - [Copias de seguridad](#copias-de-seguridad)
    - [Opciones](#opciones)
    - [Restaurar una copia](#restaurar-una-copia)
  - [Pruebas](#pruebas)
  - [Seguridad](#seguridad)
  - [Consideraciones finales](#consideraciones-finales)

---

## Descripción

Galería de Cuadros es una aplicación web que ofrece un espacio digital para la publicación, consulta y gestión de obras de arte.

Incluye herramientas para la interacción entre usuarios, la administración de contenidos y la gestión de compraventa de obras.

### Características principales

- Gestión de usuarios y perfiles.
- Publicación y administración de obras.
- Categorías artísticas.
- Sistema de favoritos.
- Comentarios en obras y publicaciones con respuestas.
- Publicaciones y artículos.
- Valoraciones de obras con estrellas.
- Organización de exposiciones.
- Solicitudes de compra.
- Notificaciones internas de actividad.
- Gestión de saldo virtual.
- Administración de ventas.
- Panel de administración con métricas.
- Historial de operaciones.
- Interfaz responsive.
- Tema claro y oscuro.
- Preferencia de tema guardada por usuario.
- Navegación adaptable con menú animado en móvil y tablet.
- Subida de imágenes para obras y publicaciones con vista previa.
- Validación anti-duplicados en usuarios, obras, publicaciones, comentarios y demás registros.
- Imágenes de demostración generadas automáticamente por los seeders.

---

## Tecnologías

| Tecnología | Función |
|---|---|
| PHP 8.2+ | Backend |
| Laravel 12 | Framework principal |
| Inertia.js | Comunicación frontend/backend |
| Vue 3 | Interfaz de usuario |
| Tailwind CSS | Diseño y estilos |
| SQLite | Base de datos |
| Composer | Dependencias PHP |
| Node.js 20+ | Entorno JavaScript |
| npm | Dependencias frontend |
| Vite | Compilación frontend |

---

## Requisitos

Antes de instalar el proyecto es necesario disponer de:

- PHP 8.2 o superior.
- Composer.
- Node.js 20 o superior.
- npm.
- Extensiones PHP necesarias para Laravel y SQLite.

Comprobar las versiones:

```bash
php -v
composer -V
node -v
npm -v
```

---

## Instalación

### 1. Clonar el repositorio

```bash
git clone URL_DEL_REPOSITORIO
```

Acceder al proyecto:

```bash
cd galeria-cuadros
```

### 2. Instalar dependencias

```bash
composer install
```

### 3. Configurar el entorno

Crear el archivo `.env`:

```bash
cp .env.example .env
```

En Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

### 4. Generar la clave

```bash
php artisan key:generate
```

### 5. Configurar SQLite

Crear el archivo de base de datos:

```bash
touch database/database.sqlite
```

En PowerShell:

```powershell
New-Item database/database.sqlite -ItemType File
```

Configurar en `.env`:

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

### 6. Ejecutar migraciones

```bash
php artisan migrate
```

Crear el enlace para las imágenes subidas:

```bash
php artisan storage:link
```

Opcionalmente, cargar los datos de demostración (incluyen imágenes generadas automáticamente):

```bash
php artisan db:seed
```

### 7. Instalar dependencias frontend

```bash
npm install
```

### 8. Compilar recursos

```bash
npm run build
```

---

## Configuración

El archivo `.env` contiene las variables de configuración del entorno.

Variables principales:

| Variable | Descripción |
|---|---|
| APP_NAME | Nombre de la aplicación |
| APP_ENV | Entorno de ejecución |
| APP_DEBUG | Modo depuración |
| APP_URL | Dirección de la aplicación |
| DB_CONNECTION | Sistema de base de datos |
| DB_DATABASE | Ruta de la base de datos |
| SESSION_DRIVER | Almacenamiento de sesiones |
| SESSION_LIFETIME | Duración de las sesiones |
| FILESYSTEM_DISK | Disco de almacenamiento por defecto |

Las imágenes se guardan siempre en el disco `public`, independientemente de `FILESYSTEM_DISK`.

En producción se recomienda:

```env
APP_ENV=production
APP_DEBUG=false
```

El archivo `.env` no debe publicarse en GitHub.

---

## Ejecución

### Opción 1: iniciar todos los servicios

```bash
composer dev
```

### Opción 2: iniciar por separado

Terminal 1:

```bash
php artisan serve
```

Terminal 2:

```bash
npm run dev
```

La aplicación estará disponible en:

http://127.0.0.1:8000

---

## Funcionalidades

### Gestión de usuarios

- Inicio de sesión.
- Gestión de perfiles.
- Edición de información personal.
- Administración de usuarios.
- Estadísticas personales.

La aplicación utiliza autenticación y no dispone de registro público según su configuración actual.

### Obras de arte

- Publicación de obras.
- Edición de obras propias, incluido el reemplazo de la imagen.
- Página «Mis obras» para gestionar las obras propias.
- Eliminación de obras autorizadas.
- Consulta de detalles.
- Clasificación por categorías.
- Búsqueda, filtros avanzados y ordenación.
- Visualización del estado de venta y de la valoración media.
- Las obras vendidas no se pueden editar.

La galería de obras (`/cuadros`) admite:

- Búsqueda por título y descripción.
- Filtro por categoría, técnica, estado y año.
- Filtro por rango de precio (mínimo y máximo).
- Ordenación por fecha (más recientes o más antiguas), número de favoritos, número de comentarios, valoración media o precio (ascendente o descendente).

### Favoritos

Permite guardar obras de arte para consultarlas posteriormente.

### Comentarios

Permite interactuar con las obras y publicaciones mediante comentarios.

Características:

- Creación de comentarios en obras y publicaciones.
- Respuestas a comentarios con **un solo nivel de anidación**.
- Las respuestas deben pertenecer a la misma obra o publicación que el comentario original.
- Edición y eliminación de los comentarios propios.
- Los administradores pueden moderar (editar o eliminar) cualquier comentario.
- Al eliminar un comentario también se eliminan sus respuestas.
- Un usuario no puede repetir el mismo comentario en la misma obra o publicación.

### Valoraciones de obras

Los usuarios pueden valorar las obras con una puntuación de 1 a 5 estrellas.

Características:

- Cada usuario solo puede emitir **una valoración por obra**; al volver a valorar se actualiza la anterior.
- El propietario no puede valorar sus propias obras.
- La valoración media y el número de votos se muestran en la galería y en el detalle de cada obra.
- La portada destaca la obra mejor valorada.
- La galería permite ordenar las obras por mejor valoración.

Tabla utilizada:

```text
ratings
```

La restricción de una valoración por usuario y obra se garantiza mediante un índice único en la base de datos.

### Publicaciones

- Crear artículos.
- Editar publicaciones propias.
- Eliminar publicaciones autorizadas.
- Clasificar contenido.
- Relacionar publicaciones con obras.
- Consultar publicaciones publicadas.
- Añadir o reemplazar la imagen de la publicación.

### Imágenes de obras y publicaciones

Tanto las obras como las publicaciones permiten subir una imagen desde el formulario de creación. En ambos casos la imagen también puede reemplazarse al editarlas.

Características:

- Formatos admitidos: **JPG, JPEG, PNG, WEBP y GIF**.
- Tamaño máximo: **5 MB**.
- Vista previa de la imagen seleccionada antes de guardar.
- Si no se sube imagen, la obra o publicación conserva su emoji o su imagen anterior.
- Al reemplazar una imagen se elimina el archivo anterior.
- Al borrar una obra o publicación se elimina también su archivo de imagen.
- Cada archivo se nombra con el hash **SHA-256** de su contenido, por lo que dos subidas idénticas comparten la misma ruta física.
- También se calculan las huellas perceptuales **pHash** y **dHash**, que detectan la misma imagen aunque esté redimensionada o recomprimida.

Las imágenes se almacenan en el disco público de Laravel:

```text
storage/app/public/paintings/
storage/app/public/publications/
storage/app/public/demo/
```

Para que las imágenes sean accesibles desde el navegador es necesario crear el enlace simbólico:

```bash
php artisan storage:link
```

El enlace genera `public/storage`, que apunta a `storage/app/public`.

Para calcular las huellas perceptuales de las imágenes que ya existían:

```bash
php artisan images:fingerprint
```

La gestión de archivos se centraliza en:

```text
app/Services/ImageService.php
```

### Detección de imágenes duplicadas

Cada imagen se compara en dos niveles antes de guardarla:

| Nivel | Método | Qué detecta |
|---|---|---|
| Exacto | SHA-256 | Archivos idénticos byte a byte |
| Aproximado | pHash y dHash | Imágenes redimensionadas, recomprimidas o con otro formato |

- pHash y dHash generan huellas de 64 bits que se comparan mediante la **distancia de Hamming**.
- Se considera casi duplicada cuando cualquiera de las dos distancias está dentro del umbral configurado.
- La comparación es conjunta entre obras y publicaciones.
- Las imágenes de un solo color se validan solo por SHA-256, porque el hash perceptual no las distingue.
- No se detectan recortes, rotaciones ni cambios de perspectiva. Para esos casos harían falta métodos adicionales como ORB; SSIM y CLIP tampoco están implementados.
- Los umbrales se configuran en `.env`:

```env
IMAGE_PHASH_MAX_DISTANCE=5
IMAGE_DHASH_MAX_DISTANCE=5
```

### Validación anti-duplicados

Antes de guardar o editar un registro se comprueba que no exista otro equivalente. La regla reutilizable `App\Rules\UniqueNormalized` compara el texto ignorando mayúsculas/minúsculas, espacios sobrantes y tildes.

| Entidad | Restricción |
|---|---|
| Usuarios | `name` y `email` únicos |
| Obras | `title` y `description` únicos; imagen idéntica o casi duplicada |
| Publicaciones | `title`, `summary` y `content` únicos; imagen idéntica o casi duplicada |
| Comentarios de obras | un usuario no puede repetir el mismo comentario en la misma obra |
| Comentarios de publicaciones | un usuario no puede repetir el mismo comentario en la misma publicación |
| Categorías | `name` único |
| Exposiciones | `title` único |
| Favoritos | un usuario solo puede guardar una obra una vez |
| Solicitudes de compra | un comprador solo puede solicitar una obra una vez |
| Valoraciones | un usuario solo puede valorar cada obra una vez |
| Cuenta del super administrador | solo puede existir una cuenta de cada tipo |
| Ingresos por venta | cada solicitud de compra genera un único ingreso |

- Los campos opcionales vacíos no se consideran duplicados.
- Al editar se excluye el propio registro.
- Las imágenes se validan por su hash **SHA-256** (idénticas) y por **pHash/dHash** (casi duplicadas), de forma conjunta entre obras y publicaciones.
- La base de datos refuerza las restricciones con índices únicos.
- Si se detecta un duplicado, se muestra un error y no se guarda el registro.

### Solicitudes de compra

Los usuarios pueden enviar solicitudes para adquirir obras.

Estados disponibles:

| Estado | Descripción |
|---|---|
| pending | Pendiente de aprobación |
| contacted | Contactado |
| approved | Aprobado |
| rejected | Rechazado |
| declined | Rechazado |
| closed | Cerrado |

Los administradores pueden revisar, aprobar o rechazar las solicitudes.

### Notificaciones

La aplicación incorpora un sistema interno de notificaciones que se almacenan en la base de datos y se consultan desde la web.

Se genera una notificación cuando:

- Un usuario comenta una obra o publicación de otro usuario.
- Un usuario responde a un comentario ajeno.
- Un usuario añade una obra de otro usuario a favoritos.
- Un comprador envía una solicitud de compra al vendedor.
- Un administrador cambia el estado de una solicitud de compra.

Características:

- La campana de la cabecera muestra el número de notificaciones sin leer.
- La página `/notificaciones` muestra el listado paginado con acceso directo al contenido relacionado.
- Al tocar una notificación se marca como leída y se abre el contenido al que hace referencia en un solo paso.
- El botón «Marcar como leída» sigue disponible para leerla sin salir del listado.
- Las notificaciones se pueden marcar como leídas de forma individual o todas a la vez.
- Las notificaciones se pueden eliminar de forma individual o todas a la vez.
- No se envían auto-avisos: el autor de la acción nunca recibe notificación por ella.

Las notificaciones se implementan con el canal `database` de Laravel y se envían mediante la cola de trabajos:

```text
app/Notifications/
```

Sobre el procesamiento de la cola:

- En desarrollo local, `.env.example` configura `QUEUE_CONNECTION=sync`, de modo que las notificaciones se entregan al instante sin necesidad de un worker.
- Si cambias a `QUEUE_CONNECTION=database` (u otro driver de cola), las notificaciones quedarán pendientes hasta que se procesen.
- El comando `composer dev` ya lanza un worker de la cola junto al servidor y a Vite.
- Si arrancas los servicios por separado con `QUEUE_CONNECTION=database`, ejecuta también:

```bash
php artisan queue:work
```

---

## Roles y permisos

La aplicación dispone de tres niveles de acceso:

| Rol | Permisos |
|---|---|
| Super administrador | Gestión administrativa completa y cuenta central de ventas. |
| Administrador | Gestión de usuarios, obras y solicitudes de compra. |
| Usuario | Gestión del perfil, obras propias, favoritos, comentarios y publicaciones propias. |

### Super administrador

- Dispone de privilegios administrativos.
- Gestiona la cuenta central de ventas.
- Consulta el historial de ingresos.
- Administra las operaciones de compraventa.

### Administrador

- Gestiona usuarios.
- Consulta perfiles.
- Elimina obras.
- Modera comentarios de cualquier usuario.
- Revisa solicitudes de compra.
- Aprueba o rechaza operaciones.
- Consulta el histórico de compras y ventas.
- Consulta el panel de administración con las métricas de la galería.

### Usuario

- Gestiona su perfil.
- Publica, edita y gestiona sus obras.
- Añade favoritos.
- Comenta obras y publicaciones y responde a comentarios.
- Valora obras de otros usuarios.
- Crea y gestiona sus publicaciones.
- Recibe notificaciones de la actividad en su contenido.
- Puede crear exposiciones si dispone del permiso de vendedor (`is_vendor`).

La autorización se implementa mediante Policies y controles de acceso en las rutas de Laravel.

---

## Sistema de compras y ventas

La aplicación incorpora un sistema de compraventa mediante solicitudes y aprobación administrativa.

### Proceso de compra

1. El comprador envía una solicitud.
2. La solicitud queda pendiente de aprobación.
3. Un administrador revisa la operación.
4. Se comprueba que el comprador dispone de saldo suficiente.
5. Se descuenta el importe de la cuenta del comprador.
6. Se ingresa el importe en la cuenta independiente del super administrador.
7. La obra queda marcada como vendida.
8. Las demás solicitudes relacionadas se actualizan o cierran.

### Registro de operaciones

Las operaciones se gestionan mediante:

```text
app/Services/SaleService.php
```

El proceso utiliza transacciones de base de datos para mantener la consistencia de los movimientos.

---

## Gestión de saldo

Cada usuario dispone de un saldo virtual que puede consultar desde su apartado personal.

Funcionalidades:

- Consulta de saldo.
- Recarga de saldo.
- Historial de recargas.
- Registro de compras.
- Consulta de movimientos.

Los movimientos se almacenan en:

```text
balance_transactions
```

### Cuenta central de ventas

Los ingresos de las ventas se registran en una cuenta independiente.

Tablas utilizadas:

```text
accounts
account_transactions
```

El panel administrativo permite consultar el saldo acumulado y el historial de movimientos.

**Importante:** el saldo es una simulación interna. No existe integración con una pasarela de pago real.

---

## Exposiciones

El sistema permite organizar obras mediante exposiciones con fecha de inicio y de fin.

Características:

- Creación y edición de exposiciones por parte del organizador o de los administradores.
- Solo pueden crear exposiciones los administradores y los usuarios con permiso de vendedor (`is_vendor`).
- Asociación de obras: solo se pueden adjuntar obras públicas o propias.
- Búsqueda por título y descripción.
- Filtro por estado: `upcoming` (próxima), `active` (activa) o `finished` (finalizada).
- El estado se calcula a partir de las fechas y no se almacena en la base de datos.
- El listado muestra primero las exposiciones próximas, después las activas y por último las finalizadas.
- El título de cada exposición debe ser único.
- La fecha de fin no puede ser anterior a la de inicio.
- Cada exposición muestra el número de obras y una portada con la primera de ellas.
- Consulta de las exposiciones relacionadas con cada obra.

La relación entre exposiciones y obras utiliza una relación muchos a muchos (N:M).

Tabla intermedia:

```text
exhibition_painting
```

---

## Panel de administración

Los administradores disponen de un panel con las métricas de la galería:

```text
/admin
```

Muestra:

- Totales de usuarios, obras, obras públicas, obras vendidas, exposiciones, comentarios, valoraciones y solicitudes pendientes.
- Ventas de los últimos seis meses.
- Número de obras por categoría.
- Artistas con más obras.
- Solicitudes de compra pendientes de revisión.
- Últimas ventas completadas.

---

## Base de datos

El proyecto utiliza SQLite.

Ubicación:

```text
database/database.sqlite
```

### Entidades principales

| Tabla | Descripción |
|---|---|
| users | Usuarios y su preferencia de tema |
| paintings | Obras de arte (incluye `image_path`, `image_hash`, `image_phash` e `image_dhash`) |
| categories | Categorías |
| favorites | Favoritos |
| comments | Comentarios de obras (incluye `parent_id` para las respuestas) |
| publications | Publicaciones (incluye `image_path`, `image_hash`, `image_phash` e `image_dhash`) |
| publication_comments | Comentarios de publicaciones (incluye `parent_id` para las respuestas) |
| exhibitions | Exposiciones |
| exhibition_painting | Relación entre exposiciones y obras |
| ratings | Valoraciones de obras (1 a 5) |
| inquiries | Solicitudes de compra |
| notifications | Notificaciones internas de los usuarios |
| accounts | Cuentas administrativas |
| account_transactions | Movimientos de cuentas |
| balance_transactions | Movimientos de saldo |

Las relaciones se gestionan mediante Eloquent ORM.

### Imágenes en la base de datos

Las tablas `paintings` y `publications` incorporan cuatro columnas para gestionar las imágenes:

| Columna | Descripción |
|---|---|
| `image_path` | Ruta relativa del archivo dentro de `storage/app/public` |
| `image_hash` | Hash SHA-256 del contenido, con índice único |
| `image_phash` | Huella perceptual pHash de 64 bits |
| `image_dhash` | Huella perceptual dHash de 64 bits |

Las migraciones que añaden estas columnas son:

```text
database/migrations/2026_11_05_000001_add_images_to_paintings_and_publications.php
database/migrations/2026_11_09_000002_add_perceptual_hashes_to_paintings_and_publications.php
```

Los archivos de imagen no se guardan en el repositorio: se generan al subirlos o al ejecutar los seeders.

---

## Estructura del proyecto

```text
galeria-cuadros/
│
├── app/
│   ├── Console/
│   │   └── Commands/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   ├── Models/
│   ├── Notifications/
│   ├── Policies/
│   ├── Services/
│   └── Support/
│
├── bootstrap/
├── config/
│
├── database/
│   ├── factories/
│   ├── migrations/
│   ├── seeders/
│   └── database.sqlite
│
├── public/
│
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   ├── app.js
│   │   ├── bootstrap.js
│   │   └── theme.js
│   ├── shared/
│   │   ├── Layout.vue
│   │   ├── Nav.vue
│   │   ├── NavLink.vue
│   │   └── ThemeToggle.vue
│   ├── pages/
│   └── views/
│       └── app.blade.php
│
├── routes/
│   └── web.php
│
├── storage/
│   └── app/
│       └── public/
│           ├── paintings/
│           ├── publications/
│           └── demo/
│
├── tests/
│
├── .env.example
├── composer.json
├── package.json
├── vite.config.js
└── README.md
```

---

## Rutas principales

| Ruta | Descripción |
|---|---|
| `/` | Página principal |
| `/login` | Inicio de sesión |
| `/cuadros` | Galería de obras con búsqueda, filtros y ordenación |
| `/cuadros/create` | Crear una obra |
| `/cuadros/{painting}` | Detalle de una obra |
| `/cuadros/{painting}/edit` | Editar una obra (propietario o administrador) |
| `/mis-obras` | Obras del usuario autenticado |
| `/publicaciones` | Listado de publicaciones |
| `/publicaciones/create` | Crear publicación |
| `/publicaciones/{publication}` | Detalle de publicación |
| `/publicaciones/{publication}/edit` | Editar publicación |
| `/exposiciones` | Listado de exposiciones con búsqueda y filtro por estado |
| `/exposiciones/create` | Crear exposición (administrador o vendedor) |
| `/exposiciones/{exhibition}` | Detalle de exposición |
| `/exposiciones/{exhibition}/edit` | Editar exposición (organizador o administrador) |
| `/notificaciones` | Notificaciones del usuario autenticado |
| `/users/list` | Listado de usuarios |
| `/users/view/{user}` | Consultar perfil |
| `/users/create` | Crear usuario |
| `/users/edit/{user}` | Editar usuario |
| `/admin` | Panel de administración con métricas (solo administradores) |
| `/settings` | Ajustes y estadísticas |
| `/mi-cuenta` | Gestión del saldo |
| `/mi-cuenta/tema` | Guardar la preferencia de tema |
| `/mi-cuenta/saldo` | Recarga de saldo |
| `/cuenta-super-admin` | Cuenta central de ventas |
| `/inquiries` | Solicitudes de compra |
| `/inquiries/history` | Histórico de operaciones |

Las rutas están protegidas mediante autenticación y autorización según el rol correspondiente.

---

## Navegación y cabecera

La cabecera forma parte del layout compartido y se adapta al tamaño de pantalla.

Características:

- La cabecera ocupa todo el ancho de la ventana; el espaciado horizontal se aplica al contenido interno.
- En escritorio (a partir del breakpoint `xl`) los enlaces se muestran en una barra horizontal.
- En móvil y tablet se muestra un botón que despliega la navegación.
- El desplegable aparece con una transición de opacidad, escala y desplazamiento.
- Los enlaces internos entran de forma escalonada.
- El botón y los enlaces tienen respuesta visual al presionarlos.
- En móvil y tablet el menú incluye la opción para cambiar entre tema claro y oscuro.
- Las animaciones se desactivan si el usuario configura `prefers-reduced-motion`.

El componente de navegación se encuentra en:

```text
resources/shared/Nav.vue
```

---

## Tema claro y oscuro

La aplicación dispone de una interfaz con tema claro y oscuro.

Características:

- Cambio de tema desde la cabecera y desde la pantalla de inicio de sesión.
- Iconos de sol y luna.
- Preferencia guardada por usuario en la base de datos.
- `localStorage` se utiliza como caché para aplicar el tema antes de montar Vue y evitar parpadeos.
- La preferencia se mantiene al iniciar sesión, cerrar sesión o navegar entre páginas.
- El tema se comparte entre navegadores y dispositivos de la misma cuenta.
- Los invitados conservan su preferencia solo en el navegador actual.
- Las preferencias existentes se conservan mediante una migración automática de la clave anterior.
- Adaptación de los componentes mediante Tailwind CSS.

La lógica del tema se centraliza en:

```text
resources/js/theme.js
```

La preferencia se guarda en la columna `theme` de la tabla `users` mediante la ruta `POST /mi-cuenta/tema`.

---

## Persistencia de datos

La base de datos SQLite se encuentra en:

```text
database/database.sqlite
```

Los datos se almacenan de forma persistente y no se eliminan al reiniciar el servidor.

### Comandos de migración

| Comando | Efecto |
|---|---|
| `php artisan migrate` | Ejecuta migraciones pendientes. |
| `php artisan migrate:status` | Consulta migraciones. |
| `php artisan migrate:fresh` | Elimina y recrea todas las tablas. |
| `php artisan migrate:refresh` | Revierte y vuelve a ejecutar migraciones. |
| `php artisan db:wipe` | Elimina todas las tablas. |

**Advertencia:** los comandos `migrate:fresh`, `migrate:refresh` y `db:wipe` pueden provocar la pérdida de datos.

Para conservar los datos existentes utiliza:

```bash
php artisan migrate
```

---

## Copias de seguridad

El proyecto dispone de un comando para generar copias de seguridad:

```bash
php artisan db:backup
```

También se puede ejecutar:

```bash
composer db:backup
```

### Opciones

Conservar 30 copias:

```bash
php artisan db:backup --keep=30
```

Conservar todas las copias:

```bash
php artisan db:backup --keep=0
```

Las copias se almacenan en:

```text
database/backups/
```

### Restaurar una copia

Detener los servicios y restaurar el archivo de respaldo:

```powershell
Copy-Item "database/backups/COPIA.sqlite" "database/database.sqlite" -Force
```

Se recomienda almacenar las copias fuera del repositorio y protegerlas adecuadamente.

---

## Pruebas

Ejecutar las pruebas automatizadas:

```bash
php artisan test
```

O utilizar:

```bash
composer test
```

Las pruebas cubren, entre otras áreas, autenticación, autorización, usuarios, obras (incluida su edición y la página «Mis obras»), publicaciones, favoritos, comentarios y sus respuestas, valoraciones, notificaciones, exposiciones, solicitudes de compra y el panel de administración. También verifican que se rechacen nombres, correos, títulos, descripciones, resúmenes, contenidos, comentarios e imágenes duplicadas o casi duplicadas.

### Colección de Postman

El repositorio incluye una colección de Postman para probar los endpoints de forma manual:

```text
Mi-Proyecto.postman_collection.json
```

Está organizada en carpetas por módulo (autenticación, cuadros, publicaciones, exposiciones, notificaciones, solicitudes de compra, panel de administración y usuarios) e incluye ejemplos del cuerpo de las peticiones. Para mantener la sesión, ejecuta primero la petición `login.store` con las credenciales de un usuario existente.

---

## Seguridad

Para proteger el proyecto:

- No publicar archivos `.env`.
- No incluir contraseñas en el repositorio.
- No publicar tokens privados.
- Mantener las dependencias actualizadas.
- Utilizar HTTPS en producción.
- Desactivar `APP_DEBUG` en producción.
- Proteger las rutas mediante autenticación y autorización.
- Validar los datos recibidos desde formularios.
- Proteger las copias de seguridad.
- Evitar publicar bases de datos que contengan información personal.

Los datos de prueba deben generarse mediante seeders y no deben contener credenciales reales.

---

## Consideraciones finales

- Backend desarrollado con Laravel 12.
- Frontend desarrollado con Vue 3 e Inertia.js.
- Diseño mediante Tailwind CSS.
- Almacenamiento mediante SQLite.
- Sistema de roles y permisos.
- Gestión de obras y exposiciones.
- Sistema interno de solicitudes y ventas.
- Valoraciones de obras y notificaciones internas.
- Panel de administración con métricas.
- Gestión de saldo virtual simulado.
- Interfaz adaptada a dispositivos móviles, tablets y ordenadores.
- Subida de imágenes y protección frente a registros duplicados.

---

**Galería de Cuadros**  
Aplicación web para la gestión, publicación y compraventa de obras de arte.
