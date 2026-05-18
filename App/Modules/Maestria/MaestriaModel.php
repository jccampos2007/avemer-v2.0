<?php
// app/Modules/Maestria/MaestriaModel.php
namespace App\Modules\Maestria;

use App\Core\Database; // Asumiendo que tienes una clase Database
use PDO;

class MaestriaModel
{
    private $pdo;
    private $table = 'maestria';

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene datos de Maestría para DataTables con paginación, búsqueda y ordenación.
     * Excluye automáticamente los registros que han sido borrados lógicamente.
     *
     * @param array $params Parámetros de DataTables (start, length, search, order, columns).
     * @return array Un array asociativo con 'data', 'recordsFiltered', 'recordsTotal'.
     */
    public function getPaginatedMaestria(array $params): array
    {
        $draw = $params['draw'] ?? 1;
        $start = $params['start'] ?? 0;
        $length = $params['length'] ?? 10;
        $searchValue = $params['search']['value'] ?? '';
        $orderColumnIndex = $params['order'][0]['column'] ?? 0;
        $orderDir = $params['order'][0]['dir'] ?? 'asc';

        // Mapeo de índices de columna a nombres de columna reales en la base de datos
        $columnMap = [
            0 => 'ma.id',
            1 => 'ma.nombre',
            2 => 'ma.numero',
            3 => 'dr.duracion_nombre',
            4 => 'ma.convenio',
        ];

        // Construir la consulta base filtrando solo las maestrías activas (deleted_at IS NULL)
        $sql = "SELECT ma.*, dr.nombre AS duracion_nombre
            FROM {$this->table} ma 
            JOIN duracion dr ON ma.duracion_id = dr.id
            WHERE ma.deleted_at IS NULL";
            
        $countSql = "SELECT COUNT(*) 
            FROM {$this->table} ma 
            WHERE ma.deleted_at IS NULL";

        $where = [];
        $queryParams = [];

        // Búsqueda global
        if (!empty($searchValue)) {
            $where[] = "(ma.nombre LIKE :search_nombre "
                . "OR ma.numero LIKE :search_numero "
                . "OR ma.convenio LIKE :search_convenio)";
            $like = '%' . $searchValue . '%';
            $queryParams[':search_nombre'] = $like;
            $queryParams[':search_numero'] = $like;
            $queryParams[':search_convenio'] = $like;
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
        $orderColumnName = $columnMap[$orderColumnIndex] ?? 'ma.id';
        $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? $orderDir : 'asc';
        $sql .= " ORDER BY {$orderColumnName} {$orderDir}";

        // Paginación
        $sql .= " LIMIT :start, :length";
        $queryParams[':start'] = (int) $start;
        $queryParams[':length'] = (int) $length;

        $stmt = $this->pdo->prepare($sql);
        // Vinculación segura de enteros para evitar problemas con LIMIT en ciertos drivers PDO
        $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
        foreach ($queryParams as $key => $val) {
            if ($key !== ':start' && $key !== ':length') {
                $stmt->bindValue($key, $val);
            }
        }
        
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener el total de registros activos sin filtrar (para 'recordsTotal')
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
     * Obtiene un registro de maestría activo por su ID.
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
     * Crea un nuevo registro en maestría.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (nombre, numero, duracion_id, convenio) VALUES (:nombre, :numero, :duracion_id, :convenio)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':numero' => $data['numero'] ?? null,
            ':duracion_id' => $data['duracion_id'] ?? 0,
            ':convenio' => $data['convenio'] ?? null
        ]);
    }

    /**
     * Actualiza un registro existente en maestría.
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET nombre = :nombre, numero = :numero, duracion_id = :duracion_id, convenio = :convenio WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':numero' => $data['numero'] ?? null,
            ':duracion_id' => $data['duracion_id'] ?? 0,
            ':convenio' => $data['convenio'] ?? null,
            ':id' => $id
        ]);
    }

    /**
     * Realiza un BORRADO LÓGICO de la maestría.
     * @param int $id El ID del registro a eliminar.
     * @return bool True si se marcó como eliminado, false en caso contrario.
     */
    public function delete(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}