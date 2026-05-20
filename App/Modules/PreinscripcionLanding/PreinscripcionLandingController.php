<?php
// app/Modules/PreinscripcionLanding/PreinscripcionLandingController.php
namespace App\Modules\PreinscripcionLanding;

use App\Core\Controller;
use PDO;

class PreinscripcionLandingController extends Controller
{
    public function index(): void
    {
        $this->renderLanding('PreinscripcionLanding/Views/preinscripcion_landing');
    }

    /**
     * Endpoint AJAX para buscar un alumno por CI/Pasaporte.
     */
    public function searchAlumno(): void
    {
        header('Content-Type: application/json');

        $ci = trim($_POST['ci_pasapote'] ?? '');
        $ci = str_replace('.', '', $ci);

        if (empty($ci)) {
            echo json_encode(['success' => false, 'message' => 'El CI/Pasaporte no puede estar vacío.']);
            exit();
        }

        try {
            $pdo = \App\Core\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("SELECT * FROM alumno WHERE REPLACE(ci_pasapote, '.', '') = ? LIMIT 1");
            $stmt->execute([$ci]);
            $alumno = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($alumno) {
                echo json_encode(['success' => true, 'found' => true, 'alumno' => $alumno]);
            } else {
                echo json_encode(['success' => true, 'found' => false, 'message' => 'Alumno no registrado. Por favor, complete el formulario.']);
            }
        } catch (\PDOException $e) {
            error_log('Error de BD en searchAlumno (PreinscripcionLanding): ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al buscar alumno.']);
        }
        exit();
    }

    /**
     * Endpoint AJAX para crear un nuevo alumno.
     */
    public function createAlumno(): void
    {
        header('Content-Type: application/json');
        
        $pdo = \App\Core\Database::getInstance()->getConnection();
        try {
            $sql = "INSERT INTO alumno (ci_pasapote, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, correo, tlf_habitacion, tlf_celular) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $this->sanitizeInput($_POST['new_ci_pasapote'] ?? ''),
                $this->sanitizeInput($_POST['new_primer_nombre'] ?? ''),
                $this->sanitizeInput($_POST['new_segundo_nombre'] ?? ''),
                $this->sanitizeInput($_POST['new_primer_apellido'] ?? ''),
                $this->sanitizeInput($_POST['new_segundo_apellido'] ?? ''),
                $this->sanitizeInput($_POST['new_correo'] ?? ''),
                $this->sanitizeInput($_POST['new_tlf_habitacion'] ?? ''),
                $this->sanitizeInput($_POST['new_tlf_celular'] ?? '')
            ]);
            
            $newId = $pdo->lastInsertId();
            $stmt = $pdo->prepare("SELECT * FROM alumno WHERE id = ?");
            $stmt->execute([$newId]);
            $alumno = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'message' => 'Alumno registrado con éxito.', 'alumno' => $alumno]);
        } catch (\PDOException $e) {
            error_log('Error de BD en createAlumno (PreinscripcionLanding): ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al crear alumno en la base de datos.']);
        } catch (\Exception $e) {
            error_log('Error en createAlumno (PreinscripcionLanding): ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al crear alumno: ' . $e->getMessage()]);
        }
        exit();
    }

    /**
     * Endpoint AJAX para obtener las ofertas académicas abiertas por tipo.
     */
    public function getOfertasAbiertas(): void
    {
        header('Content-Type: application/json');
        $typeId = (int)($_POST['typeId'] ?? '1');
        $pdo = \App\Core\Database::getInstance()->getConnection();

        try {
            $sql = '';
            switch ($typeId) {
                case 1:
                    $sql = "SELECT ca.id, ca.numero, ca.fecha, c.nombre, e.nombre AS estado_nombre, s.nombre AS sede_nombre 
                        FROM curso_abierto ca
                            INNER JOIN curso c ON ca.curso_id = c.id 
                            INNER JOIN sede s ON ca.sede_id = s.id
                            INNER JOIN estado e ON s.estado_id = e.id
                        WHERE ca.estatus_id = '1' ORDER BY c.nombre";
                    break;
                case 2:
                    $sql = "SELECT da.id, da.numero, da.diplomado_id, d.nombre, da.sede_id, s.nombre AS sede_nombre, da.estatus_id, st.nombre AS estatus_nombre, da.fecha_inicio, da.fecha_fin
                        FROM diplomado_abierto da
                            LEFT JOIN diplomado d ON da.diplomado_id = d.id
                            LEFT JOIN sede s ON da.sede_id = s.id
                            LEFT JOIN estatus st ON da.estatus_id = st.id
                        WHERE da.estatus_id = 1";
                    break;
                case 3:
                    $sql = "SELECT ea.id, ea.numero, e.nombre, sede.nombre AS sede_nombre, estado.nombre AS estado_nombre, ea.fecha_inicio AS fecha 
                        FROM evento e 
                            INNER JOIN evento_abierto ea ON e.id = ea.evento_id 
                            INNER JOIN sede ON ea.sede_id = sede.id 
                            INNER JOIN estado ON sede.estado_id = estado.id 
                        WHERE ea.estatus_id = 1";
                    break;
                case 4:
                    $sql = "SELECT ma.id, ma.numero, m.nombre, sede.nombre AS sede_nombre, estado.nombre AS estado_nombre, ma.fecha 
                        FROM maestria m 
                            INNER JOIN maestria_abierto ma ON m.id = ma.maestria_id 
                            INNER JOIN sede ON ma.sede_id = sede.id 
                            INNER JOIN estado ON sede.estado_id = estado.id 
                        WHERE ma.estatus_id = 1";
                    break;
            }

            if ($sql !== '') {
                $stmt = $pdo->query($sql);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $data]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Tipo de oferta académica inválido.']);
            }
        } catch (\PDOException $e) {
            error_log('Error de BD en getOfertasAbiertas (PreinscripcionLanding): ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al cargar ofertas abiertas.']);
        }
        exit();
    }

    /**
     * Endpoint AJAX para procesar la pre-inscripción del alumno.
     */
    public function processPreinscripcion(): void
    {
        header('Content-Type: application/json');
        $alumno_id = $_POST['alumno_id'] ?? null;
        $oferta_id = $_POST['oferta_abierta_id'] ?? null;
        $typeId = (int)($_POST['typeId'] ?? '1');

        if (!$alumno_id || !$oferta_id) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
            exit();
        }

        $pdo = \App\Core\Database::getInstance()->getConnection();
        try {
            $table = '';
            switch ($typeId) {
                case 1:
                    $table = 'curso';
                    break;
                case 2:
                    $table = 'diplomado';
                    break;
                case 3:
                    $table = 'evento';
                    break;
                case 4:
                    $table = 'maestria';
                    break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Tipo de oferta inválido.']);
                    exit();
            }

            $check = $pdo->prepare("SELECT id FROM inscripcion_{$table} WHERE alumno_id = ? AND {$table}_abierto_id = ?");
            $check->execute([$alumno_id, $oferta_id]);
            if ($check->fetch()) {
                echo json_encode(['success' => false, 'message' => 'El alumno ya se encuentra pre-inscrito en esta oferta.']);
            } else {
                $stmt = $pdo->prepare("INSERT INTO inscripcion_{$table} (alumno_id, {$table}_abierto_id, estatus_inscripcion_id) VALUES (?, ?, 1)");
                $stmt->execute([$alumno_id, $oferta_id]);

                // ENVIAR EMAIL DE NOTIFICACIÓN
                try {
                    require_once APP_ROOT . '/App/Modules/Correo/enviar.php';

                    // 1. Consultar detalles de la oferta según typeId
                    $offerInfo = [];
                    $programType = '';
                    switch ($typeId) {
                        case 1:
                            $stmtOffer = $pdo->prepare("
                                SELECT ca.numero, c.nombre, s.nombre AS sede_nombre 
                                FROM curso_abierto ca
                                INNER JOIN curso c ON ca.curso_id = c.id 
                                INNER JOIN sede s ON ca.sede_id = s.id
                                WHERE ca.id = ?
                            ");
                            $stmtOffer->execute([$oferta_id]);
                            $offerInfo = $stmtOffer->fetch(PDO::FETCH_ASSOC);
                            $programType = 'Curso / Taller';
                            break;
                        case 2:
                            $stmtOffer = $pdo->prepare("
                                SELECT da.numero, d.nombre, s.nombre AS sede_nombre 
                                FROM diplomado_abierto da
                                LEFT JOIN diplomado d ON da.diplomado_id = d.id
                                LEFT JOIN sede s ON da.sede_id = s.id
                                WHERE da.id = ?
                            ");
                            $stmtOffer->execute([$oferta_id]);
                            $offerInfo = $stmtOffer->fetch(PDO::FETCH_ASSOC);
                            $programType = 'Diplomado';
                            break;
                        case 3:
                            $stmtOffer = $pdo->prepare("
                                SELECT ea.numero, e.nombre, s.nombre AS sede_nombre 
                                FROM evento_abierto ea
                                INNER JOIN evento e ON ea.evento_id = e.id 
                                INNER JOIN sede s ON ea.sede_id = s.id
                                WHERE ea.id = ?
                            ");
                            $stmtOffer->execute([$oferta_id]);
                            $offerInfo = $stmtOffer->fetch(PDO::FETCH_ASSOC);
                            $programType = 'Evento';
                            break;
                        case 4:
                            $stmtOffer = $pdo->prepare("
                                SELECT ma.numero, m.nombre, s.nombre AS sede_nombre 
                                FROM maestria_abierto ma
                                INNER JOIN maestria m ON ma.maestria_id = m.id 
                                INNER JOIN sede s ON ma.sede_id = s.id
                                WHERE ma.id = ?
                            ");
                            $stmtOffer->execute([$oferta_id]);
                            $offerInfo = $stmtOffer->fetch(PDO::FETCH_ASSOC);
                            $programType = 'Maestría';
                            break;
                    }

                    // 2. Consultar detalles del Alumno
                    $stmtAlumno = $pdo->prepare("SELECT * FROM alumno WHERE id = ?");
                    $stmtAlumno->execute([$alumno_id]);
                    $alumnoInfo = $stmtAlumno->fetch(PDO::FETCH_ASSOC);

                    if ($alumnoInfo && $offerInfo) {
                       $ci = htmlspecialchars($alumnoInfo['ci_pasapote'] ?? '');
                       $alumnoName = htmlspecialchars(($alumnoInfo['primer_nombre'] ?? '') . ' ' . ($alumnoInfo['primer_apellido'] ?? ''));
                       $alumnoCorreo = htmlspecialchars($alumnoInfo['correo'] ?? '');
                       $alumnoTlf = htmlspecialchars($alumnoInfo['tlf_celular'] ?? '');

                       $numeroProgram = htmlspecialchars($offerInfo['numero'] ?? '');
                       $nombreProgram = htmlspecialchars($offerInfo['nombre'] ?? '');
                       $sedeProgram = htmlspecialchars($offerInfo['sede_nombre'] ?? '');

                       // HTML Template
                       $emailBody = '
                       <!DOCTYPE html>
                       <html lang="es">
                       <head>
                           <meta charset="UTF-8">
                           <title>Nueva Preinscripción</title>
                       </head>
                       <body style="font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; background-color: #f4f6f8; margin: 0; padding: 20px; -webkit-font-smoothing: antialiased;">
                           <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); overflow: hidden; border-collapse: collapse;">
                               <tr>
                                   <td style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); padding: 30px; text-align: center;">
                                       <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; letter-spacing: 0.5px;">Nueva Pre-inscripción Registrada</h1>
                                       <p style="color: #dbeafe; margin: 5px 0 0 0; font-size: 14px;">Landing Page - Sistema de Gestión</p>
                                   </td>
                               </tr>
                               <tr>
                                   <td style="padding: 30px; color: #334155; font-size: 16px; line-height: 1.6;">
                                       <p style="margin-top: 0; font-size: 16px; font-weight: bold; color: #1e293b;">Detalles del Alumno:</p>
                                       <table width="100%" style="border-collapse: collapse; margin-bottom: 25px; background-color: #f8fafc; border-radius: 6px; overflow: hidden;">
                                           <tr>
                                               <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #64748b; width: 35%;">CI / Pasaporte:</td>
                                               <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">' . $ci . '</td>
                                           </tr>
                                           <tr>
                                               <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #64748b;">Nombre Completo:</td>
                                               <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">' . $alumnoName . '</td>
                                           </tr>
                                           <tr>
                                               <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #64748b;">Correo:</td>
                                               <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">' . $alumnoCorreo . '</td>
                                           </tr>
                                           <tr>
                                               <td style="padding: 12px 15px; font-weight: 600; color: #64748b;">Celular:</td>
                                               <td style="padding: 12px 15px; color: #0f172a;">' . $alumnoTlf . '</td>
                                           </tr>
                                       </table>

                                       <p style="font-size: 16px; font-weight: bold; color: #1e293b; margin-top: 20px;">Programa Seleccionado:</p>
                                       <table width="100%" style="border-collapse: collapse; background-color: #f8fafc; border-radius: 6px; overflow: hidden;">
                                           <tr>
                                               <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #64748b; width: 35%;">Tipo:</td>
                                               <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; color: #0f172a; font-weight: 600;">' . $programType . '</td>
                                           </tr>
                                           <tr>
                                               <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #64748b;">Número:</td>
                                               <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">' . $numeroProgram . '</td>
                                           </tr>
                                           <tr>
                                               <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #64748b;">Nombre:</td>
                                               <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">' . $nombreProgram . '</td>
                                           </tr>
                                           <tr>
                                               <td style="padding: 12px 15px; font-weight: 600; color: #64748b;">Sede:</td>
                                               <td style="padding: 12px 15px; color: #0f172a;">' . $sedeProgram . '</td>
                                           </tr>
                                       </table>
                                   </td>
                               </tr>
                               <tr>
                                   <td style="background-color: #f1f5f9; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0;">
                                       <p style="margin: 0;">Este es un mensaje automático del Sistema de Registro Avemer.</p>
                                       <p style="margin: 5px 0 0 0;">&copy; ' . date('Y') . ' Grupo Avemer. Todos los derechos reservados.</p>
                                   </td>
                               </tr>
                           </table>
                       </body>
                       </html>';

                       $subject = "Nueva preinscripción de " . $alumnoName . " en " . $programType;
                       correo($subject, $emailBody, 'grupoavemer@gmail.com');
                    }
                } catch (\Exception $emailEx) {
                    error_log('Error al enviar correo de preinscripcion: ' . $emailEx->getMessage());
                }

                echo json_encode(['success' => true, 'message' => '¡Pre-inscripción realizada con éxito!']);
            }
        } catch (\PDOException $e) {
            error_log('Error de BD en processPreinscripcion (PreinscripcionLanding): ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al realizar la pre-inscripción.']);
        } catch (\Exception $e) {
            error_log('Error en processPreinscripcion (PreinscripcionLanding): ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al procesar: ' . $e->getMessage()]);
        }
        exit();
    }
}
