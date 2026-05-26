# Fase 5 — Calidad de Código 🧹

## Objetivo
Reducir duplicación, eliminar dead code, refactorizar métodos largos y mejorar mantenibilidad.

---

## 5.1 Refactorizar DataTables params a helper

### Problema
~15 controladores tienen este bloque duplicado (~20 líneas c/u):
```php
$draw = $_GET['draw'] ?? 1;
$start = $_GET['start'] ?? 0;
$length = $_GET['length'] ?? 10;
$searchValue = $_GET['search']['value'] ?? '';
$orderColumnIndex = $_GET['order'][0]['column'] ?? 0;
$orderDir = $_GET['order'][0]['dir'] ?? 'asc';
// ... etc
```

### Solución
Crear `App/Core/DataTableHelper.php`:
```php
<?php
namespace App\Core;

class DataTableHelper
{
    public static function parseParams(): array
    {
        return [
            'draw' => (int)($_GET['draw'] ?? 1),
            'start' => (int)($_GET['start'] ?? 0),
            'length' => (int)($_GET['length'] ?? 10),
            'search' => ['value' => $_GET['search']['value'] ?? ''],
            'order' => [
                'column' => (int)($_GET['order'][0]['column'] ?? 0),
                'dir' => strtoupper($_GET['order'][0]['dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC'
            ],
        ];
    }
}
```

Uso en controladores:
```php
$params = DataTableHelper::parseParams();
$result = $this->model->getPaginated($params);
echo json_encode($result);
```

---

## 5.2 Refactorizar processPreinscripcion()

### Archivo
`App/Modules/PreinscripcionLanding/PreinscripcionLandingController.php` (líneas 153-353)

### Qué hacer
Separar en métodos privados:
```php
private function validatePreinscripcionData(): array
{
    // Validar $_POST, devolver datos o lanzar excepción
}

private function buildEmailTemplate(array $data): string
{
    // Generar HTML del email
}

private function sendConfirmationEmail(string $to, string $template): void
{
    // Enviar correo
}
```

Estructura final:
```php
public function processPreinscripcion(): void
{
    try {
        $data = $this->validatePreinscripcionData();
        $enrollmentId = $this->createEnrollment($data);
        $template = $this->buildEmailTemplate($data);
        $this->sendConfirmationEmail($data['correo'], $template);
        echo json_encode(['success' => true, 'id' => $enrollmentId]);
    } catch (\Exception $e) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al procesar preinscripción.']);
    }
}
```

---

## 5.3 Refactorizar getAutocompleteData()

### Archivo
`App/Api/ApiController.php` (líneas 82-240)

### Qué hacer
Separar en:
```php
private function validateTableConfig(string $tableName): ?array
{
    // Validar que la tabla existe en configuración
}

private function validateDisplayColumn(array $config, string $column): string
{
    // Validar columna de visualización
}

private function buildSearchConditions(array $searchColumns, string $term): array
{
    // Construir WHERE clauses + params
}

private function applyStatusFilter(array $config, ?string $status): array
{
    // Aplicar filtro de estatus
}
```

---

## 5.4 Dashboard queries: 4 → 1 (eliminar N+1)

### Archivo
`App/Modules/Dashboard/DashboardModel.php` (líneas 16-70 y 72-167)

### Qué hacer
Reemplazar 4 queries separadas por 1 UNION:
```php
public function getInscripcionesStats(): array
{
    $sql = "SELECT 'curso' AS tipo, COUNT(*) AS total FROM inscripcion_curso
            UNION ALL SELECT 'diplomado', COUNT(*) FROM inscripcion_diplomado
            UNION ALL SELECT 'evento', COUNT(*) FROM inscripcion_evento
            UNION ALL SELECT 'maestria', COUNT(*) FROM inscripcion_maestria";
    $rows = $this->pdo->query($sql)->fetchAll(PDO::FETCH_KEY_PAIR);
    return $rows;
}
```

---

## 5.5 Eliminar dead code y commented-out credentials

### Archivos a limpiar
- `App/Modules/Correo/enviar.php` (líneas 20-22): comentarios con credenciales Gmail antiguas
- `App/Modules/Correo/enviar.php` (línea 27): BCC comentado
- `App/Modules/Correo/CorreoController.php` (líneas 25-28): comentario innecesario
- `.env` (líneas 1-4): credenciales comentadas del remoto
- Cualquier otro bloque `// ... código muerto` encontrado

### Buscar con
```bash
grep -rn "//.*@" /var/www/html/php_mvc_app/App/ | grep -v ".md"
```

---

## 5.6 Eliminar empty catch blocks

### Archivo
`App/Modules/Dashboard/DashboardModel.php`

### Qué hacer
```php
// ANTES
catch (\Exception $e) {}

// DESPUÉS
catch (\Exception $e) {
    error_log("DashboardModel error: " . $e->getMessage());
    // Opcional: relanzar o retornar valor por defecto
}
```

---

## 5.7 Agregar base model class

### Archivo nuevo
`App/Core/BaseModel.php`

### Qué hacer
```php
<?php
namespace App\Core;

abstract class BaseModel
{
    protected \PDO $pdo;
    protected string $table;

    public function __construct(\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getInstance()->getConnection();
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getAll(): array
    {
        return $this->pdo->query("SELECT * FROM {$this->table}")->fetchAll();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    abstract public function create(array $data): bool;
    abstract public function update(int $id, array $data): bool;
}
```

Los modelos existentes pueden extender esta clase y solo implementar `create()` y `update()`.
