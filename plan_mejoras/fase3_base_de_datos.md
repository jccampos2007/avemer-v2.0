# Fase 3 — Base de Datos 🗄️

## Objetivo
Completar la integridad referencial con FK constraints faltantes, optimizar performance con índices, y mover BLOBs a disco.

---

## 3.1 Agregar FK constraints faltantes

Basado en los JOINs que hacen los modelos, estas relaciones carecen de FK:

| Tabla | Columna | Referencia |
|---|---|---|
| `alumno` | `profesion_oficio_id` | `profesion_oficio.id` |
| `alumno` | `estado_id` | `estado.id` |
| `alumno` | `nacionalidad_id` | `nacionalidad.id` |
| `usuario` | `grupo_id` | `grupo.grupo_id` |
| `curso_abierto` | `curso_id` | `curso.id` |
| `curso_abierto` | `sede_id` | `sede.id` |
| `curso_abierto` | `docente_id` | `docente.id` |
| `diplomado_abierto` | `diplomado_id` | `diplomado.id` |
| `diplomado_abierto` | `sede_id` | `sede.id` |
| `evento_abierto` | `evento_id` | `evento.id` |
| `evento_abierto` | `sede_id` | `sede.id` |
| `evento_abierto` | `docente_id` | `docente.id` |
| `maestria_abierto` | `maestria_id` | `maestria.id` |
| `maestria_abierto` | `sede_id` | `sede.id` |
| `maestria_abierto` | `docente_id` | `docente.id` |
| `inscripcion_diplomado` | `diplomado_abierto_id` | `diplomado_abierto.id` |
| `inscripcion_diplomado` | `alumno_id` | `alumno.id` |
| `inscripcion_evento` | `evento_abierto_id` | `evento_abierto.id` |
| `inscripcion_evento` | `alumno_id` | `alumno.id` |
| `inscripcion_maestria` | `maestria_abierto_id` | `maestria_abierto.id` |
| `inscripcion_maestria` | `alumno_id` | `alumno.id` |
| `asistencia_detalle` | `alumno_id` | `alumno.id` |
| `convenio_detalle` | `alumno_id` | `alumno.id` |
| `pago` | `alumno_id` | `alumno.id` |
| `transaccion` | `alumno_id` | `alumno.id` |

### Query genérica
```sql
ALTER TABLE <tabla> ADD CONSTRAINT fk_<tabla>_<columna>
FOREIGN KEY (<columna>) REFERENCES <ref_tabla>(<ref_columna>)
ON DELETE RESTRICT;
```

### Precaución
- Antes de cada FK, verificar huérfanos:
  ```sql
  SELECT COUNT(*) FROM <tabla> t
  LEFT JOIN <ref_tabla> r ON r.<ref_columna> = t.<columna>
  WHERE r.<ref_columna> IS NULL;
  ```
- Aplicar en test DB primero, luego en prod

---

## 3.2 Agregar índices

### Columnas prioritarias
| Tabla | Columna | Tipo de índice | Razón |
|---|---|---|---|
| `alumno` | `ci_pasapote` | INDEX | Búsqueda LIKE frecuente |
| `alumno` | `estatus_activo_id` | INDEX | Filtro por estatus |
| `usuario` | `grupo_id` | INDEX | JOIN con grupo en sidebar |
| `inscripcion_curso` | `alumno_id` | INDEX | JOIN + WHERE |
| `inscripcion_diplomado` | `alumno_id` | INDEX | JOIN + WHERE |
| `inscripcion_evento` | `alumno_id` | INDEX | JOIN + WHERE |
| `inscripcion_maestria` | `alumno_id` | INDEX | JOIN + WHERE |
| `curso_abierto` | `curso_id` | INDEX | JOIN |
| `diplomado_abierto` | `diplomado_id` | INDEX | JOIN |
| `evento_abierto` | `evento_id` | INDEX | JOIN |
| `maestria_abierto` | `maestria_id` | INDEX | JOIN |
| Todas las `*_abierto` | `estatus_id` | INDEX | Filtro por estatus |

### Query
```sql
ALTER TABLE <tabla> ADD INDEX idx_<columna> (<columna>);
```

---

## 3.3 Migrar BLOBs de fotos a disco

### Archivos afectados
- `alumno.foto` (BLOB)
- `alumno.imagen` (BLOB)
- `docente.foto` (BLOB)
- `docente.imagen` (BLOB)

### Qué hacer
1. Crear directorio `public/uploads/fotos/`
2. Agregar columna `foto_path` VARCHAR(255) NULL a `alumno` y `docente`
3. Script de migración: leer cada BLOB, escribirlo a disco, guardar la ruta:
   ```php
   $rows = $pdo->query("SELECT id, foto FROM alumno WHERE foto IS NOT NULL");
   foreach ($rows as $row) {
       $path = "public/uploads/fotos/alumno_{$row['id']}.jpg";
       file_put_contents($path, $row['foto']);
       $pdo->prepare("UPDATE alumno SET foto_path = ? WHERE id = ?")
          ->execute([$path, $row['id']]);
   }
   ```
4. Actualizar modelos para leer/escribir `foto_path` en vez de BLOB
5. Opcional: eliminar columna `foto` BLOB después de migración exitosa

### Validación
- Foto se muestra correctamente desde la ruta en disco
- Backup de BD es más pequeño
- Las queries `SELECT *` ya no cargan BLOBs pesados
