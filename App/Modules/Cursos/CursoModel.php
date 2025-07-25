<?php
// php_mvc_app/app/Modules/Cursos/CursoModel.php
namespace App\Modules\Cursos; // Nuevo namespace

use App\Core\Database;
use PDO;

class CursoModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT id, nombre, numero, horas, convenio FROM curso ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    /**
     * Obtiene datos de Cursos para DataTables con paginación, búsqueda y ordenación.
     *
     * @param array $params Parámetros de DataTables (start, length, search, order, columns).
     * @return array Un array asociativo con 'data', 'recordsFiltered', 'recordsTotal'.
     */
    public function getPaginatedCursos(array $params): array
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
            0 => 'id',
            1 => 'nombre',
            2 => 'numero',
            3 => 'horas',
            4 => 'convenio',
        ];

        // Construir la consulta base
        $sql = "SELECT id, nombre, numero, horas, convenio FROM curso";
        $countSql = "SELECT COUNT(*) FROM curso";
        $where = [];
        $queryParams = [];

        // Búsqueda global
        if (!empty($searchValue)) {
            $where[] = "(nombre LIKE :nombre "
                . "OR numero LIKE :numero "
                . "OR convenio LIKE :convenio)";
            $like = '%' . $searchValue . '%';
            $queryParams[':nombre'] = $like;
            $queryParams[':numero'] = $like;
            $queryParams[':convenio'] = $like;
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
        $orderColumnName = $columnMap[$orderColumnIndex] ?? 'id'; // Columna por defecto si no se encuentra
        $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? $orderDir : 'asc';
        $sql .= " ORDER BY {$orderColumnName} {$orderDir}";

        // Paginación
        $sql .= " LIMIT :start, :length";
        $queryParams[':start'] = (int) $start;
        $queryParams[':length'] = (int) $length;


        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($queryParams);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Formatear los datos para DataTables
        $formattedData = [];
        foreach ($data as $row) {
            $formattedData[] = [
                $row['id'],
                htmlspecialchars($row['nombre']),
                htmlspecialchars($row['numero']),
                htmlspecialchars($row['horas']),
                htmlspecialchars($row['convenio']),
                '' // Columna para acciones (editar/eliminar)
            ];
        }

        // Obtener el total de registros sin filtrar (para 'recordsTotal')
        $totalRecordsStmt = $this->pdo->query("SELECT COUNT(*) FROM curso");
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
        $stmt = $this->pdo->prepare("SELECT * FROM curso WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $curso = $stmt->fetch();
        return $curso ?: null;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO curso (nombre, numero, horas, convenio) VALUES (:nombre, :numero, :horas, :convenio)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'nombre' => $data['nombre'],
            'numero' => $data['numero'],
            'horas' => $data['horas'],
            'convenio' => $data['convenio'],
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE curso SET nombre = :nombre, numero = :numero, horas = :horas, convenio = :convenio WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $params = [
            'nombre' => $data['nombre'],
            'numero' => $data['numero'],
            'horas' => $data['horas'],
            'convenio' => $data['convenio'],
            'id' => $id
        ];
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM curso WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
