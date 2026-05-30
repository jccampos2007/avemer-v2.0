-- ============================================================================
-- MIGRACIÓN COMPLETA PARA PRODUCCIÓN
-- Refactorización módulo Cuota + costo/inicial en aperturas + pago cleanup
-- ============================================================================
-- Ejecutar ANTES de desplegar el código nuevo
-- ============================================================================

-- ============================================================================
-- 1. Agregar costo/inicial a tablas de apertura (idempotente)
-- ============================================================================
SET @db = DATABASE();

SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'curso_abierto' AND COLUMN_NAME = 'costo');
SET @sql = IF(@col = 0, 'ALTER TABLE curso_abierto ADD COLUMN costo float NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'curso_abierto' AND COLUMN_NAME = 'inicial');
SET @sql = IF(@col = 0, 'ALTER TABLE curso_abierto ADD COLUMN inicial float NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'diplomado_abierto' AND COLUMN_NAME = 'costo');
SET @sql = IF(@col = 0, 'ALTER TABLE diplomado_abierto ADD COLUMN costo float NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'diplomado_abierto' AND COLUMN_NAME = 'inicial');
SET @sql = IF(@col = 0, 'ALTER TABLE diplomado_abierto ADD COLUMN inicial float NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'maestria_abierto' AND COLUMN_NAME = 'costo');
SET @sql = IF(@col = 0, 'ALTER TABLE maestria_abierto ADD COLUMN costo float NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'maestria_abierto' AND COLUMN_NAME = 'inicial');
SET @sql = IF(@col = 0, 'ALTER TABLE maestria_abierto ADD COLUMN inicial float NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'evento_abierto' AND COLUMN_NAME = 'costo');
SET @sql = IF(@col = 0, 'ALTER TABLE evento_abierto ADD COLUMN costo float NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'evento_abierto' AND COLUMN_NAME = 'inicial');
SET @sql = IF(@col = 0, 'ALTER TABLE evento_abierto ADD COLUMN inicial float NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================================
-- 2. Truncar tablas dependientes (respaldar si es necesario)
-- ============================================================================
TRUNCATE TABLE cuota;
TRUNCATE TABLE transaccion;
TRUNCATE TABLE pago;

-- ============================================================================
-- 3. Cambios estructurales en cuota
-- ============================================================================
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'cuota' AND COLUMN_NAME = 'diplomado_curso_id');
SET @sql = IF(@col > 0, 'ALTER TABLE cuota CHANGE COLUMN diplomado_curso_id diplomado_control_id int DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'cuota' AND COLUMN_NAME = 'tipo_modalidad_id');
SET @sql = IF(@col > 0, 'ALTER TABLE cuota DROP COLUMN tipo_modalidad_id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================================
-- 4. Eliminar tabla obsoleta tipo_modalidad
-- ============================================================================
DROP TABLE IF EXISTS tipo_modalidad;

-- ============================================================================
-- 5. Eliminar columna generado de diplomado_control
-- ============================================================================
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'diplomado_control' AND COLUMN_NAME = 'generado');
SET @sql = IF(@col > 0, 'ALTER TABLE diplomado_control DROP COLUMN generado', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================================
-- 6. Limpieza de columnas redundantes en pago (pago_cuota es el único vínculo)
-- ============================================================================
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'pago' AND COLUMN_NAME = 'cuota_id');
SET @sql = IF(@col > 0, 'ALTER TABLE pago DROP COLUMN cuota_id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'pago' AND COLUMN_NAME = 'oferta_academica_id');
SET @sql = IF(@col > 0, 'ALTER TABLE pago DROP COLUMN oferta_academica_id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'pago' AND COLUMN_NAME = 'tipo_oferta_academica_id');
SET @sql = IF(@col > 0, 'ALTER TABLE pago DROP COLUMN tipo_oferta_academica_id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'pago' AND COLUMN_NAME = 'diplomado_control_id');
SET @sql = IF(@col > 0, 'ALTER TABLE pago DROP COLUMN diplomado_control_id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'pago' AND COLUMN_NAME = 'alumno_id');
SET @sql = IF(@col > 0, 'ALTER TABLE pago DROP COLUMN alumno_id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- transaccion.pago_id es nullable y sin código que lo use
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'transaccion' AND COLUMN_NAME = 'pago_id');
SET @sql = IF(@col > 0, 'ALTER TABLE transaccion DROP COLUMN pago_id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================================
-- VERIFICACIÓN
-- ============================================================================
SELECT '✅ cuota columnas:' AS estado;
SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'cuota'
ORDER BY ORDINAL_POSITION;

SELECT '✅ tipo_modalidad eliminada:' AS estado;
SELECT IF(COUNT(*) = 0, 'OK', 'EXISTE AÚN') AS resultado
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'tipo_modalidad';

SELECT '✅ generado eliminada de diplomado_control:' AS estado;
SELECT IF(COUNT(*) = 0, 'OK', 'EXISTE AÚN') AS resultado
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'diplomado_control' AND COLUMN_NAME = 'generado';

SELECT '✅ costo/inicial en aperturas:' AS estado;
SELECT 'curso_abierto', IF(COUNT(*) = 2, 'OK', 'FALTAN') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'curso_abierto' AND COLUMN_NAME IN ('costo','inicial')
UNION ALL
SELECT 'diplomado_abierto', IF(COUNT(*) = 2, 'OK', 'FALTAN') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'diplomado_abierto' AND COLUMN_NAME IN ('costo','inicial')
UNION ALL
SELECT 'maestria_abierto', IF(COUNT(*) = 2, 'OK', 'FALTAN') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'maestria_abierto' AND COLUMN_NAME IN ('costo','inicial')
UNION ALL
SELECT 'evento_abierto', IF(COUNT(*) = 2, 'OK', 'FALTAN') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'evento_abierto' AND COLUMN_NAME IN ('costo','inicial');

SELECT '✅ columnas eliminadas de pago:' AS estado;
SELECT 'ninguna columna redundante' AS resultado FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'pago' AND COLUMN_NAME IN ('cuota_id','oferta_academica_id','tipo_oferta_academica_id','diplomado_control_id','alumno_id')
UNION ALL
SELECT IF(COUNT(*) = 0, 'OK: ninguna columna redundante en pago', 'ALERTA') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'pago' AND COLUMN_NAME IN ('cuota_id','oferta_academica_id','tipo_oferta_academica_id','diplomado_control_id','alumno_id')
UNION ALL
SELECT IF(COUNT(*) = 1, 'OK: pago_cuota existe', 'FALTA: pago_cuota') FROM information_schema.TABLES WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'pago_cuota'
UNION ALL
SELECT IF(COUNT(*) = 0, 'OK: transaccion.pago_id eliminado', 'EXISTE AÚN') FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'transaccion' AND COLUMN_NAME = 'pago_id';
