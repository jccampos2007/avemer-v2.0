# avemer-mvc — MVC Architecture, Routing & Modules

## Directory Structure

```
/
├── config/app.php          # App config (DB, constants, autoloader)
├── public/index.php        # Entry point, router, route registration
├── public/js/main.js       # Global JS utilities
├── public/css/base.css     # Base button/action styles
├── public/css/input.css    # Tailwind + custom form classes
├── App/
│   ├── Core/
│   │   ├── Router.php       # Route matching & dispatch
│   │   ├── Controller.php   # Base controller (sanitize, CSRF, JSON)
│   │   ├── Database.php     # PDO singleton
│   │   └── Auth.php         # Login, session, CSRF
│   └── Modules/
│       ├── Alumnos/
│       ├── Cuota/
│       ├── DiplomadoControl/
│       └── ... (one folder per module)
└── .opencode/skills/        # Project skills
```

## Module Anatomy

Every module follows this 5-file structure:

```
App/Modules/{ModuleName}/
  {ModuleName}Controller.php   # Route handlers
  {ModuleName}Model.php        # DB queries
  {module_name}.js             # JS (DataTable, events, AJAX)
  Views/
    list.php                   # List page (DataTable HTML shell)
    form.php                   # Create/Edit form
```

**Naming conventions:**
| Element | Convention | Example |
|---------|-----------|---------|
| Module folder | PascalCase singular | `Alumnos` |
| Controller class | PascalCase + `Controller` | `AlumnoController` |
| Model class | PascalCase + `Model` | `AlumnoModel` |
| JS file | snake_case module name | `alumnos.js` |
| View files | lowercase | `list.php`, `form.php` |
| Controller methods | camelCase | `getAlumnosData()` |

**Namespace:** `App\Modules\{ModuleName}\{ModuleName}Controller`
**Autoloading:** PSR-4 with prefix `App\` mapped to `app/`

## Route Registration

All routes are registered in `/var/www/html/php_mvc_app/public/index.php`.

**Standard CRUD pattern:**
```php
$router->add('GET',  '/module',              Controller::class . '@index');
$router->add('GET',  '/module/create',       Controller::class . '@create');
$router->add('POST', '/module/create',       Controller::class . '@create');
$router->add('GET',  '/module/edit/{id}',    Controller::class . '@edit');
$router->add('POST', '/module/edit/{id}',    Controller::class . '@edit');
$router->add('GET',  '/module/delete/{id}',  Controller::class . '@delete');
$router->add('GET',  '/module/data',         Controller::class . '@getData');  // DataTables AJAX
$router->add('GET',  '/module/some-ajax',    Controller::class . '@someAjax');
$router->add('POST', '/module/some-action',  Controller::class . '@someAction');
```

**Key rules:**
- Import controller at top: `use App\Modules\ModuleName\ControllerName;`
- `{id}` in path is passed as `int $id` parameter to the controller method
- GET for page loads and data fetches; POST for mutations
- AJAX endpoints check `X-Requested-Width: XMLHttpRequest` header

## Controller Pattern

```php
namespace App\Modules\Alumnos;

use App\Core\Controller;
use App\Core\Auth;

class AlumnoController extends Controller
{
    private AlumnoModel $alumnoModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->alumnoModel = new AlumnoModel();
    }

    public function index(): void
    {
        $this->view('Alumnos/list');  // Renders Views/list.php
    }

    public function getAlumnosData(): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');
        echo json_encode(['data' => $this->alumnoModel->getAll()]);
        exit();
    }
}
```

**Helper methods from base Controller:**
- `$this->view(string $view, array $data = [])` — renders layout + view
- `$this->sanitizeInput(string $input): string`
- `$this->validateCsrf()` — checks CSRF token on POST
- `Auth::generateCsrfToken()`, `Auth::requireLogin()`

## AJAX Response Format

Always returned as JSON with `exit()`:

```php
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => '...', 'data' => [...]]);
exit();
```

## View Rendering

Controller calls `$this->view('ModuleName/viewName', ['key' => $val])`.  
The view file `App/Modules/ModuleName/Views/viewName.php` receives `$key`.

Views use layout defined in `App/Layout/` (header, sidebar, footer are automatic).
