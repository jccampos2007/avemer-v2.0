<?php
// php_mvc_app/app/Modules/CursoAbierto/CursoAbiertoModel.php
namespace App\Modules\CursoAbierto; // Nuevo namespace

use App\Core\Database;
use PDO;

class CursoAbiertoModel
{
    private $pdo;
    private $table = 'curso_abierto';

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene todas las aperturas de cursos activas que no han sido borradas.
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT id, numero, curso_id, sede_id, estatus_id, docente_id, fecha, nombre_carta, convenio FROM {$this->table} WHERE deleted_at IS NULL ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    /**
     * Obtiene datos de Cursos Abiertos para DataTables con paginación, búsqueda, ordenación 
     * y filtrado de borrado lógico.
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
            {$this->table} ca
                LEFT JOIN
            curso c ON ca.curso_id = c.id
                LEFT JOIN
            sede s ON ca.sede_id = s.id
                LEFT JOIN
            estatus e ON ca.estatus_id = e.id
                LEFT JOIN
            docente d ON ca.docente_id = d.id";

        $countSql = "SELECT COUNT(*) FROM
            {$this->table} ca
                LEFT JOIN
            curso c ON ca.curso_id = c.id
                LEFT JOIN
            sede s ON ca.sede_id = s.id
                LEFT JOIN
            estatus e ON ca.estatus_id = e.id
                LEFT JOIN
            docente d ON ca.docente_id = d.id";

        // Filtro base de borrado lógico
        $where = ["ca.deleted_at IS NULL"];
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
        $orderColumnName = $columnMap[$orderColumnIndex] ?? 'id';
        $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? $orderDir : 'desc';
        $sql .= " ORDER BY {$orderColumnName} {$orderDir}";

        // Paginación con enlace seguro de enteros
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
     * Busca una apertura de curso activa por su ID.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute(['id' => $id]);
        $curso_abierto = $stmt->fetch();
        return $curso_abierto ?: null;
    }

    /**
     * Cuenta si hay alumnos matriculados en esta apertura de curso para evitar su borrado accidental.
     * Ajustar el nombre de la tabla (por ejemplo, 'inscrito_curso') según corresponda en tu base de datos.
     *
     * @param int $cursoAbiertoId ID de la apertura del curso.
     * @return int Cantidad de inscritos.
     */
    public function countInscritos(int $cursoAbiertoId): int
    {
        // Ajusta el nombre de la tabla pivote/inscritos si es diferente en tu base de datos
        $sql = "SELECT COUNT(*) FROM inscrito_curso WHERE curso_abierto_id = :curso_abierto_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':curso_abierto_id', $cursoAbiertoId, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (numero, curso_id, sede_id, estatus_id, docente_id, fecha, nombre_carta, convenio) VALUES (:numero, :curso_id, :sede_id, :estatus_id, :docente_id, :fecha, :nombre_carta, :convenio)";
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
        $sql = "UPDATE {$this->table} SET numero = :numero, curso_id = :curso_id, sede_id = :sede_id, estatus_id = :estatus_id, docente_id = :docente_id, fecha = :fecha, nombre_carta = :nombre_carta, convenio = :convenio WHERE id = :id";
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

    /**
     * Realiza un BORRADO LÓGICO de la apertura de curso.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}