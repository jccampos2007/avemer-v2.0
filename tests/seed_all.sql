-- ============================================================
-- Comprehensive seed for php_mvc_app_test
-- All INSERTs are idempotent (INSERT IGNORE)
-- ============================================================

-- Reference catalogs
INSERT IGNORE INTO estatus (id, nombre) VALUES (1, 'Abierto'), (2, 'Culminado'), (3, 'Cerrado');
INSERT IGNORE INTO estatus_inscripcion (id, nombre) VALUES (1, 'Inscrito'), (2, 'Pre-Inscrito'), (3, 'Cesante');
INSERT IGNORE INTO estatus_activo (id, nombre) VALUES (1, 'Activo'), (2, 'Inactivo');
INSERT IGNORE INTO tipo_oferta_academica (id, nombre) VALUES (1, 'Curso'), (2, 'Diplomado'), (3, 'Evento'), (4, 'Maestría');
INSERT IGNORE INTO tipo_modalidad (id, nombre) VALUES (1, 'Presencial'), (2, 'Online');

-- Countries / States / Nationalities
INSERT IGNORE INTO pais (id, nombre) VALUES (999, 'TEST País');
INSERT IGNORE INTO estado (id, pais_id, nombre) VALUES (999, 999, 'TEST Estado'), (998, 999, 'TEST Estado 2');
INSERT IGNORE INTO estado (id, pais_id, nombre, deleted_at) VALUES (997, 999, 'TEST Estado Deleted', NOW());
INSERT IGNORE INTO nacionalidad (id, nombre) VALUES (999, 'TEST Nacionalidad');

-- Professions
INSERT IGNORE INTO profesion_oficio (id, nombre) VALUES (999, 'TEST Profesión');

-- Duration
INSERT IGNORE INTO duracion (id, nombre) VALUES (999, 'TEST Duración');

-- Venue / Sede
INSERT IGNORE INTO sede (id, estado_id, nombre, tlf_sede, correo)
VALUES (999, 999, 'TEST Sede', '0000', 'test@sede.com');

-- Bank
INSERT IGNORE INTO banco (id, nombre) VALUES (999, 'TEST Banco');

-- Diploma Program
INSERT IGNORE INTO diplomado (id, duracion_id, nombre, descripcion, siglas, costo, inicial)
VALUES (999, 999, 'TEST Diplomado', 'Test description', 'TD', 100, 50);

-- Chapter
INSERT IGNORE INTO capitulo (id, diplomado_id, numero, nombre, descripcion, orden)
VALUES (999, 999, '1', 'TEST Capítulo', 'Test chapter', 1);

-- Coordinator
INSERT IGNORE INTO coordinador (id, ci_pasaporte, primer_nombre, primer_apellido, estatus_activo_id)
VALUES (999, 'COOR-TEST', 'Coord', 'Test', 1);

-- Root user (must exist first for FK references from other tables)
INSERT IGNORE INTO usuario (usuario_id, grupo_id, usuario_cedula, usuario_nombre, usuario_apellido, usuario_user, usuario_pws, estatus_activo_id, tipo_usuario, usuario_idreg, usuario_fechareg)
VALUES (1, NULL, 'admin', 'Admin', 'System', 'admin', 'admin123', 1, 1, 1, CURDATE());

-- Group (RBAC) — references usuario.usuario_id=1
INSERT IGNORE INTO grupo (grupo_id, nombre_grupo, descripcion_grupo, usuario_idreg, grupo_fechareg)
VALUES (999, 'TEST Grupo', 'Test group', 1, CURDATE());

-- Test User — references grupo 999 and usuario 1
INSERT IGNORE INTO usuario (usuario_id, grupo_id, usuario_cedula, usuario_nombre, usuario_apellido, usuario_user, usuario_pws, estatus_activo_id, tipo_usuario, usuario_idreg, usuario_fechareg)
VALUES (999, 999, 'V-999', 'Test', 'User', 'testuser', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1, 1, CURDATE());

-- Application (for permissions) — references usuario 1
INSERT IGNORE INTO aplicacion (aplicacion_id, nombre_aplicacion, descripcion_aplicacion, usuario_idreg, aplicacion_fechareg)
VALUES (999, 'TEST App', 'Test app', 1, CURDATE());

-- Window (for permissions) — references usuario 1
INSERT IGNORE INTO ventana (ventana_id, ventana_titulo, usuario_idreg, key_word)
VALUES (999, 'TEST Ventana', 1, 'test_window');

