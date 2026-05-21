<?php
// app/Modules/Diplomado/DiplomadoModel.php
namespace App\Modules\Diplomado;

use App\Core\Database; // Assumindo que você tem uma classe Database
use PDO;

class DiplomadoModel
{
    private $pdo;
    private $table = 'diplomado';

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene datos de Diplomado para DataTables con paginación, búsqueda y ordenación.
     * Filtra los registros que no han sido borrados lógicamente.
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

        // Construir la consulta base filtrando solo diplomados activos (d.deleted_at IS NULL)
        $sql = "
            SELECT
                d.id,
                d.duracion_id,
                dr.nombre AS duracion_nombre,
                d.nombre,
                d.descripcion,
                d.siglas,
                d.costo,
                d.inicial
            FROM
                {$this->table} d
            LEFT JOIN
                duracion dr ON d.duracion_id = dr.id
            WHERE
                d.deleted_at IS NULL
        ";
        $countSql = "
            SELECT COUNT(*)
            FROM
                {$this->table} d
            LEFT JOIN
                duracion dr ON d.duracion_id = dr.id
            WHERE
                d.deleted_at IS NULL
        ";

        $where = [];
        $queryParams = [];

        // Búsqueda global
        if (!empty($searchValue)) {
            $where[] = "(d.nombre LIKE :search_nombre "
                . "OR d.descripcion LIKE :search_descripcion "
                . "OR d.siglas LIKE :search_siglas)";
            $like = '%' . $searchValue . '%';
            $queryParams[':search_nombre'] = $like;
            $queryParams[':search_descripcion'] = $like;
            $queryParams[':search_siglas'] = $like;
        }

        if (!empty($where)) {
            $sql .= " AND " . implode(' AND ', $where);
            $countSql .= " AND " . implode(' AND ', $where);
        }

        // Obtener el total de registros filtrados (después de la búsqueda)
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($queryParams);
        $recordsFiltered = $stmt->fetchColumn();

        // Ordenación
        $orderColumnName = $columnMap[$orderColumnIndex] ?? 'd.id'; // Columna por defecto si no se encuentra
        $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? $orderDir : 'asc';
        $sql .= " ORDER BY {$orderColumnName} {$orderDir}";

        // Paginación con binds explícitos de enteros para evitar inconvenientes de PDO
        if ((int)$length !== -1) {
            $sql .= " LIMIT :start, :length";
        }

        $stmt = $this->pdo->prepare($sql);
        if ((int)$length !== -1) {
            $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
        }
        foreach ($queryParams as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener el total de registros sin filtrar pero que sigan activos
        $totalRecordsStmt = $this->pdo->query("SELECT COUNT(*) FROM {$this->table} WHERE deleted_at IS NULL");
        $recordsTotal = $totalRecordsStmt->fetchColumn();

        return [
            'draw' => (int) $draw,
            'recordsTotal' => (int) $recordsTotal,
            'recordsFiltered' => (int) $recordsFiltered,
            'data' => $data,
        ];
    }

    /**
     * Obtiene un registro de diplomado activo por su ID.
     * @param int $id El ID del registro.
     * @return array|false El registro o false si no se encuentra o está borrado lógicamente.
     */
    public function getById(int $id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo registro en diplomado.
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
     * Realiza un BORRADO LÓGICO de un registro de diplomado.
     * @param int $id El ID del registro a eliminar.
     * @return bool True si se eliminó correctamente de forma lógica, false en caso contrario.
     */
    public function delete(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}