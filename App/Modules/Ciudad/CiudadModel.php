<?php
namespace App\Modules\Ciudad;

use App\Core\Database;
use PDO;

class CiudadModel
{
    private $pdo;
    private $table = 'estado';

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene todas las ciudades que no han sido borradas lógicamente.
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table} WHERE deleted_at IS NULL ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todos los países disponibles.
     */
    public function getAllPaises(): array
    {
        $stmt = $this->pdo->query("SELECT id, nombre FROM pais ORDER BY nombre ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene datos de Ciudades para DataTables con paginación, búsqueda, ordenación
     * y filtrado de borrado lógico.
     *
     * @param array $params Parámetros de DataTables (start, length, search, order, columns).
     * @return array Un array asociativo con 'data', 'recordsFiltered', 'recordsTotal'.
     */
    public function getPaginated(array $params): array
    {
        $draw = $params['draw'] ?? 1;
        $start = $params['start'] ?? 0;
        $length = $params['length'] ?? 10;
        $searchValue = $params['search']['value'] ?? '';
        $orderColumnIndex = $params['order'][0]['column'] ?? 0;
        $orderDir = $params['order'][0]['dir'] ?? 'asc';

        $columnMap = [
            0 => 'id',
            1 => 'nombre',
            2 => 'pais_id'
        ];

        $sql = "SELECT id, nombre, pais_id FROM {$this->table}";
        $countSql = "SELECT COUNT(*) FROM {$this->table}";
        
        // Filtro base de borrado lógico
        $where = ["deleted_at IS NULL"];
        $queryParams = [];

        // Búsqueda global
        if (!empty($searchValue)) {
            $where[] = "(nombre LIKE :nombre OR pais_id LIKE :pais_id)";
            $like = '%' . $searchValue . '%';
            $queryParams[':nombre'] = $like;
            $queryParams[':pais_id'] = $like;
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
        $orderColumnName = $columnMap[$orderColumnIndex] ?? 'id';
        $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? $orderDir : 'asc';
        $sql .= " ORDER BY {$orderColumnName} {$orderDir}";

        // Paginación con enlace seguro de enteros
        $sql .= " LIMIT :start, :length";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
        foreach ($queryParams as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Formatear los datos para DataTables
        $formattedData = [];
        foreach ($data as $row) {
            $formattedData[] = [
                $row['id'],
                htmlspecialchars($row['nombre']),
                htmlspecialchars($row['pais_id']),
                '' 
            ];
        }

        // Obtener el total de registros activos sin filtrar (para 'recordsTotal')
        $totalRecordsStmt = $this->pdo->query("SELECT COUNT(*) FROM {$this->table} WHERE deleted_at IS NULL");
        $recordsTotal = $totalRecordsStmt->fetchColumn();

        return [
            'draw' => (int) $draw,
            'recordsTotal' => (int) $recordsTotal,
            'recordsFiltered' => (int) $recordsFiltered,
            'data' => $formattedData,
        ];
    }

    /**
     * Busca una ciudad activa por su ID.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (nombre, pais_id) VALUES (:nombre, :pais_id)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':pais_id' => $data['pais_id'] ?? 1
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET nombre = :nombre, pais_id = :pais_id WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':pais_id' => $data['pais_id'] ?? 1,
            ':id' => $id
        ]);
    }

    /**
     * Realiza un BORRADO LÓGICO de la ciudad.
     */
    public function delete(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT); 
        return $stmt->execute();
    }
}