-- Permissions — references grupo 999, ventana 999, aplicacion 999, usuario 1
INSERT IGNORE INTO permisos (grupo_id, ventana_id, aplicacion_id, permisos_crear, permisos_modificar, permisos_eliminar, permisos_listar, usuario_idreg, permisos_fechareg)
VALUES (999, 999, 999, 1, 1, 1, 1, 1, CURDATE());

-- Message
INSERT IGNORE INTO mensajehtml (id, titulo, mensaje)
VALUES (999, 'TEST Mensaje', 'Test message body');

-- Event
INSERT IGNORE INTO evento (id, duracion_id, nombre, descripcion, siglas, costo, inicial)
VALUES (999, 999, 'TEST Evento', 'Test description', 'TE', 80, 40);

-- Master's Degree
INSERT INTO maestria (id, duracion_id, nombre, numero) VALUES (999, 999, 'TEST Maestría', 'M-001')
ON DUPLICATE KEY UPDATE duracion_id = VALUES(duracion_id), nombre = VALUES(nombre), numero = VALUES(numero);

-- Teacher / Docente
INSERT IGNORE INTO docente (id, ci_pasaporte, primer_nombre, primer_apellido, profesion_oficio_id, estatus_activo_id)
VALUES (999, 'DOCTEST', 'Doc', 'Test', 999, 1);

-- Course
INSERT IGNORE INTO curso (id, nombre, horas)
VALUES (99991, 'TEST Curso', 40);

-- Open Course
INSERT IGNORE INTO curso_abierto (id, numero, curso_id, sede_id, estatus_id, docente_id, fecha, nombre_carta, convenio)
VALUES (999, 'CA-001', 99991, 999, 1, 999, '2026-01-01', 'Test Carta', 'CONV-001');

-- Open Diploma
INSERT IGNORE INTO diplomado_abierto (id, numero, diplomado_id, sede_id, estatus_id, fecha_inicio, fecha_fin, nombre_carta)
VALUES (999, 'DA-001', 999, 999, 1, '2026-01-01', '2026-06-30', 'Test Diploma Carta');

-- Open Event
INSERT IGNORE INTO evento_abierto (id, numero, evento_id, sede_id, estatus_id, docente_id, fecha_inicio, fecha_fin, nombre_carta)
VALUES (999, 'EA-001', 999, 999, 1, 999, '2026-03-01', '2026-03-05', 'Test Event Carta');

-- Open Master's
INSERT IGNORE INTO maestria_abierto (id, numero, maestria_id, sede_id, estatus_id, docente_id, fecha, nombre_carta)
VALUES (999, 'MA-001', 999, 999, 1, 999, '2026-01-01', 'Test Master Carta');

-- Students
INSERT IGNORE INTO alumno (id, ci_pasaporte, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, correo, tlf_celular, profesion_oficio_id, estado_id, nacionalidad_id, usuario_id, estatus_activo_id, created_at)
VALUES (999901, '99999901', 'Test', 'SinFoto', 'Alumno', 'Uno', 'test1@test.com', '04120000001', 999, 999, 999, 1, 1, NOW());

INSERT IGNORE INTO alumno (id, ci_pasaporte, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, correo, tlf_celular, foto, profesion_oficio_id, estado_id, nacionalidad_id, usuario_id, estatus_activo_id, created_at)
VALUES (999902, '99999902', 'Test', 'ConFoto', 'Alumno', 'Dos', 'test2@test.com', '04120000002', '89504e470d0a1a0a0000000d494844520000', 999, 999, 999, 1, 1, NOW());

-- Enrollment (diplomado)
INSERT IGNORE INTO inscripcion_diplomado (diplomado_abierto_id, alumno_id, estatus_inscripcion_id)
VALUES (999, 999901, 1);

-- Enrollment (evento)
INSERT IGNORE INTO inscripcion_evento (evento_abierto_id, alumno_id, estatus_inscripcion_id)
VALUES (999, 999901, 1);

-- Enrollment (maestria)
INSERT IGNORE INTO inscripcion_maestria (maestria_abierto_id, alumno_id, estatus_inscripcion_id)
VALUES (999, 999901, 1);

-- Mailbox (buzon) for EnviosModel
INSERT IGNORE INTO buzon (id, correo, id_mensaje, estado)
VALUES (999, 'test@envio.com', 999, 0);
