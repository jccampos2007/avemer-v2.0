<?php
// php_mvc_app/app/Modules/CursoAbierto/CursoAbiertoModel.php
namespace App\Modules\CursoAbierto; // Nuevo namespace

use App\Core\Database;
use PDO;

class CursoAbiertoModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT id, numero, curso_id, sede_id, estatus_id, docente_id, fecha, nombre_carta, convenio FROM curso_abierto ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    /**
     * Obtiene datos de Cursos Abiertos para DataTables con paginación, búsqueda y ordenación.
     *
     * @param array $params Parámetros de DataTables (start, length, search, order, columns).
     * @return array Un array asociativo con 'data', 'recordsFiltered', 'recordsTotal'.
     */
    public function getPaginatedCursoAbierto(array $params): array
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
            1 => 'ca.numero',
            2 => 'curso_nombre',
            3 => 'sede_nombre',
            4 => 'estatus_nombre',
            5 => 'docente_nombre',
            6 => 'fecha',
            7 => 'nombre_carta',
            8 => 'ca.convenio',
        ];

        // Construir la consulta base
        $sql = "SELECT 
            ca.id,
            ca.numero,
            ca.fecha,
            c.nombre AS curso_nombre,
            s.nombre AS sede_nombre,
            e.nombre AS estatus_nombre,
            CONCAT(d.primer_apellido, ', ', d.primer_nombre) AS docente_nombre
        FROM
            curso_abierto ca
                LEFT JOIN
            curso c ON ca.curso_id = c.id
                LEFT JOIN
            sede s ON ca.sede_id = s.id
                LEFT JOIN
            estatus e ON ca.estatus_id = e.id
                LEFT JOIN
            docente d ON ca.docente_id = d.id";
        $countSql = "SELECT COUNT(*) FROM
            curso_abierto ca
                LEFT JOIN
            curso c ON ca.curso_id = c.id
                LEFT JOIN
            sede s ON ca.sede_id = s.id
                LEFT JOIN
            estatus e ON ca.estatus_id = e.id
                LEFT JOIN
            docente d ON ca.docente_id = d.id";
        $where = [];
        $queryParams = [];

        // Búsqueda global
        if (!empty($searchValue)) {
            $where[] = "(ca.numero LIKE :numero "
                . "OR c.nombre LIKE :curso_nombre "
                . "OR s.nombre LIKE :sede_nombre "
                . "OR e.nombre LIKE :estatus_nombre "
                . "OR CONCAT(d.primer_apellido, ', ', d.primer_nombre) LIKE :docente_nombre "
                . "OR ca.convenio LIKE :convenio)";
            $like = '%' . $searchValue . '%';
            $queryParams[':numero'] = $like;
            $queryParams[':curso_nombre'] = $like;
            $queryParams[':sede_nombre'] = $like;
            $queryParams[':estatus_nombre'] = $like;
            $queryParams[':docente_nombre'] = $like;
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
        $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? $orderDir : 'desc';
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
                htmlspecialchars($row['numero']),
                htmlspecialchars($row['curso_nombre'] ?? ''),
                htmlspecialchars($row['sede_nombre'] ?? ''),
                htmlspecialchars($row['estatus_nombre'] ?? ''),
                htmlspecialchars($row['docente_nombre'] ?? ''),
                htmlspecialchars($row['fecha']),
                ''
            ];
        }

        // Obtener el total de registros sin filtrar (para 'recordsTotal')
        $totalRecordsStmt = $this->pdo->query("SELECT COUNT(*) FROM curso_abierto");
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
        $stmt = $this->pdo->prepare("SELECT * FROM curso_abierto WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $curso_abierto = $stmt->fetch();
        return $curso_abierto ?: null;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO curso_abierto (numero, curso_id, sede_id, estatus_id, docente_id, fecha, nombre_carta, convenio) VALUES (:numero, :curso_id, :sede_id, :estatus_id, :docente_id, :fecha, :nombre_carta, :convenio)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'numero' => $data['numero'],
            'curso_id' => $data['curso_id'],
            'sede_id' => $data['sede_id'],
            'estatus_id' => $data['estatus_id'],
            'docente_id' => $data['docente_id'],
            'fecha' => $data['fecha'],
            'nombre_carta' => $data['nombre_carta'],
            'convenio' => $data['convenio'],
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE curso_abierto SET numero = :numero, curso_id = :curso_id, sede_id = :sede_id, estatus_id = :estatus_id, docente_id = :docente_id, fecha = :fecha, nombre_carta = :nombre_carta, convenio = :convenio WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $params = [
            'numero' => $data['numero'],
            'curso_id' => $data['curso_id'],
            'sede_id' => $data['sede_id'],
            'estatus_id' => $data['estatus_id'],
            'docente_id' => $data['docente_id'],
            'fecha' => $data['fecha'],
            'nombre_carta' => $data['nombre_carta'],
            'convenio' => $data['convenio'],
            'id' => $id
        ];
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM curso_abierto WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
