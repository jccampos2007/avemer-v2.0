<?php
// php_mvc_app/App/Modules/Duracion/DuracionModel.php
namespace App\Modules\Duracion;

use App\Core\Database;
use PDO;

class DuracionModel
{
    private $pdo;
    private $table = 'duracion';

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table} ORDER BY nombre ASC");
        return $stmt->fetchAll();
    }

    public function getPaginatedDuraciones(array $params): array
    {
        $draw = $params['draw'] ?? 1;
        $start = $params['start'] ?? 0;
        $length = $params['length'] ?? 10;
        $searchValue = $params['search']['value'] ?? '';
        $orderColumnIndex = $params['order'][0]['column'] ?? 0;
        $orderDir = $params['order'][0]['dir'] ?? 'asc';

        $columnMap = [0 => 'id', 1 => 'nombre'];

        $sql = "SELECT * FROM {$this->table}";
        $countSql = "SELECT COUNT(*) FROM {$this->table}";
        $where = [];
        $queryParams = [];

        if (!empty($searchValue)) {
            $where[] = "nombre LIKE :nombre";
            $queryParams[':nombre'] = '%' . $searchValue . '%';
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
            $countSql .= " WHERE " . implode(' AND ', $where);
        }

        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($queryParams);
        $recordsFiltered = $stmt->fetchColumn();

        $orderColumnName = $columnMap[$orderColumnIndex] ?? 'nombre';
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
            $formattedData[] = [$row['id'], htmlspecialchars($row['nombre']), ''];
        }

        $recordsTotal = $this->pdo->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();

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
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare("INSERT INTO {$this->table} (nombre) VALUES (:nombre)");
        return $stmt->execute(['nombre' => $data['nombre']]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET nombre = :nombre WHERE id = :id");
        return $stmt->execute(['nombre' => $data['nombre'], 'id' => $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
