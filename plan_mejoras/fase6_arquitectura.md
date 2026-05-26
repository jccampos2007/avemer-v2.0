# Fase 6 — Arquitectura 🏗️

## Objetivo
Mejorar la estructura del sistema para facilitar mantenimiento, testing y escalabilidad.

---

## 6.1 Agregar paginación a grupos y correos

### Archivos
- `App/Modules/Grupo/GrupoController.php` (líneas 38-61)
- `App/Modules/Grupo/GrupoModel.php` — agregar `getGroupsPaginated()`
- `App/Modules/Correo/CorreoModel.php` — agregar LIMIT a `getCursos()`, `getDiplomados()`, etc.

### Patrón a seguir
Los demás módulos (Alumnos, Docentes, Cursos) ya tienen paginación DataTables con `getPaginatedX()`. Aplicar el mismo patrón:

```php
public function getGroupsPaginated(array $params): array
{
    $sql = "SELECT g.*, u.usuario_nombre AS creador_nombre
            FROM {$this->table} g
            LEFT JOIN usuario u ON u.usuario_id = g.usuario_idreg
            WHERE 1=1";
    
    // Search
    if (!empty($params['search']['value'])) {
        $sql .= " AND (g.nombre_grupo LIKE :search)";
        $bind[':search'] = '%' . $params['search']['value'] . '%';
    }
    
    // Total count
    $countSql = str_replace("SELECT g.*, u.usuario_nombre", "SELECT COUNT(*)", $sql);
    $total = $this->pdo->prepare($countSql);
    $total->execute($bind ?? []);
    $recordsTotal = $total->fetchColumn();
    
    // Pagination
    $sql .= " LIMIT :start, :length";
    $bind[':start'] = (int)$params['start'];
    $bind[':length'] = (int)$params['length'];
    
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($bind);
    
    return [
        'draw' => $params['draw'],
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsTotal,
        'data' => $stmt->fetchAll(),
    ];
}
```

---

## 6.2 Agregar Repository Layer (opcional)

Para módulos con lógica de negocio compleja, separar en:
- **Controller**: request handling, validación, respuesta
- **Service**: lógica de negocio (ej: `PreinscripcionService`)
- **Repository/Model**: solo consultas SQL

### Estructura propuesta
```
App/Modules/PreinscripcionLanding/
├── PreinscripcionLandingController.php   # Solo maneja request/response
├── PreinscripcionService.php             # Lógica de negocio
├── PreinscripcionLandingModel.php        # Consultas SQL
└── Views/
    └── preinscripcion_landing.php
```

---

## 6.3 Agregar Middleware pipeline (opcional)

### Archivo nuevo
`App/Core/Middleware.php`

### Qué hacer
Implementar middleware para:
- `AuthMiddleware`: verificar sesión activa
- `CsrfMiddleware`: verificar token CSRF en POST
- `PermissionMiddleware`: verificar permiso específico
- `JsonMiddleware`: forzar Content-Type JSON

### Uso en router
```php
$router->add('GET', '/api/search/{table_name}', ApiController::class . '@getAutocompleteData')
       ->middleware([JsonMiddleware::class]);

$router->add('POST', '/users/create', UserController::class . '@store')
       ->middleware([AuthMiddleware::class, CsrfMiddleware::class]);
```

---

## 6.4 Estandarizar respuestas de API

### Crear helper
```php
class JsonResponse
{
    public static function success(array $data = [], string $message = 'OK'): void
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => $message, 'data' => $data]);
        exit;
    }

    public static function error(string $message, int $code = 400): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}
```

### Uso
```php
// En vez de:
echo json_encode(['success' => true, 'alumno' => $alumno]);
exit();

// Usar:
JsonResponse::success(['alumno' => $alumno], 'Alumno creado con éxito');
```

---

## 6.5 Dependency Injection básico

### Archivo nuevo
`App/Core/Container.php`

```php
class Container
{
    private array $instances = [];

    public function set(string $id, callable $factory): void
    {
        $this->instances[$id] = $factory;
    }

    public function get(string $id): mixed
    {
        if (!isset($this->instances[$id])) {
            throw new \InvalidArgumentException("No binding for {$id}");
        }
        return $this->instances[$id]($this);
    }
}
```

### Uso en test
```php
$container = new Container();
$container->set(PDO::class, fn() => $mockPdo);
$container->set(AlumnoModel::class, fn($c) => new AlumnoModel($c->get(PDO::class)));
$controller = new AlumnoController($container);
```
