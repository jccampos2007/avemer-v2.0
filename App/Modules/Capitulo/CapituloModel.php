<?php
// app/Modules/Capitulo/CapituloModel.php
namespace App\Modules\Capitulo;

use App\Core\Database;
use PDO;

class CapituloModel
{
    private $pdo;
    private $table = 'capitulo';

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene datos de Capítulo para DataTables con paginación, búsqueda y ordenación.
     * Filtra por diplomado_id.
     *
     * @param array $params Parámetros de DataTables (start, length, search, order, columns, diplomado_id).
     * @return array Un array asociativo con 'data', 'recordsFiltered', 'recordsTotal'.
     */
    public function getPaginatedCapitulos(array $params): array
    {
        $draw = $params['draw'] ?? 1;
        $start = $params['start'] ?? 0;
        $length = $params['length'] ?? 10;
        $searchValue = $params['search']['value'] ?? '';
        $orderColumnIndex = $params['order'][0]['column'] ?? 0;
        $orderDir = $params['order'][0]['dir'] ?? 'asc';
        $columns = $params['columns'] ?? [];
        $diplomadoId = $params['diplomado_id'] ?? null; // ¡CRUCIAL! Recibir el diplomado_id

        // Mapeo de índices de columna a nombres de columna reales en la base de datos
        $columnMap = [
            0 => 'c.id',
            1 => 'c.numero',
            2 => 'c.nombre',
            3 => 'c.descripcion',
            4 => 'c.activo',
        ];

        // Construir la consulta base
        $sql = "
            SELECT
                c.id,
                c.diplomado_id,
                c.numero,
                c.nombre,
                c.descripcion,
                c.activo,
                c.orden
            FROM
                {$this->table} c
        ";
        $countSql = "
            SELECT COUNT(*)
            FROM
                {$this->table} c
        ";

        $where = [];
        $queryParams = [];

        // Filtro obligatorio por diplomado_id
        if ($diplomadoId !== null) {
            $where[] = "c.diplomado_id = :diplomado_id";
            $queryParams[':diplomado_id'] = (int) $diplomadoId;
        } else {
            // Si no hay diplomado_id, no se devuelve nada
            return [
                'draw' => (int) $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ];
        }

        // Búsqueda global
        if (!empty($searchValue)) {
            $where[] = "(c.numero LIKE :search_numero "
                . "OR c.nombre LIKE :search_nombre "
                . "OR c.descripcion LIKE :search_descripcion)";
            $like = '%' . $searchValue . '%';
            $queryParams[':search_numero'] = $like;
            $queryParams[':search_nombre'] = $like;
            $queryParams[':search_descripcion'] = $like;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
            $countSql .= " WHERE " . implode(' AND ', $where);
        }

        // Obtener el total de registros filtrados (después de la búsqueda y el filtro de diplomado)
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($queryParams);
        $recordsFiltered = $stmt->fetchColumn();

        // Ordenación
        $orderColumnName = $columnMap[$orderColumnIndex] ?? 'c.orden'; // Ordenar por 'orden' por defecto
        $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? $orderDir : 'asc';
        $sql .= " ORDER BY {$orderColumnName} {$orderDir}";

        // Paginación
        $sql .= " LIMIT :start, :length";
        $queryParams[':start'] = (int) $start;
        $queryParams[':length'] = (int) $length;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($queryParams);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener el total de registros sin filtrar (pero con el filtro de diplomado)
        $totalRecordsSql = "SELECT COUNT(*) FROM {$this->table} WHERE diplomado_id = :diplomado_id_total";
        $totalRecordsStmt = $this->pdo->prepare($totalRecordsSql);
        $totalRecordsStmt->bindParam(':diplomado_id_total', $diplomadoId, PDO::PARAM_INT);
        $totalRecordsStmt->execute();
        $recordsTotal = $totalRecordsStmt->fetchColumn();

        return [
            'draw' => (int) $draw,
            'recordsTotal' => (int) $recordsTotal,
            'recordsFiltered' => (int) $recordsFiltered,
            'data' => $data,
        ];
    }

    /**
     * Obtiene un registro de capitulo por su ID.
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
     * Crea un nuevo registro en capitulo.
     * @param array $data Los datos del nuevo registro.
     * @return bool True si se creó correctamente, false en caso contrario.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (diplomado_id, numero, nombre, descripcion, activo, orden) VALUES (:diplomado_id, :numero, :nombre, :descripcion, :activo, :orden)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':diplomado_id' => $data['diplomado_id'],
            ':numero' => $data['numero'],
            ':nombre' => $data['nombre'],
            ':descripcion' => $data['descripcion'],
            ':activo' => $data['activo'],
            ':orden' => $data['orden']
        ]);
    }

    /**
     * Actualiza un registro existente en capitulo.
     * @param int $id El ID del registro a actualizar.
     * @param array $data Los nuevos datos del registro.
     * @return bool True si se actualizó correctamente, false en caso contrario.
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET diplomado_id = :diplomado_id, numero = :numero, nombre = :nombre, descripcion = :descripcion, activo = :activo, orden = :orden WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':diplomado_id' => $data['diplomado_id'],
            ':numero' => $data['numero'],
            ':nombre' => $data['nombre'],
            ':descripcion' => $data['descripcion'],
            ':activo' => $data['activo'],
            ':orden' => $data['orden'],
            ':id' => $id
        ]);
    }

    /**
     * Elimina un registro de capitulo.
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
