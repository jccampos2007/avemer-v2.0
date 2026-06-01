# Sistema de Gestión Académica — AVEMER

Sistema web para la administración integral de programas educativos: diplomados, talleres, maestrías y eventos. Construido con una arquitectura PHP MVC propia y modular.

## Características

- **Gestión de Registro**: Alumnos, Instructores y Coordinadores.
- **Talleres / Cursos**: Catálogo, apertura de convocatorias e inscripciones.
- **Eventos**: Catálogo, apertura e inscripciones.
- **Diplomados**: Catálogo, capítulos, apertura, control de capítulos, inscripciones y pre-inscripciones web.
- **PreinscripcionLanding**: Landing público con flujo paso a paso (Tailwind + FontAwesome + XHR), búsqueda/creación de alumno, y pre-inscripción.
- **Maestrías**: Catálogo, apertura e inscripciones.
- **Pagos**: Cuotas, pagos, compensaciones y cronograma.
- **Mensajería**: Listas de correo, mensajes y listas de envío (integración con PHPMailer).
- **Mantenimiento**: Sedes, bancos, duraciones, profesiones/oficios, ciudades/estados.
- **Seguridad**: Usuarios, grupos y permisos RBAC (CRUD por ventana).
- **DataTables Server-Side**: Todas las listas cargan con paginación, búsqueda y ordenamiento desde el servidor.
- **Exportación**: Exportación a Excel y PDF desde cualquier listado (DataTables Buttons).
- **Autocompletado**: Búsqueda AJAX con jQuery UI Autocomplete.
- **Responsive**: Interfaz adaptable con Tailwind CSS y menú colapsable.

## Arquitectura

### Front Controller

Todas las peticiones entran por `public/index.php`. Apache reescribe las URLs amigables a `index.php?url=<ruta>`. El `Router` analiza la URI y despacha al controlador correspondiente.

### Modular MVC

Cada módulo es autocontenido dentro de `App/Modules/<Modulo>/`:

```
App/Modules/Alumnos/
├── AlumnoController.php    ← Lógica del controlador
├── AlumnoModel.php         ← Interacción con la BD
├── alumnos.js              ← JavaScript específico del módulo
└── Views/
    ├── list.php            ← Vista de listado (DataTables)
    └── form.php            ← Vista de formulario (crear/editar)
```

### Servicio de Assets JavaScript

Los archivos JS de cada módulo se sirven a través de un controlador PHP (AssetController) en lugar de estar dentro del document root:

```
URL: /asset/js/Alumnos/alumnos.js
  ↓
AssetController@serveJs("Alumnos", "alumnos.js")
  ↓
Lee App/Modules/Alumnos/alumnos.js
  ↓
Content-Type: application/javascript + Cache-Control
```

Esto mantiene `App/` fuera del alcance del navegador sin necesidad de copiar archivos ni usar .htaccess específico.

### Autoloading

