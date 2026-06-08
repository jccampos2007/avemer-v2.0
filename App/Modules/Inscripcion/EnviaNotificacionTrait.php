<?php
namespace App\Modules\Inscripcion;

use App\Core\Database;
use PDO;

trait EnviaNotificacionTrait
{
    protected function sendInscripcionEmail(int $alumnoId, int $ofertaAbiertaId, int $typeId): void
    {
        try {
            $pdo = Database::getInstance()->getConnection();

            $stmtAlumno = $pdo->prepare("SELECT * FROM alumno WHERE id = ?");
            $stmtAlumno->execute([$alumnoId]);
            $alumno = $stmtAlumno->fetch(PDO::FETCH_ASSOC);
            if (!$alumno) return;

            $offerData = [];
            $programType = '';
            switch ($typeId) {
                case 1:
                    $stmt = $pdo->prepare("
                        SELECT ca.numero, ca.nombre_carta, c.nombre, s.nombre AS sede_nombre
                        FROM curso_abierto ca
                        INNER JOIN curso c ON ca.curso_id = c.id
                        INNER JOIN sede s ON ca.sede_id = s.id
                        WHERE ca.id = ?
                    ");
                    $stmt->execute([$ofertaAbiertaId]);
                    $offerData = $stmt->fetch(PDO::FETCH_ASSOC);
                    $programType = 'Curso / Taller';
                    break;
                case 2:
                    $stmt = $pdo->prepare("
                        SELECT da.numero, da.nombre_carta, d.nombre, s.nombre AS sede_nombre
                        FROM diplomado_abierto da
                        LEFT JOIN diplomado d ON da.diplomado_id = d.id
                        LEFT JOIN sede s ON da.sede_id = s.id
                        WHERE da.id = ?
                    ");
                    $stmt->execute([$ofertaAbiertaId]);
                    $offerData = $stmt->fetch(PDO::FETCH_ASSOC);
                    $programType = 'Diplomado';
                    break;
                case 3:
                    $stmt = $pdo->prepare("
                        SELECT ea.numero, ea.nombre_carta, e.nombre, s.nombre AS sede_nombre
                        FROM evento_abierto ea
                        INNER JOIN evento e ON ea.evento_id = e.id
                        INNER JOIN sede s ON ea.sede_id = s.id
                        WHERE ea.id = ?
                    ");
                    $stmt->execute([$ofertaAbiertaId]);
                    $offerData = $stmt->fetch(PDO::FETCH_ASSOC);
                    $programType = 'Evento';
                    break;
                case 4:
                    $stmt = $pdo->prepare("
                        SELECT ma.numero, ma.nombre_carta, m.nombre, s.nombre AS sede_nombre
                        FROM maestria_abierto ma
                        INNER JOIN maestria m ON ma.maestria_id = m.id
                        INNER JOIN sede s ON ma.sede_id = s.id
                        WHERE ma.id = ?
                    ");
                    $stmt->execute([$ofertaAbiertaId]);
                    $offerData = $stmt->fetch(PDO::FETCH_ASSOC);
                    $programType = 'Maestría';
                    break;
            }

            if (!$offerData) return;

            $nombreCarta = $offerData['nombre_carta'] ?? '';
            if (empty(trim($nombreCarta))) return;

            $ci = htmlspecialchars($alumno['ci_pasaporte'] ?? '');
            $alumnoName = htmlspecialchars(($alumno['primer_nombre'] ?? '') . ' ' . ($alumno['primer_apellido'] ?? ''));
            $alumnoCorreo = htmlspecialchars($alumno['correo'] ?? '');
            $alumnoTlf = htmlspecialchars($alumno['tlf_celular'] ?? '');

            $numeroProgram = htmlspecialchars($offerData['numero'] ?? '');
            $nombreProgram = htmlspecialchars($offerData['nombre'] ?? '');
            $sedeProgram = htmlspecialchars($offerData['sede_nombre'] ?? '');

            $replaces = [
                '{{alumnoName}}'     => $alumnoName,
                '{{ci}}'             => $ci,
                '{{correo}}'         => $alumnoCorreo,
                '{{tlf}}'            => $alumnoTlf,
                '{{programType}}'    => $programType,
                '{{programaNumero}}' => $numeroProgram,
                '{{programaNombre}}' => $nombreProgram,
                '{{sede}}'           => $sedeProgram,
                '{{year}}'           => date('Y'),
            ];

            $emailBody = str_replace(array_keys($replaces), array_values($replaces), $nombreCarta);

            require_once APP_ROOT . '/App/Modules/Correo/enviar.php';
            $subject = "Inscripción confirmada - {$programType}: {$nombreProgram}";
            correo($subject, $emailBody, $alumnoCorreo);

        } catch (\Exception $e) {
            error_log('Error al enviar correo de inscripción: ' . $e->getMessage());
        }
    }
}
