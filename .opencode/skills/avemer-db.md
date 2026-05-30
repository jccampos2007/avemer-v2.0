# avemer-db — Database Conventions

## General Rules

- **Engine:** InnoDB (all tables)
- **Charset:** `latin1` (legacy) or `utf8mb4`
- **PK:** Always `id INT AUTO_INCREMENT PRIMARY KEY`
- **Timestamps:** `created_at DATETIME DEFAULT CURRENT_TIMESTAMP`, `updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP`
- **Table names:** snake_case, **singular** (e.g., `alumno`, `cuota`, `forma_pago`)
- **Column names:** snake_case (e.g., `tipo_oferta_academica_id`, `fecha_vencimiento`)
- **FK columns:** `{referenced_table_singular}_id` (e.g., `alumno_id`, `cuota_id`)

## Key Tables & Relationships

```
alumno
  ├── inscripcion_curso       (alumno_id → alumno.id, curso_abierto_id → curso_abierto.id)
  ├── inscripcion_diplomado   (alumno_id → alumno.id, diplomado_abierto_id → diplomado_abierto.id)
  ├── inscripcion_maestria    (alumno_id → alumno.id, maestria_abierto_id → maestria_abierto.id)
  ├── inscripcion_evento      (alumno_id → alumno.id, evento_abierto_id → evento_abierto.id)
  ├── transaccion             (alumno_id → alumno.id)  ← debts/credits
  └── preinscripcion_*        (alumno_id → alumno.id)

cuota
  ├── oferta_academica_id     → {curso|diplomado|maestria|evento}_abierto.id
  ├── tipo_oferta_academica_id→ tipo_oferta_academica.id  (1=Curso,2=Diplomado,3=Evento,4=Maestria)
  └── diplomado_control_id    → diplomado_control.id (nullable, solo tipo=2)

pago
  ├── forma_pago_id           → forma_pago.id
  ├── banco_id                → banco.id
  └── cuota_id                → cuota.id  (permite duplicados = abonos)

transaccion
  ├── alumno_id               → alumno.id
  ├── cuota_id                → cuota.id
  ├── tipo (int)              → 1=Debito (deuda), 2=Credito (pago)
  └── estatus (int)           → 1=Activo/Pendiente
```

## tipo_oferta_academica Enum

| id | Nombre |
|----|--------|
| 1  | Curso/Taller |
| 2  | Diplomado |
| 3  | Evento |
| 4  | Maestría |

## forma_pago (Métodos de Pago)

| id | Nombre |
|----|--------|
| 1  | Cheque |
| 2  | Deposito |
| 3  | Transferencia |
| 4  | Efectivo |
| 5  | Pago Movil |
| 6  | Dolares |
| 7  | Zelle |

## Naming Patterns by Module

| Module | Table | Offer FK | Inscription Table |
|--------|-------|----------|-------------------|
| Curso | `curso_abierto` | `curso_abierto_id` | `inscripcion_curso` |
| Diplomado | `diplomado_abierto` | `diplomado_abierto_id` | `inscripcion_diplomado` |
| Maestría | `maestria_abierto` | `maestria_abierto_id` | `inscripcion_maestria` |
| Evento | `evento_abierto` | `evento_abierto_id` | `inscripcion_evento` |

All `*_abierto` tables have: `id, nombre, costo, inicial, created_at, updated_at`

## Migration Pattern

MySQL 8.4 does NOT support `ADD COLUMN IF NOT EXISTS`. Use the information_schema pattern:

```sql
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mi_tabla' AND COLUMN_NAME = 'mi_columna');
SET @sql = IF(@col = 0, 'ALTER TABLE mi_tabla ADD COLUMN mi_columna tipo DEFAULT valor', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
```

`DROP COLUMN IF EXISTS` IS supported directly:
```sql
ALTER TABLE mi_tabla DROP COLUMN IF EXISTS mi_columna;
```

Migration scripts go in `/var/www/html/php_mvc_app/plan_mejoras/`.

## Column Conventions

| Pattern | Example | Notes |
|---------|---------|-------|
| IDs | `id` | PK auto_increment |
| FK | `alumno_id`, `cuota_id` | Always `{table}_id` |
| Status | `estatus` | int (1=active) |
| Dates | `fecha` | date/datetime |
| Amounts | `monto` | float/double |
| Type discriminators | `tipo` | int enum |
| Timestamps | `created_at`, `updated_at` | Standard across all tables |
