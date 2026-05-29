-- ============================================================================
-- MIGRACIÓN COMPLETA PARA PRODUCCIÓN
-- Refactorización módulo Cuota + costo/inicial en aperturas
-- ============================================================================
-- Ejecutar ANTES de desplegar el código nuevo
-- ============================================================================

-- ============================================================================
-- 1. Agregar costo/inicial a tablas de apertura (idempotente)
-- ============================================================================
ALTER TABLE curso_abierto ADD COLUMN IF NOT EXISTS costo float NOT NULL DEFAULT 0;
ALTER TABLE curso_abierto ADD COLUMN IF NOT EXISTS inicial float NOT NULL DEFAULT 0;

ALTER TABLE diplomado_abierto ADD COLUMN IF NOT EXISTS costo float NOT NULL DEFAULT 0;
ALTER TABLE diplomado_abierto ADD COLUMN IF NOT EXISTS inicial float NOT NULL DEFAULT 0;

ALTER TABLE maestria_abierto ADD COLUMN IF NOT EXISTS costo float NOT NULL DEFAULT 0;
ALTER TABLE maestria_abierto ADD COLUMN IF NOT EXISTS inicial float NOT NULL DEFAULT 0;

ALTER TABLE evento_abierto ADD COLUMN IF NOT EXISTS costo float NOT NULL DEFAULT 0;
ALTER TABLE evento_abierto ADD COLUMN IF NOT EXISTS inicial float NOT NULL DEFAULT 0;

-- ============================================================================
-- 2. Truncar tablas dependientes (respaldar si es necesario)
-- ============================================================================
TRUNCATE TABLE cuota;
TRUNCATE TABLE transaccion;
TRUNCATE TABLE pago;

-- ============================================================================
-- 3. Cambios estructurales en cuota
-- ============================================================================
ALTER TABLE cuota CHANGE COLUMN diplomado_curso_id diplomado_control_id int DEFAULT NULL;
ALTER TABLE cuota DROP COLUMN IF EXISTS tipo_modalidad_id;

-- ============================================================================
-- 4. Eliminar tabla obsoleta tipo_modalidad
-- ============================================================================
DROP TABLE IF EXISTS tipo_modalidad;

-- ============================================================================
-- 5. Eliminar columna generado de diplomado_control
-- ============================================================================
ALTER TABLE diplomado_control DROP COLUMN IF EXISTS generado;

-- ============================================================================
-- VERIFICACIÓN
-- ============================================================================
SELECT '✅ cuota columnas:' AS check;
SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cuota'
ORDER BY ORDINAL_POSITION;

SELECT '✅ tipo_modalidad eliminada:' AS check;
SELECT IF(COUNT(*) = 0, 'OK', 'EXISTE AÚN') AS resultado
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tipo_modalidad';

SELECT '✅ generado eliminada de diplomado_control:' AS check;
SELECT IF(COUNT(*) = 0, 'OK', 'EXISTE AÚN') AS resultado
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'diplomado_control' AND COLUMN_NAME = 'generado';

SELECT '✅ costo/inicial en aperturas:' AS check;
SELECT 'curso_abierto', IF(COUNT(*) = 2, 'OK', 'FALTAN') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'curso_abierto' AND COLUMN_NAME IN ('costo','inicial')
UNION ALL
SELECT 'diplomado_abierto', IF(COUNT(*) = 2, 'OK', 'FALTAN') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'diplomado_abierto' AND COLUMN_NAME IN ('costo','inicial')
UNION ALL
SELECT 'maestria_abierto', IF(COUNT(*) = 2, 'OK', 'FALTAN') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'maestria_abierto' AND COLUMN_NAME IN ('costo','inicial')
UNION ALL
SELECT 'evento_abierto', IF(COUNT(*) = 2, 'OK', 'FALTAN') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'evento_abierto' AND COLUMN_NAME IN ('costo','inicial');
