<?php
// app/Modules/Diplomado/DiplomadoModel.php
namespace App\Modules\Diplomado;

use App\Core\Database; // Assumindo que você tem uma classe Database
use PDO;

class DiplomadoModel
{
    private $pdo;
    private $table = 'diplomado';

    public function __construct(PDO $pdo)
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene datos de Diplomado para DataTables con paginación, búsqueda y ordenación.
     *
     * @param array $params Parámetros de DataTables (start, length, search, order, columns).
     * @return array Un array asociativo con 'data', 'recordsFiltered', 'recordsTotal'.
     */
    public function getPaginatedDiplomados(array $params): array
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
            0 => 'd.id',
            1 => 'duracion_nombre', // Alias de la columna unida
            2 => 'd.nombre',
            3 => 'd.descripcion',
            4 => 'd.siglas',
            5 => 'd.costo',
            6 => 'd.inicial',
        ];

        // Construir la consulta base
        $sql = "
            SELECT
                d.id,
                d.duracion_id,
                dr.nombre AS duracion_nombre, -- Asumimos 'nombre' es el campo a mostrar de la tabla 'duracion'
                d.nombre,
                d.descripcion,
                d.siglas,
                d.costo,
                d.inicial
            FROM
                {$this->table} d
            LEFT JOIN
                duracion dr ON d.duracion_id = dr.id -- Asumimos una tabla 'duracion'
        ";
        $countSql = "
            SELECT COUNT(*)
            FROM
                {$this->table} d
            LEFT JOIN
                duracion dr ON d.duracion_id = dr.id
        ";

        $where = [];
        $queryParams = [];

        // Búsqueda global
        if (!empty($searchValue)) {
            $where[] = "(d.nombre LIKE :search_nombre "
                . "OR d.descripcion LIKE :search_descripcion "
                . "OR d.siglas LIKE :search_siglas "
                . "OR d.costo LIKE :search_costo " // Cuidado con buscar float como string
                . "OR d.inicial LIKE :search_inicial " // Cuidado con buscar float como string
                . "OR dr.nombre LIKE :search_duracion_nombre)";
            $like = '%' . $searchValue . '%';
            $queryParams[':search_nombre'] = $like;
            $queryParams[':search_descripcion'] = $like;
            $queryParams[':search_siglas'] = $like;
            $queryParams[':search_costo'] = $like;
            $queryParams[':search_inicial'] = $like;
            $queryParams[':search_duracion_nombre'] = $like;
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
        $orderColumnName = $columnMap[$orderColumnIndex] ?? 'd.id'; // Columna por defecto si no se encuentra
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
     * Obtiene un registro de diplomado por su ID.
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
     * Crea un nuevo registro en diplomado.
     * @param array $data Los datos del nuevo registro.
     * @return bool True si se creó correctamente, false en caso contrario.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (duracion_id, nombre, descripcion, siglas, costo, inicial) VALUES (:duracion_id, :nombre, :descripcion, :siglas, :costo, :inicial)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':duracion_id' => $data['duracion_id'],
            ':nombre' => $data['nombre'],
            ':descripcion' => $data['descripcion'],
            ':siglas' => $data['siglas'],
            ':costo' => $data['costo'],
            ':inicial' => $data['inicial']
        ]);
    }

    /**
     * Actualiza un registro existente en diplomado.
     * @param int $id El ID del registro a actualizar.
     * @param array $data Los nuevos datos del registro.
     * @return bool True si se actualizó correctamente, false en caso contrario.
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET duracion_id = :duracion_id, nombre = :nombre, descripcion = :descripcion, siglas = :siglas, costo = :costo, inicial = :inicial WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':duracion_id' => $data['duracion_id'],
            ':nombre' => $data['nombre'],
            ':descripcion' => $data['descripcion'],
            ':siglas' => $data['siglas'],
            ':costo' => $data['costo'],
            ':inicial' => $data['inicial'],
            ':id' => $id
        ]);
    }

    /**
     * Elimina un registro de diplomado.
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
