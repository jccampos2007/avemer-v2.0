<?php
// app/Modules/PreinscripcionDiplomado/PreinscripcionDiplomadoModel.php
namespace App\Modules\PreinscripcionDiplomado;

use PDO;

class PreinscripcionDiplomadoModel
{
    private $pdo;
    private $table = 'inscripcion_diplomado'; // La pre-inscripción se guarda en la misma tabla

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Crea un nuevo registro de pre-inscripción.
     * @param array $data Los datos del nuevo registro (diplomado_abierto_id, alumno_id, estatus_inscripcion_id).
     * @return bool True si se creó correctamente, false en caso contrario.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (diplomado_abierto_id, alumno_id, estatus_inscripcion_id) VALUES (:diplomado_abierto_id, :alumno_id, :estatus_inscripcion_id)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':diplomado_abierto_id' => $data['diplomado_abierto_id'],
            ':alumno_id' => $data['alumno_id'],
            ':estatus_inscripcion_id' => $data['estatus_inscripcion_id']
        ]);
    }

    /**
     * Verifica si ya existe una inscripción (o pre-inscripción) para un alumno y diplomado abierto.
     * @param int $alumnoId
     * @param int $diplomadoAbiertoId
     * @return bool True si existe, false en caso contrario.
     */
    public function exists(int $alumnoId, int $diplomadoAbiertoId): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE alumno_id = :alumno_id AND diplomado_abierto_id = :diplomado_abierto_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':alumno_id', $alumnoId, PDO::PARAM_INT);
        $stmt->bindParam(':diplomado_abierto_id', $diplomadoAbiertoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    // No se necesitan métodos getById, update, delete para este módulo específico de pre-inscripción
}
