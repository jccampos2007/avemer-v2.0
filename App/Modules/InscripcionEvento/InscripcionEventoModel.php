<?php
// app/Modules/InscripcionEvento/InscripcionEventoModel.php
namespace App\Modules\InscripcionEvento;

use App\Core\Database; // Assumindo que você tem uma classe Database
use PDO;

class InscripcionEventoModel
{
    private $pdo;
    private $table = 'inscripcion_evento';

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene datos de InscripcionEvento para DataTables con paginación, búsqueda y ordenación.
     *
     * @param array $params Parámetros de DataTables (start, length, search, order, columns).
     * @return array Un array asociativo con 'data', 'recordsFiltered', 'recordsTotal'.
     */
    public function getPaginatedInscripcionEvento(array $params): array
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
            0 => 'ie.id',
            1 => 'evento_abierto_numero',
            2 => 'alumno_nombre_completo',
            3 => 'alumno_telefono',
            4 => 'estatus_inscripcion_nombre',
        ];

        // Construir la consulta base
        $sql = "
            SELECT
                ie.id,
                ie.evento_abierto_id,
                ea.numero AS evento_abierto_numero, -- Asumimos que 'numero' es el campo a mostrar de evento_abierto
                ie.alumno_id,
                CONCAT(a.primer_nombre, ' ', a.primer_apellido) AS alumno_nombre_completo, -- Asumimos campos en tabla 'alumno'
                COALESCE(a.tlf_celular, a.tlf_habitacion, a.tlf_trabajo) AS alumno_telefono,
                ie.estatus_inscripcion_id,
                ei.nombre AS estatus_inscripcion_nombre -- Asumimos 'nombre' en tabla 'estatus_inscripcion'
            FROM
                {$this->table} ie
            LEFT JOIN
                evento_abierto ea ON ie.evento_abierto_id = ea.id
            LEFT JOIN
                alumno a ON ie.alumno_id = a.id -- Asumimos una tabla 'alumno'
            LEFT JOIN
                estatus_inscripcion ei ON ie.estatus_inscripcion_id = ei.id -- Asumimos una tabla 'estatus_inscripcion'
        ";
        $countSql = "
            SELECT COUNT(*)
            FROM
                {$this->table} ie
            LEFT JOIN
                evento_abierto ea ON ie.evento_abierto_id = ea.id
            LEFT JOIN
                alumno a ON ie.alumno_id = a.id
            LEFT JOIN
                estatus_inscripcion ei ON ie.estatus_inscripcion_id = ei.id
        ";

        $where = [];
        $where[] = "a.estatus_activo_id = 1";
        $queryParams = [];

        // Búsqueda global
        if (!empty($searchValue)) {
            $where[] = "(ea.numero LIKE :search_evento_abierto "
                . "OR CONCAT(a.primer_nombre, ' ', a.primer_apellido) LIKE :search_alumno_nombre "
                . "OR COALESCE(a.tlf_celular, a.tlf_habitacion, a.tlf_trabajo) LIKE :search_telefono "
                . "OR ei.nombre LIKE :search_estatus_inscripcion)";
            $like = '%' . $searchValue . '%';
            $queryParams[':search_evento_abierto'] = $like;
            $queryParams[':search_alumno_nombre'] = $like;
            $queryParams[':search_telefono'] = $like;
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
        $orderColumnName = $columnMap[$orderColumnIndex] ?? 'ie.id'; // Columna por defecto si no se encuentra
        $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? $orderDir : 'asc';
        $sql .= " ORDER BY {$orderColumnName} {$orderDir}";

        // Paginación
        if ((int)$length !== -1) {
            $sql .= " LIMIT :start, :length";
            $queryParams[':start'] = (int) $start;
            $queryParams[':length'] = (int) $length;
        }

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
     * Obtiene un registro de inscripcion_evento por su ID.
     * @param int $id El ID del registro.
     * @return array|false El registro o false si no se encuentra.
     */
    public function getById(int $id)
    {
        $sql = "SELECT ie.*, CONCAT(primer_apellido, ', ', primer_nombre) AS alumno_nombre_completo FROM {$this->table} ie JOIN alumno al ON ie.alumno_id = al.id WHERE ie.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo registro en inscripcion_evento.
     * @param array $data Los datos del nuevo registro.
     * @return bool True si se creó correctamente, false en caso contrario.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (evento_abierto_id, alumno_id, estatus_inscripcion_id) VALUES (:evento_abierto_id, :alumno_id, :estatus_inscripcion_id)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':evento_abierto_id' => $data['evento_abierto_id'],
            ':alumno_id' => $data['alumno_id'],
            ':estatus_inscripcion_id' => $data['estatus_inscripcion_id']
        ]);
    }

    /**
     * Actualiza un registro existente en inscripcion_evento.
     * @param int $id El ID del registro a actualizar.
     * @param array $data Los nuevos datos del registro.
     * @return bool True si se actualizó correctamente, false en caso contrario.
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET evento_abierto_id = :evento_abierto_id, alumno_id = :alumno_id, estatus_inscripcion_id = :estatus_inscripcion_id WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':evento_abierto_id' => $data['evento_abierto_id'],
            ':alumno_id' => $data['alumno_id'],
            ':estatus_inscripcion_id' => $data['estatus_inscripcion_id'],
            ':id' => $id
        ]);
    }

    public function exists(int $alumnoId, int $eventoAbiertoId, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE alumno_id = :alumno_id AND evento_abierto_id = :evento_abierto_id";
        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':alumno_id', $alumnoId, PDO::PARAM_INT);
        $stmt->bindParam(':evento_abierto_id', $eventoAbiertoId, PDO::PARAM_INT);
        if ($excludeId !== null) {
            $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Elimina un registro de inscripcion_evento.
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
