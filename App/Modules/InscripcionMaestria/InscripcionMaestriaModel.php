<?php
// app/Modules/InscripcionMaestria/InscripcionMaestriaModel.php
namespace App\Modules\InscripcionMaestria;

use App\Core\Database; // Asumiendo que tienes una clase Database
use PDO;

class InscripcionMaestriaModel
{
    private $pdo;
    private $table = 'inscripcion_maestria';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene datos de Inscripción de Maestría para DataTables con paginación, búsqueda y ordenación.
     *
     * @param array $params Parámetros de DataTables (start, length, search, order, columns).
     * @return array Un array asociativo con 'data', 'recordsFiltered', 'recordsTotal'.
     */
    public function getPaginatedInscripcionMaestria(array $params): array
    {
        $draw = $params['draw'] ?? 1;
        $start = $params['start'] ?? 0;
        $length = $params['length'] ?? 10;
        $searchValue = $params['search']['value'] ?? '';
        $orderColumnIndex = $params['order'][0]['column'] ?? 0;
        $orderDir = $params['order'][0]['dir'] ?? 'asc';
        $columns = $params['columns'] ?? [];

        // Mapeo de índices de columna a nombres de columna reales en la base de datos
        $columnMap = [
            0 => 'im.id',
            1 => 'maestria_abierto_numero', // Alias de la columna unida
            2 => 'alumno_nombre_completo',   // Alias de la columna unida
            3 => 'estatus_inscripcion_nombre', // Alias de la columna unida
        ];

        // Construir la consulta base
        $sql = "
            SELECT
                im.id,
                im.maestria_abierto_id,
                ma.numero AS maestria_abierto_numero, -- Asumimos 'numero' es el campo a mostrar de maestria_abierto
                im.alumno_id,
                CONCAT(a.primer_nombre, ' ', a.primer_apellido) AS alumno_nombre_completo, -- Asumimos campos en tabla 'alumno'
                im.estatus_inscripcion_id,
                ei.nombre AS estatus_inscripcion_nombre -- Asumimos 'nombre' en tabla 'estatus_inscripcion'
            FROM
                {$this->table} im
            LEFT JOIN
                maestria_abierto ma ON im.maestria_abierto_id = ma.id
            LEFT JOIN
                alumno a ON im.alumno_id = a.id
            LEFT JOIN
                estatus_inscripcion ei ON im.estatus_inscripcion_id = ei.id
        ";
        $countSql = "
            SELECT COUNT(*)
            FROM
                {$this->table} im
            LEFT JOIN
                maestria_abierto ma ON im.maestria_abierto_id = ma.id
            LEFT JOIN
                alumno a ON im.alumno_id = a.id
            LEFT JOIN
                estatus_inscripcion ei ON im.estatus_inscripcion_id = ei.id
        ";

        $where = [];
        $queryParams = [];

        // Búsqueda global
        if (!empty($searchValue)) {
            $where[] = "(ma.numero LIKE :search_maestria_abierto "
                . "OR CONCAT(a.primer_nombre, ' ', a.primer_apellido) LIKE :search_alumno_nombre "
                . "OR ei.nombre LIKE :search_estatus_inscripcion)";
            $like = '%' . $searchValue . '%';
            $queryParams[':search_maestria_abierto'] = $like;
            $queryParams[':search_alumno_nombre'] = $like;
            $queryParams[':search_estatus_inscripcion'] = $like;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
            $countSql .= " WHERE " . implode(' AND ', $where);
        }

        // Obtener el total de registros filtrados (después de la búsqueda)
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($queryParams);
        $recordsFiltered = $stmt->fetchColumn();

        // Ordenación
        $orderColumnName = $columnMap[$orderColumnIndex] ?? 'im.id'; // Columna por defecto si no se encuentra
        $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? $orderDir : 'asc';
        $sql .= " ORDER BY {$orderColumnName} {$orderDir}";

        // Paginación
        $sql .= " LIMIT :start, :length";
        $queryParams[':start'] = (int) $start;
        $queryParams[':length'] = (int) $length;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($queryParams);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener el total de registros sin filtrar (para 'recordsTotal')
        $totalRecordsStmt = $this->pdo->query("SELECT COUNT(*) FROM {$this->table}");
        $recordsTotal = $totalRecordsStmt->fetchColumn();

        return [
            'draw' => (int) $draw,
            'recordsTotal' => (int) $recordsTotal,
            'recordsFiltered' => (int) $recordsFiltered,
            'data' => $data, // Devolvemos los datos tal cual, el controlador los formateará para DataTables
        ];
    }

    /**
     * Obtiene un registro de inscripcion_maestria por su ID.
     * @param int $id El ID del registro.
     * @return array|false El registro o false si no se encuentra.
     */
    public function getById(int $id)
    {
        $sql = "SELECT im.*, CONCAT(primer_apellido, ', ', primer_nombre) AS alumno_nombre_completo FROM {$this->table} im JOIN alumno al ON im.alumno_id = al.id WHERE im.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo registro en inscripcion_maestria.
     * @param array $data Los datos del nuevo registro.
     * @return bool True si se creó correctamente, false en caso contrario.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (maestria_abierto_id, alumno_id, estatus_inscripcion_id) VALUES (:maestria_abierto_id, :alumno_id, :estatus_inscripcion_id)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':maestria_abierto_id' => $data['maestria_abierto_id'],
            ':alumno_id' => $data['alumno_id'],
            ':estatus_inscripcion_id' => $data['estatus_inscripcion_id']
        ]);
    }

    /**
     * Actualiza un registro existente en inscripcion_maestria.
     * @param int $id El ID del registro a actualizar.
     * @param array $data Los nuevos datos del registro.
     * @return bool True si se actualizó correctamente, false en caso contrario.
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET maestria_abierto_id = :maestria_abierto_id, alumno_id = :alumno_id, estatus_inscripcion_id = :estatus_inscripcion_id WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':maestria_abierto_id' => $data['maestria_abierto_id'],
            ':alumno_id' => $data['alumno_id'],
            ':estatus_inscripcion_id' => $data['estatus_inscripcion_id'],
            ':id' => $id
        ]);
    }

    /**
     * Elimina un registro de inscripcion_maestria.
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
}
