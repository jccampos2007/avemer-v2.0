<?php
// app/Modules/Maestria/MaestriaModel.php
namespace App\Modules\Maestria;

use App\Core\Database; // Asumiendo que tienes una clase Database
use PDO;

class MaestriaModel
{
    private $pdo;
    private $table = 'maestria';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene datos de Maestría para DataTables con paginación, búsqueda y ordenación.
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
        $columns = $params['columns'] ?? [];

        // Mapeo de índices de columna a nombres de columna reales en la base de datos
        // Asegúrate de que estos índices coincidan con el orden de las columnas en tu DataTables JS
        $columnMap = [
            0 => 'ma.id',
            1 => 'ma.nombre',
            2 => 'ma.numero',
            3 => 'dr.duracion_nombre',
            4 => 'ma.convenio',
        ];

        // Construir la consulta base
        $sql = "SELECT ma.*, dr.nombre AS duracion_nombre
            FROM {$this->table} ma 
            JOIN duracion dr ON ma.duracion_id = dr.id";
        $countSql = "SELECT COUNT(*) FROM {$this->table}";

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
            $sql .= " WHERE " . implode(' AND ', $where);
            $countSql .= " WHERE " . implode(' AND ', $where);
        }

        // Obtener el total de registros filtrados (después de la búsqueda)
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($queryParams);
        $recordsFiltered = $stmt->fetchColumn();

        // Ordenación
        $orderColumnName = $columnMap[$orderColumnIndex] ?? 'ma.id'; // Columna por defecto si no se encuentra
        $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? $orderDir : 'asc';
        $sql .= " ORDER BY {$orderColumnName} {$orderDir}";

        // Paginación
        $sql .= " LIMIT :start, :length";
        $queryParams[':start'] = (int) $start;
        $queryParams[':length'] = (int) $length;

        error_log($sql);

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($queryParams);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener el total de registros sin filtrar (para 'recordsTotal')
        $totalRecordsStmt = $this->pdo->query("SELECT COUNT(*) FROM {$this->table}");
        $recordsTotal = $totalRecordsStmt->fetchColumn();

        return [
            'draw' => (int) $draw,
            'recordsTotal' => (int) $recordsTotal,
            'recordsFiltered' => (int) $recordsFiltered,
            'data' => $data, // Devolvemos los datos tal cual, el controlador los formateará para DataTables
        ];
    }

    /**
     * Obtiene un registro de maestría por su ID.
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
     * Crea un nuevo registro en maestría.
     * @param array $data Los datos del nuevo registro.
     * @return bool True si se creó correctamente, false en caso contrario.
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
     * @param int $id El ID del registro a actualizar.
     * @param array $data Los nuevos datos del registro.
     * @return bool True si se actualizó correctamente, false en caso contrario.
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
     * Elimina un registro de maestría.
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
