<?php
// app/Modules/Evento/EventoModel.php
namespace App\Modules\Evento;

use PDO;

class EventoModel
{
    private $pdo;
    private $table = 'evento';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene datos de Evento para DataTables (Solo registros NO eliminados lógicamente).
     */
    public function getPaginatedEvento(array $params): array
    {
        $draw = $params['draw'] ?? 1;
        $start = $params['start'] ?? 0;
        $length = $params['length'] ?? 10;
        $searchValue = $params['search']['value'] ?? '';
        $orderColumnIndex = $params['order'][0]['column'] ?? 0;
        $orderDir = $params['order'][0]['dir'] ?? 'asc';

        $columnMap = [
            0 => 'e.id',
            1 => 'duracion_nombre',
            2 => 'e.nombre',
            3 => 'e.descripcion',
            4 => 'e.siglas',
            5 => 'e.costo',
            6 => 'e.inicial',
        ];

        // Filtro base: Excluir los eliminados lógicamente (e.deleted_at IS NULL)
        $sql = "
            SELECT
                e.id,
                e.duracion_id,
                d.nombre AS duracion_nombre,
                e.nombre,
                e.descripcion,
                e.siglas,
                e.costo,
                e.inicial
            FROM
                {$this->table} e
            LEFT JOIN
                duracion d ON e.duracion_id = d.id
            WHERE
                e.deleted_at IS NULL
        ";
        
        $countSql = "
            SELECT COUNT(*)
            FROM
                {$this->table} e
            LEFT JOIN
                duracion d ON e.duracion_id = d.id
            WHERE
                e.deleted_at IS NULL
        ";

        $where = [];
        $queryParams = [];

        if (!empty($searchValue)) {
            // Se añade la condición de búsqueda respetando el filtro de borrado lógico previo
            $where[] = "(e.nombre LIKE :search_nombre "
                . "OR e.descripcion LIKE :search_descripcion "
                . "OR e.siglas LIKE :search_siglas "
                . "OR e.costo LIKE :search_costo "
                . "OR e.inicial LIKE :search_inicial "
                . "OR d.nombre LIKE :search_duracion_nombre)";
            
            $like = '%' . $searchValue . '%';
            $queryParams[':search_nombre'] = $like;
            $queryParams[':search_descripcion'] = $like;
            $queryParams[':search_siglas'] = $like;
            $queryParams[':search_costo'] = $like;
            $queryParams[':search_inicial'] = $like;
            $queryParams[':search_duracion_nombre'] = $like;
        }

        if (!empty($where)) {
            $sql .= " AND " . implode(' AND ', $where);
            $countSql .= " AND " . implode(' AND ', $where);
        }

        // Obtener total filtrado
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($queryParams);
        $recordsFiltered = $stmt->fetchColumn();

        // Ordenación
        $orderColumnName = $columnMap[$orderColumnIndex] ?? 'e.id';
        $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? $orderDir : 'asc';
        $sql .= " ORDER BY {$orderColumnName} {$orderDir}";

        // Paginación
        if ((int)$length !== -1) {
            if ((int)$length !== -1) {
            $sql .= " LIMIT :start, :length";
        }
            $queryParams[':start'] = (int) $start;
            $queryParams[':length'] = (int) $length;
        }

        $stmt = $this->pdo->prepare($sql);
        // Vinculación manual de enteros por compatibilidad con LIMIT en emulación de PDO
        if ((int)$length !== -1) {
            $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
        }
        foreach ($queryParams as $key => $val) {
            if ($key !== ':start' && $key !== ':length') {
                $stmt->bindValue($key, $val);
            }
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Total de registros activos para el indicador general
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
     * Obtiene un registro activo por su ID.
     */
    public function getById(int $id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

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
     * Realiza un BORRADO LÓGICO del evento.
     * También deberías evaluar propagar este estado a las aperturas asociadas si corresponde.
     */
    public function delete(int $id): bool
    {
        // Se realiza un UPDATE en lugar de un DELETE físico
        $sql = "UPDATE {$this->table} SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}