# Fase 4 — Tests 🧪

## Objetivo
Hacer los tests repeatables (sin contaminación entre ejecuciones) y cubrir las áreas críticas no testeadas.

---

## 4.1 Envolver DatabaseTestCase en transacciones

### Archivo
`tests/DatabaseTestCase.php`

### Qué hacer
```php
abstract class DatabaseTestCase extends TestCase
{
    protected ?PDO $pdo = null;

    protected function setUp(): void
    {
        $refl = new \ReflectionClass(Database::class);
        $prop = $refl->getProperty('instance');
        $prop->setValue(null);

        $this->pdo = Database::getInstance()->getConnection();
        $this->pdo->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->pdo !== null && $this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
        parent::tearDown();
    }

    protected function getConnection(): PDO
    {
        return $this->pdo ?? Database::getInstance()->getConnection();
    }
}
```

### Impacto en tests existentes
- Tests que asumen que `insertTestRecord()` persiste cambios entre tests → **deben actualizarse**
- Tests que dependen de que otro test haya insertado datos → **deben arreglarse**
- Cada test arranca desde el seed limpio

### Validación
- Ejecutar suite completa 2 veces seguidas → mismos resultados
- Sin acumulación de filas en `inscripcion_evento`/`inscripcion_maestria`
- `test_countInscritos` siempre encuentra 1 fila

---

## 4.2 Agregar tests para Auth (login/logout)

### Archivo nuevo
`tests/AuthIntegrationTest.php`

### Tests a cubrir
```php
public function test_login_with_valid_credentials_sets_session(): void
{
    // POST a /login con credenciales válidas
    // Assert: $_SESSION['user_id'] está seteado
    // Assert: redirección ocurre
}

public function test_login_with_invalid_credentials_returns_error(): void
{
    // POST a /login con credenciales inválidas
    // Assert: mensaje de error
    // Assert: $_SESSION['user_id'] NO está seteado
}

public function test_logout_destroys_session(): void
{
    // Login → Logout
    // Assert: $_SESSION['user_id'] ya no existe
}

public function test_session_regenerate_after_login(): void
{
    // Obtener session_id antes y después de login
    // Assert: son diferentes
}
```

---

## 4.3 Agregar tests para formularios CRUD

### Patrón para cada módulo (Alumnos, Docentes, Coordinadores, Cursos, etc.)

```php
public function test_store_creates_record(): void
{
    // POST a /{module}/create con datos válidos
    // Assert: registro creado en BD
    // Assert: redirección o JSON success
}

public function test_store_with_empty_required_fields_returns_error(): void
{
    // POST a /{module}/create con campos vacíos
    // Assert: mensaje de error
    // Assert: registro NO creado
}

public function test_update_modifies_record(): void
{
    // POST a /{module}/edit/{id} con datos modificados
    // Assert: BD refleja los cambios
}

public function test_delete_removes_record(): void
{
    // POST a /{module}/delete/{id}
    // Assert: registro eliminado (o soft-delete)
}
```

---

## 4.4 Agregar tests para PreinscripcionLandingController

### Archivo nuevo
`tests/PreinscripcionLandingFlowTest.php`

### Tests
```php
public function test_createAlumno_creates_student(): void
{
    // POST con datos de alumno nuevo
    // Assert: alumno creado con estatus_activo_id = 1
    // Assert: JSON success
}

public function test_createAlumno_duplicate_ci_returns_error(): void
{
    // POST con CI ya existente
    // Assert: JSON error, "Ya existe"
}

public function test_processPreinscripcion_creates_enrollment(): void
{
    // POST con alumno existente + oferta académica
    // Assert: inscripcion_{tipo} creada
    // Assert: JSON success con comprobante
}

public function test_processPreinscripcion_missing_data_returns_error(): void
{
    // POST sin datos obligatorios
    // Assert: JSON error
}
```

---

## 4.5 Agregar tests para upload de fotos

```php
public function test_store_with_valid_photo_saves_file(): void
{
    // Simular $_FILES con imagen PNG válida (1x1 px)
    // Assert: foto guardada (path o BLOB)
}

public function test_store_with_invalid_photo_type_rejected(): void
{
    // Simular $_FILES con archivo .txt renombrado
    // Assert: rechazado, mensaje de error
}

public function test_store_with_oversized_photo_rejected(): void
{
    // Simular $_FILES con archivo > tamaño máximo
    // Assert: rechazado
}
```

---

## 4.6 Agregar tests para CSRF

```php
public function test_post_without_csrf_token_rejected(): void
{
    // POST sin token CSRF
    // Assert: 403 o redirect con error
}

public function test_post_with_invalid_csrf_token_rejected(): void
{
    // POST con token inválido
    // Assert: 403 o redirect con error
}

public function test_post_with_valid_csrf_token_succeeds(): void
{
    // Obtener token, POST con token válido
    // Assert: operación exitosa
}
```
