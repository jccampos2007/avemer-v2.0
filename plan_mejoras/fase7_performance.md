# Fase 7 — Performance ⚡

## Objetivo
Optimizar tiempos de respuesta y reducir carga en BD y memoria.

---

## 7.1 Cache de dashboard stats

### Archivo
`App/Modules/Dashboard/DashboardModel.php`

### Qué hacer
Cachear resultados de estadísticas en archivo temporal por 5 minutos:

```php
public function getInscripcionesStats(): array
{
    $cacheFile = sys_get_temp_dir() . '/dashboard_stats_' . md5(__METHOD__) . '.cache';
    $ttl = 300; // 5 minutos

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
        return unserialize(file_get_contents($cacheFile));
    }

    $data = $this->computeInscripcionesStats();

    file_put_contents($cacheFile, serialize($data));
    return $data;
}
```

### Alternativa simple
Si el cache en archivo es demasiado complejo, agregar un flag `$forceRefresh`:
```php
public function getInscripcionesStats(bool $forceRefresh = false): array
{
    static $cache = null;
    if ($cache === null || $forceRefresh) {
        // compute
    }
    return $cache;
}
```
Esto al menos evita recalcular en la misma request.

---

## 7.2 Revisar SELECT * innecesarios

### Archivos
- `App/Modules/Users/UserModel.php` línea 19: `SELECT * FROM usuario`
- `App/Modules/Grupo/GrupoModel.php`: varios `SELECT *`
- Cualquier modelo con `getAll()` que haga `SELECT *`

### Qué hacer
Reemplazar `SELECT *` por solo las columnas necesarias:
```sql
-- ANTES
SELECT * FROM usuario ORDER BY usuario_id DESC

-- DESPUÉS
SELECT usuario_id, usuario_cedula, usuario_nombre, usuario_apellido,
       usuario_user, estatus_activo_id, grupo_id
FROM usuario
ORDER BY usuario_id DESC
```

**Importante**: excluir `usuario_pws` (password hash) de los SELECT que no lo necesiten.

---

## 7.3 Optimizar queries del dashboard

### Archivo
`App/Modules/Dashboard/DashboardModel.php`

### Qué hacer: 4 queries → 1 con CASE
```php
public function getInscripcionesStats(): array
{
    $sql = "SELECT
                SUM(CASE WHEN tipo = 'curso' THEN 1 ELSE 0 END) AS curso,
                SUM(CASE WHEN tipo = 'diplomado' THEN 1 ELSE 0 END) AS diplomado,
                SUM(CASE WHEN tipo = 'evento' THEN 1 ELSE 0 END) AS evento,
                SUM(CASE WHEN tipo = 'maestria' THEN 1 ELSE 0 END) AS maestria
            FROM (
                SELECT 'curso' AS tipo FROM inscripcion_curso
                UNION ALL SELECT 'diplomado' FROM inscripcion_diplomado
                UNION ALL SELECT 'evento' FROM inscripcion_evento
                UNION ALL SELECT 'maestria' FROM inscripcion_maestria
            ) t";
    return $this->pdo->query($sql)->fetch();
}
```

---

## 7.4 Agregar LIMIT a queries sin paginación

### Archivos
- `App/Modules/Correo/CorreoModel.php`: `getCursos()`, `getDiplomados()`, `getEventos()`, `getMaestrias()`
- `App/Modules/Cuota/CuotaModel.php`: métodos similares

### Qué hacer
```php
// ANTES
$sql = "SELECT ca.id, ca.numero, c.nombre FROM curso_abierto ca JOIN curso c ...";

// DESPUÉS
$sql = "SELECT ca.id, ca.numero, c.nombre FROM curso_abierto ca JOIN curso c ... LIMIT 100";
```

Si se necesita paginación completa, implementar `getPaginated()` como en los demás módulos.

---

## 7.5 Índices compuestos

### Columnas de alta frecuencia
```sql
-- Búsqueda de alumnos por CI + estatus (autocomplete)
ALTER TABLE alumno ADD INDEX idx_ci_estatus (ci_pasapote, estatus_activo_id);

-- Búsqueda de inscripciones por evento + alumno
ALTER TABLE inscripcion_evento ADD INDEX idx_evento_alumno (evento_abierto_id, alumno_id);

-- Búsqueda de inscripciones por curso + alumno  
ALTER TABLE inscripcion_curso ADD INDEX idx_curso_alumno (curso_abierto_id, alumno_id);
```

---

## 7.6 Conexión persistente (opcional)

### Archivo
`App/Core/Database.php`

### Qué hacer
```php
$this->pdo = new \PDO(
    "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
    $user,
    $pass,
    [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
        \PDO::ATTR_PERSISTENT => true, // <-- conexión persistente
    ]
);
```

**Precaución**: Las conexiones persistentes pueden causar problemas en entornos con transacciones largas o con estado de sesión. Probar en staging primero.

---

## 7.7 Benchmarking

### Script de prueba
```bash
#!/bin/bash
# benchmark.sh
ENDPOINTS=(
    "/api/search/alumno?term=test"
    "/dashboard"
    "/api/search/diplomado_abierto?term=DA"
)

for url in "${ENDPOINTS[@]}"; do
    echo "=== $url ==="
    ab -n 100 -c 10 "http://localhost/php_mvc_app/public$url" 2>&1 | grep -E "Requests per second|Time per request|Failed requests"
done
```

### Métricas objetivo
- Requests per second: > 50 (con cache) / > 10 (sin cache)
- Time per request: < 200ms (p95)
- Failed requests: 0%
