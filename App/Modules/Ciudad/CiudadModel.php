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

    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table} ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

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
        
        $where = [];
        $queryParams = [];

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

        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($queryParams);
        $recordsFiltered = $stmt->fetchColumn();

        $orderColumnName = $columnMap[$orderColumnIndex] ?? 'id';
        $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? $orderDir : 'asc';
        $sql .= " ORDER BY {$orderColumnName} {$orderDir}";

        $sql .= " LIMIT :start, :length";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
        foreach ($queryParams as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formattedData = [];
        foreach ($data as $row) {
            $formattedData[] = [
                $row['id'],
                htmlspecialchars($row['nombre']),
                htmlspecialchars($row['pais_id']),
                '' 
            ];
        }

        $totalRecordsStmt = $this->pdo->query("SELECT COUNT(*) FROM {$this->table}");
        $recordsTotal = $totalRecordsStmt->fetchColumn();

        return [
            'draw' => (int) $draw,
            'recordsTotal' => (int) $recordsTotal,
            'recordsFiltered' => (int) $recordsFiltered,
            'data' => $formattedData,
        ];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id");
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

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
