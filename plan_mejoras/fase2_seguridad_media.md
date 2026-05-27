# Fase 2 — Seguridad Media 🟡

## Objetivo
Corregir vulnerabilidades de severidad media y bugs de calidad que pueden derivar en problemas de seguridad.

---

## 2.1 Dejar de exponer errores de BD al usuario

### Archivos afectados (~15 controladores)
- `App/Modules/Users/UserController.php`
- `App/Modules/Alumnos/AlumnoController.php`
- `App/Modules/Cursos/CursoController.php`
- `App/Modules/Correo/CorreoController.php`
- `App/Modules/PreinscripcionDiplomado/PreinscripcionDiplomadoController.php`
- `App/Modules/Docentes/DocenteController.php`
- `App/Modules/Coordinadores/CoordinadorController.php`
- Y otros similares

### Qué hacer
Reemplazar:
```php
catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
}
```
Por:
```php
catch (PDOException $e) {
    error_log("Error en [Controller]: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
}
```

### Validación
- Forzar un error de BD (ej: drop temporal de columna) → mensaje genérico al usuario
- `error_log` contiene el detalle real

---

## 2.2 Validación de subida de archivos

### Archivos
- `App/Modules/Alumnos/AlumnoController.php` (líneas 145-150)
- `App/Modules/Docentes/DocenteController.php` (líneas 162-167)
- `App/Modules/Coordinadores/CoordinadorController.php` (líneas 127-132)

### Qué hacer
Agregar antes de `file_get_contents()`:
```php
$allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 2 * 1024 * 1024; // 2MB

if ($_FILES['foto']['size'] > $maxSize) {
    // error: archivo muy grande
}
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($_FILES['foto']['tmp_name']);
if (!in_array($mime, $allowedMimes)) {
    // error: tipo no permitido
}
```

### Validación
- Subir `.jpg`/`.png` → ok
- Subir `.svg` con JS → rechazado
- Subir archivo > 2MB → rechazado
- Subir `.php` → rechazado

---

## 2.3 Arreglar autoloader

### Archivo
`config/app.php` línea 41

### Qué hacer
```php
// ANTES
$base_dir = APP_ROOT . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR;

// DESPUÉS
$base_dir = APP_ROOT . DIRECTORY_SEPARATOR . 'App' . DIRECTORY_SEPARATOR;
```

O mejor: eliminar el autoloader custom y dejar solo el de Composer:
```php
// Eliminar o comentar las líneas del autoloader manual
// $base_dir = APP_ROOT . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR;
// ...
```

### Validación
- La app carga sin errores de clase no encontrada
- `php -r "require 'vendor/autoload.php';"` funciona

---

## 2.4 Corregir variable indefinida en CorreoController

### Archivo
`App/Modules/Correo/CorreoController.php` línea 63

### Qué hacer
```php
// ANTES
if (empty($data['nombre']) || empty($data['monto']) ...

// DESPUÉS - $data['nombre'] y $data['monto'] NUNCA se setean arriba
// O se agregan al array $data, o se cambia la validación
$data['nombre'] = $this->sanitizeInput($_POST['nombre'] ?? '');
$data['monto'] = $this->sanitizeInput($_POST['monto'] ?? '');
```

### Validación
- Formulario de crear correo sin errores PHP
- `empty($data['nombre'])` ya no genera warning

---

## 2.5 Fix double-encoding en sanitizeInput

### Archivo
`App/Core/Controller.php` — método `sanitizeInput()`

### Qué hacer
El orden actual es: `trim()` → `strip_tags()` → `htmlspecialchars()`.

El problema: si el input ya contiene entidades HTML como `&amp;`, `strip_tags()` las deja intactas, y luego `htmlspecialchars()` las vuelve a escapar → `&amp;amp;`.

Solución: invertir el orden o agregar `ENT_QUOTES | ENT_HTML5`:
```php
// Mantener el orden actual pero documentar que NO es para datos que deban conservar HTML
// Para DataTables output, usar htmlspecialchars() directamente sin strip_tags
```

### Validación
- Input `Click & Win` → output `Click &amp; Win` (una sola escapada)
- Input `<b>texto</b>` → output `texto` (tags eliminados)

---

## 2.6 IDOR — Validar permisos sobre registros específicos

### Archivos
- `App/Modules/Users/UserController.php` (edit, update, delete)
- `App/Modules/Grupo/GrupoController.php` (delete)

### Qué hacer
Agregar verificación de que el usuario autenticado tiene permiso para modificar/eliminar el registro específico. Ejemplo:
```php
public function delete(int $id): void
{
    $user = $this->userModel->findById($id);
    if (!$user) {
        $this->redirect('users');
    }
    // No permitir auto-eliminación
    if ((int)$id === (int)$_SESSION['user_id']) {
        // error: no puedes eliminarte a ti mismo
    }
    // ... continuar
}
```

### Validación
- Usuario sin permisos no puede editar/eliminar registros de otros
- No puedes eliminarte a ti mismo