PSR-4: `App\` → `App/`. Configurado en `config/app.php` con un autoloader propio.

## Tecnologías

| Capa | Tecnología |
|------|-----------|
| Backend | PHP 8.5+, MySQL / MariaDB |
| Frontend | Tailwind CSS 4, Alpine.js 3 |
| JavaScript | jQuery 3.7 + jQuery UI 1.13 |
| Tablas | DataTables 1.11 + Buttons + Export (Excel/PDF) |
| Calendario | Flatpickr |
| Íconos | FontAwesome 6 |
| Alertas | SweetAlert2 |
| Email | PHPMailer 7 |
| Tests | PHPUnit 11.5 |
| Assets | Node.js / npm (solo para compilar Tailwind) |

## Estructura del Proyecto

```
php_mvc_app/
├── App/
│   ├── Api/
│   │   └── ApiController.php          ← Endpoints genéricos (api/data, api/search)
│   ├── Core/
│   │   ├── Auth.php                   ← Autenticación y permisos
│   │   ├── AssetController.php        ← Servidor de assets JS
│   │   ├── Controller.php             ← Controlador base (view, model, redirect)
│   │   ├── Database.php               ← Singleton PDO
│   │   └── Router.php                 ← Enrutador de URLs
│   ├── Layout/
│   │   ├── header.php                 ← <head>, CSS, jQuery, main.js
│   │   ├── sidebar.php                ← Menú lateral con Alpine.js
│   │   ├── message.php                ← Flash messages
│   │   └── footer.php                 ← Scripts globales, CDN, módulo JS
│   └── Modules/                       ← Módulos del sistema
│       ├── Alumnos/
│       ├── Auth/
│       ├── Banco/
│       ├── Capitulo/
│       ├── Ciudad/
│       ├── Coordinadores/
│       ├── Correo/
│       ├── Cronograma/
│       ├── Cuota/
│       ├── CursoAbierto/
│       ├── Cursos/
│       ├── Dashboard/
│       ├── Diplomado/
│       ├── DiplomadoAbierto/
│       ├── DiplomadoControl/
│       ├── Docentes/
│       ├── Duracion/
│       ├── Envios/
│       ├── Evento/
│       ├── EventoAbierto/
│       ├── Grupo/
│       ├── InscripcionCurso/
│       ├── InscripcionDiplomado/
│       ├── InscripcionEvento/
│       ├── InscripcionMaestria/
│       ├── Maestria/
│       ├── MaestriaAbierto/
│       ├── Mensajes/
│       ├── Pagos/
│       ├── PreinscripcionDiplomado/
│       ├── PreinscripcionLanding/
│       ├── ProfesionOficio/
│       ├── Sede/
│       └── Users/
├── config/
│   └── app.php                        ← Constantes, autoloader, configuración
├── public/                            ← Document root del servidor web
│   ├── .htaccess                      ← RewriteRule a index.php
│   ├── index.php                      ← Entry point y definición de rutas
│   ├── css/
│   │   ├── base.css                   ← Estilos personalizados
│   │   ├── input.css                  ← Fuente Tailwind (directivas)
│   │   └── output.css                 ← Tailwind compilado
│   ├── image/                         ← Imágenes, logos, avatares
│   └── js/
│       └── main.js                    ← JavaScript global (global functions)
├── vendor/                            ← Dependencias PHP (Composer)
├── node_modules/                      ← Dependencias Node (Tailwind CLI)
├── .env                               ← Variables de entorno (no se sube)
├── .gitignore
├── composer.json
├── package.json
├── tailwind.config.js
└── README.md
```

## Módulos y Rutas

| Ruta | Módulo | Descripción |
|------|--------|-------------|
| `/dashboard` | Dashboard | Panel principal con resumen |
| `/alumnos` | Alumnos | CRUD de alumnos |
| `/docentes` | Docentes | CRUD de instructores |
| `/coordinadores` | Coordinadores | CRUD de coordinadores |
| `/cursos` | Cursos | Catálogo de talleres/cursos |
| `/cursos_abiertos` | CursoAbierto | Apertura de convocatorias |
| `/inscripcion_curso` | InscripcionCurso | Inscripción a talleres |
| `/evento` | Evento | Catálogo de eventos |
| `/evento_abierto` | EventoAbierto | Apertura de eventos |
| `/inscripcion_evento` | InscripcionEvento | Inscripción a eventos |
| `/diplomado` | Diplomado | Catálogo de diplomados |
| `/capitulo` | Capitulo | Capítulos de diplomados |
| `/diplomado_abierto` | DiplomadoAbierto | Apertura de diplomados |
| `/diplomadocontrol` | DiplomadoControl | Control de capítulos por apertura |
| `/inscripcion_diplomado` | InscripcionDiplomado | Inscripción a diplomados |
| `/preinscripcion_diplomado` | PreinscripcionDiplomado | Pre-inscripciones internas |
| `/maestria` | Maestria | Catálogo de maestrías |
| `/maestria_abierto` | MaestriaAbierto | Apertura de maestrías |
| `/inscripcion_maestria` | InscripcionMaestria | Inscripción a maestrías |
| `/cuota` | Cuota | Gestión de cuotas |
| `/pago` | Pagos | Registro de pagos |
| `/cronograma` | Cronograma | Cronograma de pagos |
| `/correo` | Correo | Listas de correo |
| `/mensajes` | Mensajes | Envío de mensajes |
| `/envios` | Envios | Listas de envío |
| `/sede` | Sede | Sedes |
| `/banco` | Banco | Bancos |
| `/duracion` | Duracion | Duraciones |
| `/profesion_oficio` | ProfesionOficio | Profesiones y oficios |
| `/ciudad` | Ciudad | Ciudades y estados |
| `/users` | Users | Usuarios del sistema |
| `/grupo` | Grupo | Grupos y permisos |
| `/login` | Auth | Inicio de sesión |
| `/preinscripcionlanding` | PreinscripcionLanding | Pre-inscripción pública web (Tailwind + XHR) |

## API Endpoints

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/data/{tabla}` | Datos para llenar selects (fillSelect) |
| GET | `/api/search/{tabla}` | Autocompletado jQuery UI |
| GET | `/asset/js/{modulo}/{archivo}` | Servir JS de módulos |

Cada módulo con listado expone `POST /{modulo}/data` para DataTables server-side.

## Testing

Suite completa de **327 tests, 807 assertions, 0 fallos**, estructurada en 6 fases progresivas:

