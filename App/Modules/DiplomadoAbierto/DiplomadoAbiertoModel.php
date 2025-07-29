<?php
// app/Modules/DiplomadoAbierto/DiplomadoAbiertoModel.php
namespace App\Modules\DiplomadoAbierto;

use App\Core\Database; // Assumindo que você tem uma classe Database
use PDO;

class DiplomadoAbiertoModel
{
    private $pdo;
    private $table = 'diplomado_abierto';

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene datos de DiplomadoAbierto para DataTables con paginación, búsqueda y ordenación.
     *
     * @param array $params Parámetros de DataTables (start, length, search, order, columns).
     * @return array Un array asociativo con 'data', 'recordsFiltered', 'recordsTotal'.
     */
    public function getPaginatedDiplomadoAbierto(array $params): array
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
            0 => 'da.id',
            1 => 'da.numero',
            2 => 'diplomado_nombre', // Alias de la columna unida
            3 => 'sede_nombre',   // Alias de la columna unida
            4 => 'estatus_nombre', // Alias de la columna unida
            5 => 'da.fecha_inicio',
            6 => 'da.fecha_fin',
            7 => 'da.nombre_carta',
        ];

        // Construir la consulta base
        $sql = "
            SELECT
                da.id,
                da.numero,
                da.diplomado_id,
                d.nombre AS diplomado_nombre, -- Asumimos 'nombre' es el campo a mostrar de la tabla 'diplomado'
                da.sede_id,
                s.nombre AS sede_nombre,   -- Asumimos 'nombre' es el campo a mostrar de la tabla 'sede'
                da.estatus_id,
                st.nombre AS estatus_nombre, -- Asumimos 'nombre' es el campo a mostrar de la tabla 'estatus'
                da.fecha_inicio,
                da.fecha_fin,
                da.nombre_carta
            FROM
                {$this->table} da
            LEFT JOIN
                diplomado d ON da.diplomado_id = d.id
            LEFT JOIN
                sede s ON da.sede_id = s.id
            LEFT JOIN
                estatus st ON da.estatus_id = st.id
        ";
        $countSql = "
            SELECT COUNT(*)
            FROM
                {$this->table} da
            LEFT JOIN
                diplomado d ON da.diplomado_id = d.id
            LEFT JOIN
                sede s ON da.sede_id = s.id
            LEFT JOIN
                estatus st ON da.estatus_id = st.id
        ";

        $where = [];
        $queryParams = [];

        // Búsqueda global
        if (!empty($searchValue)) {
            $where[] = "(da.numero LIKE :search_numero "
                . "OR d.nombre LIKE :search_diplomado_nombre "
                . "OR s.nombre LIKE :search_sede_nombre "
                . "OR st.nombre LIKE :search_estatus_nombre "
                . "OR da.fecha_inicio LIKE :search_fecha_inicio "
                . "OR da.fecha_fin LIKE :search_fecha_fin "
                . "OR da.nombre_carta LIKE :search_nombre_carta)";
            $like = '%' . $searchValue . '%';
            $queryParams[':search_numero'] = $like;
            $queryParams[':search_diplomado_nombre'] = $like;
            $queryParams[':search_sede_nombre'] = $like;
            $queryParams[':search_estatus_nombre'] = $like;
            $queryParams[':search_fecha_inicio'] = $like;
            $queryParams[':search_fecha_fin'] = $like;
            $queryParams[':search_nombre_carta'] = $like;
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
        $orderColumnName = $columnMap[$orderColumnIndex] ?? 'da.id'; // Columna por defecto si no se encuentra
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
     * Obtiene un registro de diplomado_abierto por su ID.
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

    public function getAllWithRelatedNames()
    {
        $sql = "
            SELECT
                da.id,
                da.numero,
                da.diplomado_id,
                d.nombre AS diplomado_nombre,
                da.sede_id,
                s.nombre AS sede_nombre,
                da.estatus_id,
                st.nombre AS estatus_nombre,
                da.fecha_inicio,
                da.fecha_fin
            FROM
                {$this->table} da
            LEFT JOIN
                diplomado d ON da.diplomado_id = d.id
            LEFT JOIN
                sede s ON da.sede_id = s.id
            LEFT JOIN
                estatus st ON da.estatus_id = st.id
            WHERE da.estatus_id = 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    /**
     * Crea un nuevo registro en diplomado_abierto.
     * @param array $data Los datos del nuevo registro.
     * @return bool True si se creó correctamente, false en caso contrario.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (numero, diplomado_id, sede_id, estatus_id, fecha_inicio, fecha_fin, nombre_carta) VALUES (:numero, :diplomado_id, :sede_id, :estatus_id, :fecha_inicio, :fecha_fin, :nombre_carta)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':numero' => $data['numero'],
            ':diplomado_id' => $data['diplomado_id'],
            ':sede_id' => $data['sede_id'],
            ':estatus_id' => $data['estatus_id'],
            ':fecha_inicio' => $data['fecha_inicio'],
            ':fecha_fin' => $data['fecha_fin'],
            ':nombre_carta' => $data['nombre_carta']
        ]);
    }

    /**
     * Actualiza un registro existente en diplomado_abierto.
     * @param int $id El ID del registro a actualizar.
     * @param array $data Los nuevos datos del registro.
     * @return bool True si se actualizó correctamente, false en caso contrario.
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET numero = :numero, diplomado_id = :diplomado_id, sede_id = :sede_id, estatus_id = :estatus_id, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, nombre_carta = :nombre_carta WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':numero' => $data['numero'],
            ':diplomado_id' => $data['diplomado_id'],
            ':sede_id' => $data['sede_id'],
            ':estatus_id' => $data['estatus_id'],
            ':fecha_inicio' => $data['fecha_inicio'],
            ':fecha_fin' => $data['fecha_fin'],
            ':nombre_carta' => $data['nombre_carta'],
            ':id' => $id
        ]);
    }

    /**
     * Elimina un registro de diplomado_abierto.
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
