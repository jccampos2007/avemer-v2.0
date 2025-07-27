<?php
// app/Modules/Evento/EventoModel.php
namespace App\Modules\Evento;

use App\Core\Database; // Assumindo que você tem uma classe Database
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
     * Obtiene datos de Evento para DataTables con paginación, búsqueda y ordenación.
     *
     * @param array $params Parámetros de DataTables (start, length, search, order, columns).
     * @return array Un array asociativo con 'data', 'recordsFiltered', 'recordsTotal'.
     */
    public function getPaginatedEvento(array $params): array
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
            0 => 'e.id',
            1 => 'duracion_nombre', // Alias de la columna unida
            2 => 'e.nombre',
            3 => 'e.descripcion',
            4 => 'e.siglas',
            5 => 'e.costo',
            6 => 'e.inicial',
        ];

        // Construir la consulta base
        $sql = "
            SELECT
                e.id,
                e.duracion_id,
                d.nombre AS duracion_nombre, -- Asumimos 'nombre' es el campo a mostrar de la tabla 'duracion'
                e.nombre,
                e.descripcion,
                e.siglas,
                e.costo,
                e.inicial
            FROM
                {$this->table} e
            LEFT JOIN
                duracion d ON e.duracion_id = d.id -- Asumimos una tabla 'duracion'
        ";
        $countSql = "
            SELECT COUNT(*)
            FROM
                {$this->table} e
            LEFT JOIN
                duracion d ON e.duracion_id = d.id
        ";

        $where = [];
        $queryParams = [];

        // Búsqueda global
        if (!empty($searchValue)) {
            $where[] = "(e.nombre LIKE :search_nombre "
                . "OR e.descripcion LIKE :search_descripcion "
                . "OR e.siglas LIKE :search_siglas "
                . "OR e.costo LIKE :search_costo " // Cuidado con buscar float como string
                . "OR e.inicial LIKE :search_inicial " // Cuidado con buscar float como string
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
            $sql .= " WHERE " . implode(' AND ', $where);
            $countSql .= " WHERE " . implode(' AND ', $where);
        }

        // Obtener el total de registros filtrados (después de la búsqueda)
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($queryParams);
        $recordsFiltered = $stmt->fetchColumn();

        // Ordenación
        $orderColumnName = $columnMap[$orderColumnIndex] ?? 'e.id'; // Columna por defecto si no se encuentra
        $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? $orderDir : 'asc';
        $sql .= " ORDER BY {$orderColumnName} {$orderDir}";

        // Paginación
        $sql .= " LIMIT :start, :length";
        $queryParams[':start'] = (int) $start;
        $queryParams[':length'] = (int) $length;

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
     * Obtiene un registro de evento por su ID.
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
     * Crea un nuevo registro en evento.
     * @param array $data Los datos del nuevo registro.
     * @return bool True si se creó correctamente, false en caso contrario.
     */
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

    /**
     * Actualiza un registro existente en evento.
     * @param int $id El ID del registro a actualizar.
     * @param array $data Los nuevos datos del registro.
     * @return bool True si se actualizó correctamente, false en caso contrario.
     */
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
     * Elimina un registro de evento.
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