| Fase | Tests | Tipo | Cobertura |
|------|-------|------|-----------|
| 1 | 30 | Unitarios | Breadcrumb, Router, Controller |
| 2 | 15 | Modelos (BD real) | AlumnoModel, CursoModel |
| 3 | 12 | Integración (subproceso) | PreinscripcionLandingController |
| 4a | 15 | Unitarios (Core) | Auth, Database |
| 4b | 43 | Modelos CRUD | Banco, Capitulo, Coordinador, Duracion, etc. |
| 4c | 52 | Modelos con JOINs | Ciudad, Sede, Docente, CursoAbierto, etc. |
| 4d | 58 | Inscripciones + Ofertas | InscripcionCurso/Diplomado/Evento/Maestria, etc. |
| 4e | 53 | Lógica de negocio | Cuota, User, Dashboard, DiplomadoControl, etc. |
| 5 | 26 | Integración (DataTable) | 26 endpoints AJAX server-side |
| 6 | 23 | Integración (AJAX) | 20 endpoints no-DataTable |

### Ejecutar tests

```bash
composer test          # una sola ejecución
composer test:watch    # cada 2s automático al guardar cambios (Ctrl+C para salir)
```

### Base de datos de pruebas

Usa una base de datos independiente `php_mvc_app_test` con seed centralizado. Los tests de controladores se ejecutan en subprocesos via `shell_exec()` para manejar `exit()`.

### Seed de datos de prueba

```bash
mysql -u admin -p php_mvc_app_test < tests/seed_all.sql
```

### Convenciones

- Tests extienden `Tests\DatabaseTestCase` (modelos) o `Tests\ControllerIntegrationTestCase` (controladores).
- Tablas MyISAM (`inscripcion_curso`, `buzon`) no soportan transacciones — cada test crea y limpia sus registros manualmente en `tearDown()`.
- Seed usa `INSERT IGNORE` + `ON DUPLICATE KEY UPDATE` para idempotencia.

## Notas técnicas

- `ControllerIntegrationTestCase` usa `2>&1` en vez de `2>/dev/null` para capturar errores PHP en las respuestas.
- Varios controladores tenían `error_log()` de depuración que contaminaban respuestas JSON — fueron removidos.
- El singleton `Database` se resetea entre tests via reflection.
- `base64_encode()` para el campo `foto` en respuestas JSON (evita caracteres binarios no UTF-8).
- El controlador `CiudadController::getData()` verifica `REQUEST_METHOD === 'POST'` — los tests pasan el header en `$_SERVER`.

## Permisos (RBAC)

El sistema maneja permisos por grupos de usuarios:

- **Ventanas**: cada módulo tiene una key_word en la tabla ventana.
- **Permisos**: CRUD (crear, modificar, eliminar, listar) por grupo.
- **Evaluación**: `Auth::hasPermission("key_word", "listar")` en controladores y vistas.

## Requisitos

- PHP 8.2 o superior
- MySQL 8.0 o MariaDB 10+
- Composer
- Node.js 18+ y npm
- Apache con mod_rewrite (o Nginx con reglas equivalentes)
- Extensiones PHP: pdo_mysql, mbstring

## Instalación Local

### 1. Clonar

```bash
git clone <repo-url>
cd php_mvc_app
```

### 2. Configurar el servidor web

El document root debe apuntar a la carpeta `public/`.

- Apache: VirtualHost con `DocumentRoot /ruta/a/php_mvc_app/public` y `AllowOverride All`.
- Nginx: `root /ruta/a/php_mvc_app/public` y reescribir todo a `index.php?url=$uri`.

### 3. Crear la base de datos

Crear una base de datos MySQL vacía e importar el esquema SQL.

### 4. Variables de entorno

Crear `.env` en la raíz:

```ini
DB_HOST=localhost
DB_NAME=gestion_academica
DB_USER=root
DB_PASS=
```

### 5. Dependencias

```bash
composer install
npm install
```

### 6. Compilar Tailwind CSS

```bash
npm run dev       # Desarrollo (watch)
npm run build     # Producción (minificado)
```

### 7. Configurar BASE_URL

Editar `config/app.php`:

```php
define("BASE_URL", "http://localhost/php_mvc_app/public/");
// En producción: define("BASE_URL", "https://app.grupoavemer.net/");
```

### 8. Acceder

Abrir `http://localhost/php_mvc_app/public/` en el navegador.

## Despliegue en Producción

1. Document root apuntando a `public/`
2. `BASE_URL` configurado con el dominio real
3. Asegurar que mod_rewrite esté habilitado
4. Compilar Tailwind con `npm run build`
5. Los JS se sirven vía AssetController desde `App/Modules/` (no es necesario copiar nada)
6. Archivos `.env` y `vendor/` fuera del document root
