
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
    - [Publicaciones](#publicaciones)
    - [Imágenes de obras y publicaciones](#imágenes-de-obras-y-publicaciones)
    - [Validación anti-duplicados](#validación-anti-duplicados)
    - [Gestión de archivos](#gestión-de-archivos)
    - [Solicitudes de compra](#solicitudes-de-compra)
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
- Comentarios en obras.
- Publicaciones y artículos.
- Comentarios en publicaciones.
- Organización de exposiciones.
- Solicitudes de compra.
- Gestión de saldo virtual.
- Administración de ventas.
- Panel de administración.
- Historial de operaciones.
- Interfaz responsive.
- Tema claro y oscuro.
- Preferencia de tema guardada por usuario.
- Navegación adaptable con menú animado en móvil y tablet.
- Subida de imágenes para obras y publicaciones con vista previa.
- Validación de títulos, descripciones, contenidos e imágenes duplicadas.
- Imágenes de demostración generadas automáticamente por los seeders.
- Panel de gestión de archivos para administradores.

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
- Edición de obras propias.
- Eliminación de obras autorizadas.
- Consulta de detalles.
- Clasificación por categorías.
- Búsqueda y filtros.
- Visualización del estado de venta.

### Favoritos

Permite guardar obras de arte para consultarlas posteriormente.

### Comentarios

Permite interactuar con las obras mediante comentarios y gestionar los comentarios asociados a las publicaciones.

### Publicaciones

- Crear artículos.
- Editar publicaciones propias.
- Eliminar publicaciones autorizadas.
- Clasificar contenido.
- Relacionar publicaciones con obras.
- Consultar publicaciones publicadas.
- Añadir o reemplazar la imagen de la publicación.

### Imágenes de obras y publicaciones

Tanto las obras como las publicaciones permiten subir una imagen desde el formulario de creación. En el caso de las publicaciones, la imagen también puede reemplazarse al editarlas.

Características:

- Formatos admitidos: **JPG, JPEG, PNG, WEBP y GIF**.
- Tamaño máximo: **5 MB**.
- Vista previa de la imagen seleccionada antes de guardar.
- Si no se sube imagen, la obra o publicación conserva su emoji o su imagen anterior.
- Al reemplazar una imagen se elimina el archivo anterior.
- Al borrar una obra o publicación se elimina también su archivo de imagen.
- Cada archivo se nombra con el hash **SHA-256** de su contenido, por lo que dos subidas idénticas comparten la misma ruta física.

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

La gestión de archivos se centraliza en:

```text
app/Services/ImageService.php
```

### Validación anti-duplicados

Antes de guardar un registro se comprueba que no exista otro con los mismos datos. La comparación no distingue entre mayúsculas y minúsculas y se aplica también al editar.

| Entidad | Campos únicos |
|---|---|
| Obras | `title`, `description` e imagen (`image_hash`) |
| Publicaciones | `title`, `summary`, `content` e imagen (`image_hash`) |

- El título, la descripción o el resumen vacíos no se consideran duplicados.
- En las ediciones se excluye el propio registro de la comprobación.
- La imagen se identifica mediante un hash **SHA-256** de su contenido, por lo que dos archivos distintos con la misma imagen también se detectan.
- La comprobación de imágenes se realiza de forma conjunta entre obras y publicaciones: una misma imagen no puede registrarse dos veces.
- La base de datos mantiene un índice único sobre `image_hash` como protección adicional.
- Cuando hay un duplicado, el formulario muestra un mensaje de error y no se crea el registro.

Los seeders también generan títulos, resúmenes y contenidos únicos para que los datos de demostración respeten estas reglas.

### Gestión de archivos

Los administradores disponen de una página para revisar el estado de las imágenes y los registros duplicados:

```text
/admin/archivos
```

La página muestra:

- Número y tamaño de los archivos.
- Archivos referenciados, huérfanos y duplicados.
- Registros con referencias a archivos inexistentes.
- Grupos de registros duplicados exactos.
- Títulos repetidos con contenido distinto (conflictos para revisión manual).

Acciones disponibles:

| Acción | Efecto |
|---|---|
| Limpieza segura | Fusiona registros duplicados exactos y elimina archivos duplicados y huérfanos. |
| Eliminar títulos repetidos | Además fusiona los registros que comparten título aunque el contenido sea distinto, conservando el más antiguo. |

En ambos casos los comentarios de los registros eliminados se trasladan al registro conservado.

Las mismas tareas pueden ejecutarse desde la consola:

```bash
# Ver el informe sin modificar nada
php artisan gallery:clean --dry-run

# Limpieza segura
php artisan gallery:clean

# Incluir títulos repetidos con contenido distinto
php artisan gallery:clean --strict
```

La lógica se centraliza en:

```text
app/Services/FileManager.php
app/Console/Commands/CleanGallery.php
app/Http/Controllers/FileManagementController.php
```

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
- Revisa solicitudes de compra.
- Aprueba o rechaza operaciones.
- Consulta el histórico de compras y ventas.

### Usuario

- Gestiona su perfil.
- Publica obras.
- Edita sus propias obras.
- Añade favoritos.
- Comenta obras.
- Crea y gestiona sus publicaciones.

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

El sistema permite organizar obras mediante exposiciones.

Características:

- Creación de exposiciones.
- Asociación de obras.
- Gestión de fechas.
- Consulta de exposiciones relacionadas con cada obra.

La relación entre exposiciones y obras utiliza una relación muchos a muchos (N:M).

Tabla intermedia:

```text
exhibition_painting
```

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
| paintings | Obras de arte (incluye `image_path` e `image_hash`) |
| categories | Categorías |
| favorites | Favoritos |
| comments | Comentarios de obras |
| publications | Publicaciones (incluye `image_path` e `image_hash`) |
| publication_comments | Comentarios de publicaciones |
| exhibitions | Exposiciones |
| exhibition_painting | Relación entre exposiciones y obras |
| inquiries | Solicitudes de compra |
| accounts | Cuentas administrativas |
| account_transactions | Movimientos de cuentas |
| balance_transactions | Movimientos de saldo |

Las relaciones se gestionan mediante Eloquent ORM.

### Imágenes en la base de datos

Las tablas `paintings` y `publications` incorporan dos columnas para gestionar las imágenes:

| Columna | Descripción |
|---|---|
| `image_path` | Ruta relativa del archivo dentro de `storage/app/public` |
| `image_hash` | Hash SHA-256 del contenido, con índice único |

La migración que añade estas columnas es:

```text
database/migrations/2026_11_05_000001_add_images_to_paintings_and_publications.php
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
| `/cuadros` | Galería de obras |
| `/cuadros/create` | Crear una obra |
| `/cuadros/{painting}` | Detalle de una obra |
| `/publicaciones` | Listado de publicaciones |
| `/publicaciones/create` | Crear publicación |
| `/publicaciones/{publication}` | Detalle de publicación |
| `/publicaciones/{publication}/edit` | Editar publicación |
| `/users/list` | Listado de usuarios |
| `/users/view/{user}` | Consultar perfil |
| `/users/create` | Crear usuario |
| `/users/edit/{user}` | Editar usuario |
| `/admin/archivos` | Gestión de archivos e imágenes (solo administradores) |
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

Las pruebas cubren, entre otras áreas, autenticación, autorización, obras, publicaciones, favoritos, comentarios y solicitudes de compra. También verifican que se rechacen títulos, descripciones, resúmenes, contenidos e imágenes duplicadas, y que la limpieza de archivos elimine huérfanos, duplicados y registros repetidos.

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
- Gestión de saldo virtual simulado.
- Interfaz adaptada a dispositivos móviles, tablets y ordenadores.
- Subida de imágenes y protección frente a registros duplicados.

---

**Galería de Cuadros**  
Aplicación web para la gestión, publicación y compraventa de obras de arte.
