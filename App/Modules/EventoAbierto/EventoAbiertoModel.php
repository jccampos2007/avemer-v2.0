<?php
// app/Modules/EventoAbierto/EventoAbiertoModel.php
namespace App\Modules\EventoAbierto;

use App\Core\Database; // Assumindo que você tem uma classe Database
use PDO;

class EventoAbiertoModel
{
    private $pdo;
    private $table = 'evento_abierto';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene datos de EventoAbierto para DataTables con paginación, búsqueda y ordenación.
     *
     * @param array $params Parámetros de DataTables (start, length, search, order, columns).
     * @return array Un array asociativo con 'data', 'recordsFiltered', 'recordsTotal'.
     */
    public function getPaginatedEventoAbierto(array $params): array
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
            0 => 'ea.id',
            1 => 'ea.numero',
            2 => 'evento_nombre', // Alias de la columna unida
            3 => 'sede_nombre',   // Alias de la columna unida
            4 => 'estatus_nombre', // Alias de la columna unida
            5 => 'ea.fecha_inicio',
            6 => 'ea.fecha_fin',
            7 => 'ea.nombre_carta',
        ];

        // Construir la consulta base
        $sql = "
            SELECT
                ea.id,
                ea.numero,
                ea.evento_id,
                e.nombre AS evento_nombre, -- Asumimos 'nombre' es el campo a mostrar de la tabla 'evento'
                ea.sede_id,
                s.nombre AS sede_nombre,   -- Asumimos 'nombre' es el campo a mostrar de la tabla 'sede'
                ea.estatus_id,
                st.nombre AS estatus_nombre, -- Asumimos 'nombre' es el campo a mostrar de la tabla 'estatus'
                ea.fecha_inicio,
                ea.fecha_fin,
                ea.nombre_carta
            FROM
                {$this->table} ea
            LEFT JOIN
                evento e ON ea.evento_id = e.id
            LEFT JOIN
                sede s ON ea.sede_id = s.id
            LEFT JOIN
                estatus st ON ea.estatus_id = st.id -- Usamos 'st' como alias para evitar conflicto con 'e' de evento
        ";
        $countSql = "
            SELECT COUNT(*)
            FROM
                {$this->table} ea
            LEFT JOIN
                evento e ON ea.evento_id = e.id
            LEFT JOIN
                sede s ON ea.sede_id = s.id
            LEFT JOIN
                estatus st ON ea.estatus_id = st.id
        ";

        $where = [];
        $queryParams = [];

        // Búsqueda global
        if (!empty($searchValue)) {
            $where[] = "(ea.numero LIKE :search_numero "
                . "OR e.nombre LIKE :search_evento_nombre "
                . "OR s.nombre LIKE :search_sede_nombre "
                . "OR st.nombre LIKE :search_estatus_nombre "
                . "OR ea.fecha_inicio LIKE :search_fecha_inicio "
                . "OR ea.fecha_fin LIKE :search_fecha_fin "
                . "OR ea.nombre_carta LIKE :search_nombre_carta)";
            $like = '%' . $searchValue . '%';
            $queryParams[':search_numero'] = $like;
            $queryParams[':search_evento_nombre'] = $like;
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
        $orderColumnName = $columnMap[$orderColumnIndex] ?? 'ea.id'; // Columna por defecto si no se encuentra
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
     * Obtiene un registro de evento_abierto por su ID.
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
     * Crea un nuevo registro en evento_abierto.
     * @param array $data Los datos del nuevo registro.
     * @return bool True si se creó correctamente, false en caso contrario.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (numero, evento_id, sede_id, estatus_id, fecha_inicio, fecha_fin, nombre_carta) VALUES (:numero, :evento_id, :sede_id, :estatus_id, :fecha_inicio, :fecha_fin, :nombre_carta)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':numero' => $data['numero'],
            ':evento_id' => $data['evento_id'],
            ':sede_id' => $data['sede_id'],
            ':estatus_id' => $data['estatus_id'],
            ':fecha_inicio' => $data['fecha_inicio'],
            ':fecha_fin' => $data['fecha_fin'],
            ':nombre_carta' => $data['nombre_carta']
        ]);
    }

    /**
     * Actualiza un registro existente en evento_abierto.
     * @param int $id El ID del registro a actualizar.
     * @param array $data Los nuevos datos del registro.
     * @return bool True si se actualizó correctamente, false en caso contrario.
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET numero = :numero, evento_id = :evento_id, sede_id = :sede_id, estatus_id = :estatus_id, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, nombre_carta = :nombre_carta WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':numero' => $data['numero'],
            ':evento_id' => $data['evento_id'],
            ':sede_id' => $data['sede_id'],
            ':estatus_id' => $data['estatus_id'],
            ':fecha_inicio' => $data['fecha_inicio'],
            ':fecha_fin' => $data['fecha_fin'],
            ':nombre_carta' => $data['nombre_carta'],
            ':id' => $id
        ]);
    }

    /**
     * Elimina un registro de evento_abierto.
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
