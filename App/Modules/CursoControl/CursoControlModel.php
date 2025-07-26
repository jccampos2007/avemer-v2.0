<?php
// app/Modules/CursoControl/CursoControlModel.php
namespace App\Modules\CursoControl;

use App\Core\Database;
use PDO;

class CursoControlModel
{
    private $pdo;
    private $table = 'curso_control';

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene datos de Taller Control para DataTables con paginación, búsqueda y ordenación.
     *
     * @param array $params Parámetros de DataTables (start, length, search, order, columns).
     * @return array Un array asociativo con 'data', 'recordsFiltered', 'recordsTotal'.
     */
    public function getPaginatedCursoControl(array $params): array
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
            0 => 'cc.id',
            1 => 'curso_abierto_numero',
            2 => 'docente_nombre',
            3 => 'cc.tema',
            4 => 'cc.fecha',
        ];

        // Construir la consulta base
        $sql = "
            SELECT
                cc.id,
                cc.curso_abierto_id,
                ca.numero AS curso_abierto_numero,
                cc.docente_id,
                CONCAT(d.primer_apellido, ', ', d.primer_nombre) AS docente_nombre,
                cc.tema,
                cc.fecha
            FROM
                {$this->table} cc
            LEFT JOIN
                curso_abierto ca ON cc.curso_abierto_id = ca.id
            LEFT JOIN
                docente d ON cc.docente_id = d.id
        ";
        $countSql = "
            SELECT COUNT(*)
            FROM
                {$this->table} cc
            LEFT JOIN
                curso_abierto ca ON cc.curso_abierto_id = ca.id
            LEFT JOIN
                docente d ON cc.docente_id = d.id
        ";

        $where = [];
        $queryParams = [];

        // Búsqueda global
        if (!empty($searchValue)) {
            $where[] = "(ca.numero LIKE :search_numero "
                . "OR CONCAT(d.primer_apellido, ', ', d.primer_nombre) LIKE :search_docente_nombre "
                . "OR cc.tema LIKE :search_tema)";
            $like = '%' . $searchValue . '%';
            $queryParams[':search_numero'] = $like;
            $queryParams[':search_docente_nombre'] = $like;
            $queryParams[':search_tema'] = $like;
            $queryParams[':search_fecha'] = $like;
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
        $orderColumnName = $columnMap[$orderColumnIndex] ?? 'cc.id'; // Columna por defecto si no se encuentra
        $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? $orderDir : 'desc';
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
     * Obtiene un registro de curso_control por su ID.
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
     * Crea un nuevo registro en curso_control.
     * @param array $data Los datos del nuevo registro.
     * @return bool True si se creó correctamente, false en caso contrario.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (curso_abierto_id, docente_id, tema, fecha) VALUES (:curso_abierto_id, :docente_id, :tema, :fecha)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':curso_abierto_id' => $data['curso_abierto_id'],
            ':docente_id' => $data['docente_id'],
            ':tema' => $data['tema'],
            ':fecha' => $data['fecha']
        ]);
    }

    /**
     * Actualiza un registro existente en curso_control.
     * @param int $id El ID del registro a actualizar.
     * @param array $data Los nuevos datos del registro.
     * @return bool True si se actualizó correctamente, false en caso contrario.
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET curso_abierto_id = :curso_abierto_id, docente_id = :docente_id, tema = :tema, fecha = :fecha WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':curso_abierto_id' => $data['curso_abierto_id'],
            ':docente_id' => $data['docente_id'],
            ':tema' => $data['tema'],
            ':fecha' => $data['fecha'],
            ':id' => $id
        ]);
    }

    /**
     * Elimina un registro de curso_control.
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
