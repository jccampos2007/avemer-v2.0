# Fase 1 — Seguridad Crítica 🔴

## Objetivo
Eliminar vulnerabilidades críticas que comprometen datos sensibles y permiten ejecución de acciones no autorizadas.

---

## 1.1 Mover credenciales a variables de entorno

### Archivos afectados
- `.env`
- `App/Modules/Correo/enviar.php`
- `tests/bootstrap.php`

### Qué hacer
1. Eliminar credenciales hardcodeadas de `.env` (tanto las comentadas como las activas)
2. Usar `getenv()` o `$_ENV` para leer desde variables de entorno del servidor
3. En `enviar.php`, leer credenciales SMTP desde entorno
4. En `tests/bootstrap.php`, leer credenciales test DB desde entorno

### Validación
- Verificar que la app funciona sin `.env` cuando las vars están en entorno del sistema
- `grep -r "Admin\.2026" /var/www/html/php_mvc_app/` → 0 resultados

---

## 1.2 Eliminar BCC a Gmail personal

### Archivo
`App/Modules/Correo/enviar.php` línea 28

### Qué hacer
```php
// ANTES
$mail->AddBCC('ingdiazjc@gmail.com', 'Bcc jc');

// DESPUÉS
// Eliminar esta línea
```

### Validación
- Verificar que `enviar.php` ya no contiene `ingdiazjc@gmail.com`
- Asegurar que el envío de correos sigue funcionando

---

## 1.3 Agregar CSRF tokens

### Archivos afectados
- `App/Core/Auth.php` — agregar método `generateCsrfToken()` y `validateCsrfToken()`
- Todos los controladores con métodos POST (store, update, delete)
- Todos los formularios en vistas

### Qué hacer
1. En `Auth.php`:
   ```php
   public static function generateCsrfToken(): string
   {
       if (empty($_SESSION['csrf_token'])) {
           $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
       }
       return $_SESSION['csrf_token'];
   }

   public static function validateCsrfToken(string $token): bool
   {
       return hash_equals($_SESSION['csrf_token'] ?? '', $token);
   }
   ```

2. En cada `layout` header o formulario, incluir:
   ```php
   <input type="hidden" name="csrf_token" value="<?= Auth::generateCsrfToken() ?>">
   ```

3. En cada controller POST action, al inicio:
   ```php
   if (!Auth::validateCsrfToken($_POST['csrf_token'] ?? '')) {
       // redirect con error o JSON 403
   }
   ```

### Validación
- Intentar POST sin token → 403
- Intentar POST con token inválido → 403
- POST con token válido → funciona normalmente

---

## 1.4 Validar statusFilter en ApiController

### Archivo
`App/Api/ApiController.php` línea 26

### Qué hacer
Reemplazar interpolación directa por validación contra allowlist:

```php
// ANTES
$where = $requestedStatusFilterColumn ? "WHERE {$_GET['statusFilter']} = '1'" : '';

// DESPUÉS
$allowedStatusColumns = ['estatus_id', 'estatus_activo_id'];
$statusFilterCol = $_GET['statusFilter'] ?? '';
$where = in_array($statusFilterCol, $allowedStatusColumns)
    ? "WHERE {$statusFilterCol} = '1'"
    : '';
```

### Validación
- `?statusFilter=estatus_id` → filtra correctamente
- `?statusFilter=1;DROP TABLE users` → ignora (no está en allowlist)

---

## 1.5 XSS en sidebar

### Archivo
`App/Layout/sidebar.php` líneas 370-371

### Qué hacer
```php
// ANTES
<?php echo Auth::user('user_name'); ?>
<?php echo Auth::user('nombre_grupo'); ?>

// DESPUÉS
<?php echo htmlspecialchars(Auth::user('user_name') ?? '', ENT_QUOTES, 'UTF-8'); ?>
<?php echo htmlspecialchars(Auth::user('nombre_grupo') ?? '', ENT_QUOTES, 'UTF-8'); ?>
```

### Validación
- Sidebar se ve igual con usuarios normales
- Usuario con nombre `<script>alert(1)</script>` → se muestra escapado, no se ejecuta

---

## 1.6 Session security

### Archivo
- `config/app.php` (session config)
- `App/Core/Auth.php::login()` (regenerate_id)

### Qué hacer

En `config/app.php` ANTES de `session_start()`:
```php
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
if (!empty($_SERVER['HTTPS'])) {
    ini_set('session.cookie_secure', 1);
}
session_start();
```

En `Auth.php::login()`:
```php
public static function login(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['usuario_id'];
    // ... resto
}
```

### Validación
- Login funciona normalmente
- `session_regenerate_id()` se ejecuta en cada login
- Cookie de sesión tiene flags httponly, samesite
