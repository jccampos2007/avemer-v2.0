<?php
namespace App\Modules\PreinscripcionLanding;

use App\Core\Database;
use PDO;

class PreinscripcionLandingModel
{
    private PDO $pdo;
    private string $table = 'mensajehtml';

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getTemplateByTitle(string $title): ?array
    {
        $sql = "SELECT titulo, mensaje FROM {$this->table} WHERE titulo = :titulo LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':titulo' => $title]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    private function preinscripcionHtml(): string
    {
        return '<!DOCTYPE html>
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
                        <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">{{ci}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #64748b;">Nombre Completo:</td>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">{{alumnoName}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #64748b;">Correo:</td>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">{{correo}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 15px; font-weight: 600; color: #64748b;">Celular:</td>
                        <td style="padding: 12px 15px; color: #0f172a;">{{tlf}}</td>
                    </tr>
                </table>

                <p style="font-size: 16px; font-weight: bold; color: #1e293b; margin-top: 20px;">Programa Seleccionado:</p>
                <table width="100%" style="border-collapse: collapse; background-color: #f8fafc; border-radius: 6px; overflow: hidden;">
                    <tr>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #64748b; width: 35%;">Tipo:</td>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; color: #0f172a; font-weight: 600;">{{programType}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #64748b;">Número:</td>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">{{programaNumero}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #64748b;">Nombre:</td>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">{{programaNombre}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 15px; font-weight: 600; color: #64748b;">Sede:</td>
                        <td style="padding: 12px 15px; color: #0f172a;">{{sede}}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="background-color: #f1f5f9; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0;">
                <p style="margin: 0;">Este es un mensaje automático del Sistema de Registro Avemer.</p>
                <p style="margin: 5px 0 0 0;">&copy; {{year}} Grupo Avemer. Todos los derechos reservados.</p>
            </td>
        </tr>
    </table>
</body>
</html>';
    }

    private function inscripcionHtml(): string
    {
        return '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inscripción Confirmada</title>
</head>
<body style="font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; background-color: #f4f6f8; margin: 0; padding: 20px; -webkit-font-smoothing: antialiased;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); overflow: hidden; border-collapse: collapse;">
        <tr>
            <td style="background: linear-gradient(135deg, #15803d 0%, #22c55e 100%); padding: 30px; text-align: center;">
                <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; letter-spacing: 0.5px;">¡Inscripción Confirmada!</h1>
                <p style="color: #bbf7d0; margin: 5px 0 0 0; font-size: 14px;">Sistema de Gestión Avemer</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 30px; color: #334155; font-size: 16px; line-height: 1.6;">
                <p style="margin-top: 0; font-size: 16px; font-weight: bold; color: #1e293b;">Detalles del Alumno:</p>
                <table width="100%" style="border-collapse: collapse; margin-bottom: 25px; background-color: #f8fafc; border-radius: 6px; overflow: hidden;">
                    <tr>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #64748b; width: 35%;">CI / Pasaporte:</td>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">{{ci}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #64748b;">Nombre Completo:</td>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">{{alumnoName}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #64748b;">Correo:</td>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">{{correo}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 15px; font-weight: 600; color: #64748b;">Celular:</td>
                        <td style="padding: 12px 15px; color: #0f172a;">{{tlf}}</td>
                    </tr>
                </table>

                <p style="font-size: 16px; font-weight: bold; color: #1e293b; margin-top: 20px;">Programa Inscrito:</p>
                <table width="100%" style="border-collapse: collapse; background-color: #f8fafc; border-radius: 6px; overflow: hidden;">
                    <tr>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #64748b; width: 35%;">Tipo:</td>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; color: #0f172a; font-weight: 600;">{{programType}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #64748b;">Número:</td>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">{{programaNumero}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #64748b;">Nombre:</td>
                        <td style="padding: 12px 15px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">{{programaNombre}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 15px; font-weight: 600; color: #64748b;">Sede:</td>
                        <td style="padding: 12px 15px; color: #0f172a;">{{sede}}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="background-color: #f1f5f9; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0;">
                <p style="margin: 0;">Este es un mensaje automático del Sistema de Registro Avemer.</p>
                <p style="margin: 5px 0 0 0;">&copy; {{year}} Grupo Avemer. Todos los derechos reservados.</p>
            </td>
        </tr>
    </table>
</body>
</html>';
    }

    public function seedTemplates(): array
    {
        $inserted = [];

        $templates = [
            [
                'titulo' => 'Preinscripción',
                'mensaje' => $this->preinscripcionHtml(),
            ],
            [
                'titulo' => 'Inscripción',
                'mensaje' => $this->inscripcionHtml(),
            ],
        ];

        foreach ($templates as $tpl) {
            $existing = $this->getTemplateByTitle($tpl['titulo']);
            if (!$existing) {
                $sql = "INSERT INTO {$this->table} (titulo, mensaje) VALUES (:titulo, :mensaje)";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([':titulo' => $tpl['titulo'], ':mensaje' => $tpl['mensaje']]);
                $inserted[] = $tpl['titulo'];
            }
        }

        return $inserted;
    }
}
