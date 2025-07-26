<?php
// app/Modules/InscripcionCurso/InscripcionCursoModel.php
namespace App\Modules\InscripcionCurso;

use App\Core\Database; // Assumindo que você tem uma classe Database
use PDO;

class InscripcionCursoModel
{
    private $pdo;
    private $table = 'inscripcion_curso';

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene datos de InscripcionCurso para DataTables con paginación, búsqueda y ordenación.
     *
     * @param array $params Parámetros de DataTables (start, length, search, order, columns).
     * @return array Un array asociativo con 'data', 'recordsFiltered', 'recordsTotal'.
     */
    public function getPaginatedInscripcionCurso(array $params): array
    {
        $draw = $params['draw'] ?? 1;
        $start = $params['start'] ?? 0;
        $length = $params['length'] ?? 10;
        $searchValue = $params['search']['value'] ?? '';
        $orderColumnIndex = $params['order'][0]['column'] ?? 0;
        $orderDir = $params['order'][0]['dir'] ?? 'asc';
        $columns = $params['columns'] ?? [];

        // Mapeo de índices de columna a nombres de columna reales en la base de datos
        // Asegúrate de que estos índices coincidan con el orden de las columnas en tu DataTables JS
        $columnMap = [
            0 => 'ic.id',
            1 => 'curso_abierto_numero', // Alias de la columna unida
            2 => 'alumno_nombre_completo', // Alias de la columna unida
            3 => 'estatus_inscripcion_nombre', // Alias de la columna unida
        ];

        // Construir la consulta base
        $sql = "
            SELECT
                ic.id,
                ic.curso_abierto_id,
                ca.numero AS curso_abierto_numero, -- Asumimos que 'numero' es el campo a mostrar de curso_abierto
                ic.alumno_id,
                CONCAT(a.primer_nombre, ' ', a.primer_apellido) AS alumno_nombre_completo, -- Asumimos campos en tabla 'alumno'
                ic.estatus_inscripcion_id,
                ei.nombre AS estatus_inscripcion_nombre -- Asumimos 'nombre' en tabla 'estatus_inscripcion'
            FROM
                {$this->table} ic
            LEFT JOIN
                curso_abierto ca ON ic.curso_abierto_id = ca.id
            LEFT JOIN
                alumno a ON ic.alumno_id = a.id -- Asumimos una tabla 'alumno'
            LEFT JOIN
                estatus_inscripcion ei ON ic.estatus_inscripcion_id = ei.id -- Asumimos una tabla 'estatus_inscripcion'
        ";
        $countSql = "
            SELECT COUNT(*)
            FROM
                {$this->table} ic
            LEFT JOIN
                curso_abierto ca ON ic.curso_abierto_id = ca.id
            LEFT JOIN
                alumno a ON ic.alumno_id = a.id
            LEFT JOIN
                estatus_inscripcion ei ON ic.estatus_inscripcion_id = ei.id
        ";

        $where = [];
        $where[] = "a.estatus_activo_id = 1";
        $queryParams = [];

        // Búsqueda global
        if (!empty($searchValue)) {
            $where[] = "(ca.numero LIKE :search_curso_abierto "
                . "OR CONCAT(a.primer_nombre, ' ', a.primer_apellido) LIKE :search_alumno_nombre "
                . "OR ei.nombre LIKE :search_estatus_inscripcion)";
            $like = '%' . $searchValue . '%';
            $queryParams[':search_curso_abierto'] = $like;
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
        $orderColumnName = $columnMap[$orderColumnIndex] ?? 'ic.id'; // Columna por defecto si no se encuentra
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
     * Obtiene un registro de inscripcion_curso por su ID.
     * @param int $id El ID del registro.
     * @return array|false El registro o false si no se encuentra.
     */
    public function getById(int $id)
    {
        $sql = "SELECT ic.*, CONCAT(primer_apellido, ', ', primer_nombre) AS alumno_nombre_completo FROM {$this->table} ic JOIN alumno al ON ic.alumno_id = al.id WHERE ic.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo registro en inscripcion_curso.
     * @param array $data Los datos del nuevo registro.
     * @return bool True si se creó correctamente, false en caso contrario.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (curso_abierto_id, alumno_id, estatus_inscripcion_id) VALUES (:curso_abierto_id, :alumno_id, :estatus_inscripcion_id)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':curso_abierto_id' => $data['curso_abierto_id'],
            ':alumno_id' => $data['alumno_id'],
            ':estatus_inscripcion_id' => $data['estatus_inscripcion_id']
        ]);
    }

    /**
     * Actualiza un registro existente en inscripcion_curso.
     * @param int $id El ID del registro a actualizar.
     * @param array $data Los nuevos datos del registro.
     * @return bool True si se actualizó correctamente, false en caso contrario.
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET curso_abierto_id = :curso_abierto_id, alumno_id = :alumno_id, estatus_inscripcion_id = :estatus_inscripcion_id WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':curso_abierto_id' => $data['curso_abierto_id'],
            ':alumno_id' => $data['alumno_id'],
            ':estatus_inscripcion_id' => $data['estatus_inscripcion_id'],
            ':id' => $id
        ]);
    }

    /**
     * Elimina un registro de inscripcion_curso.
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
