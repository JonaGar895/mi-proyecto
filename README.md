
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
| paintings | Obras de arte |
| categories | Categorías |
| favorites | Favoritos |
| comments | Comentarios de obras |
| publications | Publicaciones |
| publication_comments | Comentarios de publicaciones |
| exhibitions | Exposiciones |
| exhibition_painting | Relación entre exposiciones y obras |
| inquiries | Solicitudes de compra |
| accounts | Cuentas administrativas |
| account_transactions | Movimientos de cuentas |
| balance_transactions | Movimientos de saldo |

Las relaciones se gestionan mediante Eloquent ORM.

---

## Estructura del proyecto

```text
galeria-cuadros/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   ├── Models/
│   ├── Policies/
│   └── Services/
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

---

**Galería de Cuadros**  
Aplicación web para la gestión, publicación y compraventa de obras de arte.
