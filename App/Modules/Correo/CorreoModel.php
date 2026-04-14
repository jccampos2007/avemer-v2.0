<?php
// app/Modules/Correo/CorreoModel.php
namespace App\Modules\Correo;

use App\Core\Database; // Asume que tienes una clase Database
use PDO;

class CorreoModel
{
    private $pdo;
    private $table = 'correo';

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene un registro de correo por su ID.
     * @param int $id El ID del registro.
     * @return array|false El registro o false si no se encuentra.
     */
    public function getById(int $id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo registro en la tabla correo.
     * La fecha de creación se establece automáticamente a la fecha actual de la base de datos.
     * @param array $data Los datos del nuevo registro.
     * @return bool True si se creó correctamente, false en caso contrario.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (nombre, monto, oferta_academica_id, tipo_oferta_academica_id, generado, fecha_vencimiento, fecha)
                VALUES (:nombre, :monto, :oferta_academica_id, :tipo_oferta_academica_id, :generado, :fecha_vencimiento, CURDATE())"; // Usamos CURDATE() para la fecha
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':monto' => $data['monto'],
            ':oferta_academica_id' => $data['oferta_academica_id'],
            ':tipo_oferta_academica_id' => $data['tipo_oferta_academica_id'],
            ':generado' => $data['generado'] ?? 0, // Valor por defecto
            ':fecha_vencimiento' => $data['fecha_vencimiento']
            // ':fecha' ya no se necesita aquí, se genera en la base de datos
        ]);
    }

    /**
     * Actualiza un registro existente en la tabla correo.
     * La columna 'fecha' (fecha de creación) no se modifica en la actualización.
     * @param int $id El ID del registro a actualizar.
     * @param array $data Los nuevos datos del registro.
     * @return bool True si se actualizó correctamente, false en caso contrario.
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET nombre = :nombre, monto = :monto, oferta_academica_id = :oferta_academica_id,
                tipo_oferta_academica_id = :tipo_oferta_academica_id, generado = :generado, fecha_vencimiento = :fecha_vencimiento
                WHERE id = :id"; // 'fecha' se elimina del SET
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':monto' => $data['monto'],
            ':oferta_academica_id' => $data['oferta_academica_id'],
            ':tipo_oferta_academica_id' => $data['tipo_oferta_academica_id'],
            ':generado' => $data['generado'] ?? 0,
            ':fecha_vencimiento' => $data['fecha_vencimiento'],
            ':id' => $id
        ]);
    }

    /**
     * Elimina un registro de correo.
     * @param int $id El ID del registro a eliminar.
     * @return bool True si se eliminó correctamente, false en caso contrario.
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Obtiene una lista de cursos.
     * @return array
     */
    public function getCursos(): array
    {
        $sql = "SELECT ca.id, CONCAT(ca.numero , ' ', c.nombre) AS nombre 
            FROM curso_abierto ca 
            JOIN curso c ON ca.curso_id = c.id ORDER BY nombre ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una lista de diplomados.
     * @return array
     */
    public function getDiplomados(): array
    {
        $sql = "SELECT da.id, CONCAT(da.numero , ' ', d.nombre) AS nombre 
            FROM diplomado_abierto da 
            JOIN diplomado d ON da.diplomado_id = d.id ORDER BY nombre ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una lista de diplomados.
     * @return array
     */
    public function getEventos(): array
    {
        $sql = "SELECT ea.id, CONCAT(ea.numero , ' ', e.nombre) AS nombre 
            FROM evento_abierto ea 
            JOIN evento e ON ea.evento_id = e.id ORDER BY nombre ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una lista de maestrías.
     * @return array
     */
    public function getMaestrias(): array
    {
        $sql = "SELECT ma.id, CONCAT(ma.numero , ' ', nombre) AS nombre
            FROM maestria_abierto ma 
            JOIN maestria m ON ma.maestria_id = m.id ORDER BY nombre ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una lista de mensajes.
     * @return array
     */
    public function getMensajes(): array
    {
        $sql = "SELECT id, titulo FROM mensajehtml";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una lista de correos para una oferta académica específica.
     * @param int $tipoOfertaId El ID del tipo de oferta académica (1=Curso, 2=Diplomado, etc.).
     * @param int $ofertaId El ID de la oferta académica (curso_id, diplomado_id, etc.).
     * @return array Lista de correos.
     */
    public function getCorreosByOffer(int $tipoOfertaId, int $ofertaId): array
    {
        $offerTableName = '';
        $sqlWhereClause = '';
        switch ($tipoOfertaId) {
            case 1:
                $offerTableName = 'inscripcion_curso';
                $sqlWhereClause = 'INNER JOIN curso_abierto ca on tab.curso_abierto_id = ca.id
                                    INNER JOIN curso ofer on ca.curso_id = ofer.id
                                    where tab.curso_abierto_id =';
                break;
            case 2:
                $offerTableName = 'inscripcion_diplomado';
                $sqlWhereClause = 'INNER JOIN diplomado_abierto da on tab.diplomado_abierto_id = da.id
                                    INNER JOIN diplomado ofer on da.diplomado_id = ofer.id
                                    WHERE tab.diplomado_abierto_id =';
                break;
            case 3:
                $offerTableName = 'inscripcion_evento';
                $sqlWhereClause = 'INNER JOIN evento_abierto ea on tab.evento_abierto_id = ea.id 
                                    INNER JOIN evento ofer on ea.evento_id = ofer.id
                                    WHERE tab.evento_abierto_id = ';
                break;
            case 4:
                $offerTableName = 'inscripcion_maestria';
                $sqlWhereClause = 'INNER JOIN maestria_abierto ma on tab.maestria_abierto_id = ma.id
                                    INNER JOIN maestria ofer on ma.maestria_id = ofer.id
                                    WHERE tab.maestria_abierto_id =';
                break;
            default:
                return [];
        }

        $sql = "
            SELECT a.correo, a.ci_pasapote, concat(a.primer_nombre,' - ',a.primer_apellido) as nombre, ofer.nombre as nombre_oferta
            FROM $offerTableName tab 
                INNER JOIN alumno a on tab.alumno_id = a.id 
                $sqlWhereClause :oferta_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':oferta_id', $ofertaId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los alumnos inscritos en una oferta académica específica.
     * Se asume una tabla de inscripción específica para cada tipo de oferta.
     * Las tablas de inscripción deben tener: alumno_id, y el ID de la oferta (ej. curso_abierto_id, diplomado_abierto_id).
     * @param int $tipoOfertaId El ID del tipo de oferta académica.
     * @param int $ofertaId El ID de la oferta académica.
     * @return array Lista de alumnos.
     */
    public function getStudentsByOffer(int $tipoOfertaId, int $ofertaId): array
    {
        $inscripcionTableName = '';
        $ofertaIdColumnName = ''; // Nombre de la columna de ID en la tabla de inscripción (ej. curso_abierto_id)

        switch ($tipoOfertaId) {
            case 1:
                $inscripcionTableName = 'inscripcion_curso';
                $ofertaIdColumnName = 'curso_abierto_id';
                break;
            case 2:
                $inscripcionTableName = 'inscripcion_diplomado';
                $ofertaIdColumnName = 'diplomado_abierto_id';
                break;
            case 3:
                $inscripcionTableName = 'inscripcion_evento';
                $ofertaIdColumnName = 'evento_abierto_id';
                break;
            case 4:
                $inscripcionTableName = 'inscripcion_maestria';
                $ofertaIdColumnName = 'maestria_abierto_id';
                break;
            default:
                return [];
        }

        $sql = "
            SELECT
                a.id AS alumno_id,
                a.primer_nombre AS alumno_nombre,
                a.primer_apellido AS alumno_apellido
            FROM
                alumno a
            JOIN
                {$inscripcionTableName} i ON a.id = i.alumno_id
            WHERE
                i.{$ofertaIdColumnName} = :oferta_id
            ORDER BY
                a.primer_nombre ASC, a.primer_apellido ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':oferta_id', $ofertaId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Inserta un registro en la tabla 'transaccion'.
     * @param array $data Datos de la transacción (alumno_id, correo_id, monto, tipo, estatus, id_transaccion_origen).
     * @return bool True si se insertó correctamente, false en caso contrario.
     */
    public function insertTransaction(array $data): bool
    {
        $sql = "INSERT INTO transaccion (alumno_id, correo_id, tipo, monto, fecha, estatus, id_transaccion_origen)
                VALUES (:alumno_id, :correo_id, :tipo, :monto, NOW(), :estatus, :id_transaccion_origen)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':alumno_id' => $data['alumno_id'],
            ':correo_id' => $data['correo_id'],
            ':tipo' => $data['tipo'] ?? 1, // Por defecto 1: Debito
            ':monto' => $data['monto'],
            ':estatus' => $data['estatus'] ?? 1, // Por defecto 1: Deuda generada
            ':id_transaccion_origen' => $data['id_transaccion_origen'] ?? null
        ]);
    }

    /**
     * Actualiza el campo 'generado' de una correo.
     * @param int $correoId El ID de la correo.
     * @param int $generadoStatus El nuevo estado de 'generado' (0 o 1).
     * @return bool True si se actualizó correctamente, false en caso contrario.
     */
    public function updateCorreoGenerado(int $correoId, int $generadoStatus): bool
    {
        $sql = "UPDATE {$this->table} SET generado = :generado WHERE id = :correo_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':generado', $generadoStatus, PDO::PARAM_INT);
        $stmt->bindParam(':correo_id', $correoId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
