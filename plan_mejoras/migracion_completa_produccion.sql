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
-- 6. Simplificar pago: cuota_id directo (permite abonos), dropear pago_cuota
-- ============================================================================
-- Re-agregar cuota_id (se dropeó en paso anterior del script, pero es idempotente)
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'pago' AND COLUMN_NAME = 'cuota_id');
SET @sql = IF(@col = 0, 'ALTER TABLE pago ADD COLUMN cuota_id int NOT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Dropear otras columnas redundantes (excepto cuota_id que sí usamos)
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

-- Dropear pago_cuota (ya no necesitamos pivote)
DROP TABLE IF EXISTS pago_cuota;

-- transaccion.pago_id es nullable y sin código que lo use
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'transaccion' AND COLUMN_NAME = 'pago_id');
SET @sql = IF(@col > 0, 'ALTER TABLE transaccion DROP COLUMN pago_id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================================
-- 7. Agregar formas de pago faltantes (Efectivo, Pago Movil, Dolares, Zelle)
-- ============================================================================
INSERT IGNORE INTO forma_pago (id, nombre) VALUES (4, 'Efectivo');
INSERT IGNORE INTO forma_pago (id, nombre) VALUES (5, 'Pago Movil');
INSERT IGNORE INTO forma_pago (id, nombre) VALUES (6, 'Dolares');
INSERT IGNORE INTO forma_pago (id, nombre) VALUES (7, 'Zelle');

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

SELECT '✅ pago columnas:' AS estado;
DESCRIBE pago;

SELECT '✅ pago_cuota eliminada:' AS estado;
SELECT IF(COUNT(*) = 0, 'OK', 'EXISTE AÚN') AS resultado
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'pago_cuota';

SELECT '✅ forma_pago:' AS estado;
SELECT * FROM forma_pago;

SELECT '✅ transaccion.pago_id eliminado:' AS estado;
SELECT IF(COUNT(*) = 0, 'OK', 'EXISTE AÚN') AS resultado
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'transaccion' AND COLUMN_NAME = 'pago_id';
