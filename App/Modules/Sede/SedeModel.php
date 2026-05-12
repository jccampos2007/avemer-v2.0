<?php
// php_mvc_app/App/Modules/Sede/SedeModel.php
namespace App\Modules\Sede;

use App\Core\Database;
use PDO;

class SedeModel
{
    private $pdo;
    private $table = 'sede';

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table} ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    /**
     * Obtiene datos de sedes para DataTables con paginación, búsqueda y ordenación.
     *
     * @param array $params Parámetros de DataTables (start, length, search, order, columns).
     * @return array Un array asociativo con 'data', 'recordsFiltered', 'recordsTotal'.
     */
    public function getPaginatedSedes(array $params): array
    {
        $draw = $params['draw'] ?? 1;
        $start = $params['start'] ?? 0;
        $length = $params['length'] ?? 10;
        $searchValue = $params['search']['value'] ?? '';
        $orderColumnIndex = $params['order'][0]['column'] ?? 0;
        $orderDir = $params['order'][0]['dir'] ?? 'asc';

        // Mapeo de índices de columna a nombres de columna reales en la base de datos
        $columnMap = [
            0 => 's.id',
            1 => 's.nombre',
            2 => 's.tlf_sede',
            3 => 's.correo',
            4 => 'e.nombre'
        ];

        // Construir la consulta base
        $sql = "SELECT s.*, e.nombre AS estado_nombre FROM {$this->table} s LEFT JOIN estado e ON s.estado_id = e.id";
        $countSql = "SELECT COUNT(*) FROM {$this->table} s LEFT JOIN estado e ON s.estado_id = e.id";
        $where = [];
        $queryParams = [];

        // Búsqueda global
        if (!empty($searchValue)) {
            $where[] = "(s.nombre LIKE :nombre "
                . "OR s.tlf_sede LIKE :tlf_sede "
                . "OR s.correo LIKE :correo "
                . "OR e.nombre LIKE :estado_nombre)";
            $like = '%' . $searchValue . '%';
            $queryParams[':nombre'] = $like;
            $queryParams[':tlf_sede'] = $like;
            $queryParams[':correo'] = $like;
            $queryParams[':estado_nombre'] = $like;
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
        $orderColumnName = $columnMap[$orderColumnIndex] ?? 's.id';
        $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? $orderDir : 'asc';
        $sql .= " ORDER BY {$orderColumnName} {$orderDir}";

        // Paginación
        $sql .= " LIMIT :start, :length";
        // Necesitamos usar bindValue para start y length para asegurar que sean tratados como enteros
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
                htmlspecialchars($row['tlf_sede']),
                htmlspecialchars($row['correo']),
                htmlspecialchars($row['estado_nombre'] ?? 'N/A'),
                '' // Espacio para acciones
            ];
        }

        // Obtener el total de registros sin filtrar
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
        $sede = $stmt->fetch();
        return $sede ?: null;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (estado_id, nombre, tlf_sede, correo) VALUES (:estado_id, :nombre, :tlf_sede, :correo)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'estado_id' => $data['estado_id'],
            'nombre' => $data['nombre'],
            'tlf_sede' => $data['tlf_sede'],
            'correo' => $data['correo']
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET estado_id = :estado_id, nombre = :nombre, tlf_sede = :tlf_sede, correo = :correo WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'estado_id' => $data['estado_id'],
            'nombre' => $data['nombre'],
            'tlf_sede' => $data['tlf_sede'],
            'correo' => $data['correo'],
            'id' => $id
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
