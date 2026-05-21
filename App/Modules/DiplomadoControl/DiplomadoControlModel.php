<?php
// php_mvc_app/app/Modules/DiplomadoControl/DiplomadoControlModel.php
namespace App\Modules\DiplomadoControl;

use App\Core\Database;
use PDO;

class DiplomadoControlModel
{
    private $pdo;
    private $table = 'diplomado_control';

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene el listado de diplomados abiertos y verifica si ya cuentan con detalle en diplomado_control.
     */
    public function getDiplomadosAbiertosConControl(): array
    {
        $sql = "
            SELECT 
                da.id AS diplomado_abierto_id,
                da.numero AS oferta_numero,
                d.nombre AS diplomado_nombre,
                e.nombre AS estatus_oferta,
                (SELECT COUNT(*) FROM diplomado_control dc WHERE dc.diplomado_abierto_id = da.id) AS total_controles,
                (
                    SELECT COUNT(*) 
                    FROM diplomado_control dc 
                    WHERE dc.diplomado_abierto_id = da.id AND dc.generado = 2
                ) AS controles_generados
            FROM diplomado_abierto da
            JOIN diplomado d ON da.diplomado_id = d.id
            JOIN estatus e ON da.estatus_id = e.id
            WHERE da.deleted_at IS NULL
            ORDER BY da.id DESC
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un diplomado abierto específico por su ID junto con la información del diplomado base.
     */
    public function findDiplomadoAbierto(int $diplomadoAbiertoId): ?array
    {
        $sql = "
            SELECT da.*, d.nombre AS diplomado_nombre, d.id AS diplomado_id
            FROM diplomado_abierto da
            JOIN diplomado d ON da.diplomado_id = d.id
            WHERE da.id = :id AND da.deleted_at IS NULL
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $diplomadoAbiertoId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? $data : null;
    }

    /**
     * Obtiene todos los diplomados abiertos disponibles para la creación de un nuevo control (opciones del select).
     */
    public function getDiplomadosAbiertosDisponibles(): array
    {
        $sql = "
            SELECT da.id, da.numero, d.nombre AS diplomado_nombre
            FROM diplomado_abierto da
            JOIN diplomado d ON da.diplomado_id = d.id
            WHERE da.deleted_at IS NULL
            ORDER BY d.nombre ASC, da.numero DESC
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los capítulos asociados a un diplomado base.
     * Modifica esta consulta según el nombre real de tu tabla de capítulos de diplomados.
     */
    public function getCapitulosPorDiplomado(int $diplomadoId): array
    {
        // Se asume la existencia de una tabla 'capitulo' o similar asociada al diplomado base.
        // Si tu esquema maneja otra nomenclatura, puedes ajustar el nombre de la tabla de capítulos.
        $sql = "
            SELECT id, numero, nombre 
            FROM capitulo 
            WHERE diplomado_id = :diplomado_id AND deleted_at IS NULL
            ORDER BY numero ASC, id ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['diplomado_id' => $diplomadoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el listado de docentes para poblar el select.
     */
    public function getDocentesActivos(): array
    {
        $sql = "
            SELECT id, ci_pasapote, primer_nombre, primer_apellido 
            FROM docente 
            ORDER BY primer_apellido ASC, primer_nombre ASC
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Registra un nuevo control en la tabla diplomado_control.
     */
    public function createControl(array $data): bool
    {
        $sql = "
            INSERT INTO {$this->table} 
            (diplomado_abierto_id, capitulo_id, docente_id, fecha, mensualidad, generado) 
            VALUES 
            (:diplomado_abierto_id, :capitulo_id, :docente_id, :fecha, :mensualidad, :generado)
        ";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'diplomado_abierto_id' => $data['diplomado_abierto_id'],
            'capitulo_id' => $data['capitulo_id'],
            'docente_id' => $data['docente_id'],
            'fecha' => $data['fecha'],
            'mensualidad' => $data['mensualidad'],
            'generado' => $data['generado'] ?? 1
        ]);
    }

    /**
     * Obtiene los detalles de diplomado_control para un diplomado abierto específico.
     */
    public function getControlesPorDiplomadoAbierto(int $diplomadoAbiertoId): array
    {
        $sql = "
            SELECT dc.*, c.nombre AS capitulo_nombre, c.numero AS capitulo_numero, d.primer_nombre, d.primer_apellido
            FROM {$this->table} dc
            JOIN capitulo c ON dc.capitulo_id = c.id
            LEFT JOIN docente d ON dc.docente_id = d.id
            WHERE dc.diplomado_abierto_id = :diplomado_abierto_id
            ORDER BY c.numero ASC, dc.id ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['diplomado_abierto_id' => $diplomadoAbiertoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Elimina todos los registros de control para un diplomado abierto específico (útil para sobreescribir).
     */
    public function deleteControlesPorDiplomadoAbierto(int $diplomadoAbiertoId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE diplomado_abierto_id = :diplomado_abierto_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['diplomado_abierto_id' => $diplomadoAbiertoId]);
    }
}