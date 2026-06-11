-- ============================================================================
-- MIGRACIÓN DE ESTRUCTURA: LOCAL → PRODUCCIÓN
-- Columnas, defaults, índices y constraints que existen en local
-- y faltan en la base de datos de producción (grupoave_avemer)
-- ============================================================================
-- Ejecutar con: mysql -h mysql.grupoavemer.com.ve -u udbgravseg -p'Db.Em81w3' grupoave_avemer < migracion_estructura_local_a_prod.sql
-- ============================================================================

SET @db = DATABASE();

-- ============================================================================
-- 1. alumno_auth: agregar reset_token_expires_at
-- ============================================================================
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'alumno_auth' AND COLUMN_NAME = 'reset_token_expires_at');
SET @sql = IF(@col = 0,
  'ALTER TABLE alumno_auth ADD COLUMN `reset_token_expires_at` datetime DEFAULT NULL AFTER `verification_token`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================================
-- 2. alumno_auth: agregar FOREIGN KEY (alumno_id → alumno.id)
-- ============================================================================
SET @fk = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'alumno_auth' AND CONSTRAINT_TYPE = 'FOREIGN KEY');
SET @sql = IF(@fk = 0,
  'ALTER TABLE alumno_auth ADD CONSTRAINT `alumno_auth_ibfk_1` FOREIGN KEY (`alumno_id`) REFERENCES `alumno` (`id`) ON DELETE CASCADE',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================================
-- 3. asistencia_historial: agregar alumno_id y presente
-- ============================================================================
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'asistencia_historial' AND COLUMN_NAME = 'alumno_id');
SET @sql = IF(@col = 0,
  'ALTER TABLE asistencia_historial ADD COLUMN `alumno_id` int NOT NULL DEFAULT 0 AFTER `oferta_id`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'asistencia_historial' AND COLUMN_NAME = 'presente');
SET @sql = IF(@col = 0,
  'ALTER TABLE asistencia_historial ADD COLUMN `presente` tinyint(1) NOT NULL DEFAULT 1 AFTER `alumno_id`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================================
-- 4. asistencia_historial: UNIQUE KEY en vez de INDEX simple
-- ============================================================================
SET @uq = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'asistencia_historial' AND INDEX_NAME = 'uq_asistencia');
SET @sql = IF(@uq = 0,
  'ALTER TABLE asistencia_historial DROP INDEX `idx_asistencia_tipo_oferta`, ADD UNIQUE KEY `uq_asistencia` (`tipo_oferta_academica_id`, `oferta_id`, `alumno_id`)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================================
-- 5. pago: agregar columna voucher
-- ============================================================================
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'pago' AND COLUMN_NAME = 'voucher');
SET @sql = IF(@col = 0,
  'ALTER TABLE pago ADD COLUMN `voucher` varchar(255) DEFAULT NULL AFTER `numero_control`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================================
-- 6. usuario: agregar columna profile_image
-- ============================================================================
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'usuario' AND COLUMN_NAME = 'profile_image');
SET @sql = IF(@col = 0,
  'ALTER TABLE usuario ADD COLUMN `profile_image` varchar(255) DEFAULT NULL AFTER `correo`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================================
-- 7. asistencia: agregar defaults a tipo_oferta_academica_id y oferta_id
-- ============================================================================
SET @def_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'asistencia' AND COLUMN_NAME = 'tipo_oferta_academica_id' AND COLUMN_DEFAULT IS NOT NULL);
SET @sql = IF(@def_exists = 0,
  'ALTER TABLE asistencia ALTER COLUMN `tipo_oferta_academica_id` SET DEFAULT 2',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @def_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'asistencia' AND COLUMN_NAME = 'oferta_id' AND COLUMN_DEFAULT IS NOT NULL);
SET @sql = IF(@def_exists = 0,
  'ALTER TABLE asistencia ALTER COLUMN `oferta_id` SET DEFAULT 0',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================================
-- 8. asistencia: cambiar INDEX a UNIQUE KEY
-- ============================================================================
SET @uq = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'asistencia' AND INDEX_NAME = 'uq_asistencia_oferta');
SET @sql = IF(@uq = 0,
  'ALTER TABLE asistencia DROP INDEX `idx_asistencia_tipo_oferta`, ADD UNIQUE KEY `uq_asistencia_oferta` (`tipo_oferta_academica_id`, `oferta_id`)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================================
-- FIN
-- ============================================================================
