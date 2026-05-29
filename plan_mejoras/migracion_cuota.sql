-- ============================================================================
-- MIGRACIÓN: Refactorización del Módulo Cuota
-- ============================================================================
-- Ejecutar en: producción (grupoave_avemer) y test (php_mvc_app_test)
-- Orden: 1 primero, luego desplegar el código, luego 2
-- ============================================================================

-- ============================================================================
-- 1. TRUNCATE tables (elimina todos los registros existentes)
-- ============================================================================
TRUNCATE TABLE cuota;
TRUNCATE TABLE transaccion;
TRUNCATE TABLE pago;

-- ============================================================================
-- 2. Cambios estructurales en cuota
-- ============================================================================
-- Renombrar diplomado_curso_id → diplomado_control_id (nullable, solo para Diplomados)
ALTER TABLE cuota CHANGE COLUMN diplomado_curso_id diplomado_control_id int DEFAULT NULL;

-- Eliminar columna obsoleta tipo_modalidad_id
ALTER TABLE cuota DROP COLUMN tipo_modalidad_id;

-- ============================================================================
-- 3. Eliminar tabla tipo_modalidad
-- ============================================================================
DROP TABLE IF EXISTS tipo_modalidad;

-- ============================================================================
-- 4. Eliminar columna generado de diplomado_control (el concepto "configurado"
--    ahora se determina por la existencia de registros, no por una bandera)
-- ============================================================================
SET @generado_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'diplomado_control' AND COLUMN_NAME = 'generado');
SET @drop_sql := IF(@generado_exists > 0, 'ALTER TABLE diplomado_control DROP COLUMN generado', 'SELECT ''Column generado does not exist, skipping''');
PREPARE stmt FROM @drop_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- VERIFICACIÓN
-- ============================================================================
SELECT 'cuota' AS tabla, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cuota'
ORDER BY ORDINAL_POSITION;

SELECT 'tipo_modalidad existe?' AS check;
SELECT COUNT(*) AS existe FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tipo_modalidad';
