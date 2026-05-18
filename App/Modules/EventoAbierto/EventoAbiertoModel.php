<?php
// app/Modules/EventoAbierto/EventoAbiertoModel.php
namespace App\Modules\EventoAbierto;

use App\Core\Database;
use PDO;

class EventoAbiertoModel
{
    private $pdo;
    private $table = 'evento_abierto';

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene datos de EventoAbierto para DataTables (Solo activos).
     */
    public function getPaginatedEventoAbierto(array $params): array
    {
        $draw = $params['draw'] ?? 1;
        $start = $params['start'] ?? 0;
        $length = $params['length'] ?? 10;
        $searchValue = $params['search']['value'] ?? '';
        $orderColumnIndex = $params['order'][0]['column'] ?? 0;
        $orderDir = $params['order'][0]['dir'] ?? 'asc';

        $columnMap = [
            0 => 'ea.id',
            1 => 'ea.numero',
            2 => 'evento_nombre',
            3 => 'sede_nombre',
            4 => 'estatus_nombre',
            5 => 'ea.fecha_inicio',
            6 => 'ea.fecha_fin',
            7 => 'ea.nombre_carta',
        ];

        // Filtro base: Excluir eliminados de forma lógica
        $sql = "
            SELECT
                ea.id,
                ea.numero,
                ea.evento_id,
                e.nombre AS evento_nombre,
                ea.sede_id,
                s.nombre AS sede_nombre,
                ea.estatus_id,
                st.nombre AS estatus_nombre,
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
                estatus st ON ea.estatus_id = st.id
            WHERE
                ea.deleted_at IS NULL
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
            WHERE
                ea.deleted_at IS NULL
        ";

        $where = [];
        $queryParams = [];

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
            $sql .= " AND " . implode(' AND ', $where);
            $countSql .= " AND " . implode(' AND ', $where);
        }

        // Ejecutar conteo de filtrados
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($queryParams);
        $recordsFiltered = $stmt->fetchColumn();

        // Ordenación
        $orderColumnName = $columnMap[$orderColumnIndex] ?? 'ea.id';
        $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? $orderDir : 'asc';
        $sql .= " ORDER BY {$orderColumnName} {$orderDir}";

        // Paginación
        $sql .= " LIMIT :start, :length";
        $queryParams[':start'] = (int) $start;
        $queryParams[':length'] = (int) $length;

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);
        foreach ($queryParams as $key => $val) {
            if ($key !== ':start' && $key !== ':length') {
                $stmt->bindValue($key, $val);
            }
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Conteo total de registros activos
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

    /**
     * Comprueba si una apertura tiene inscritos asociados.
     * Útil antes de permitir cualquier borrado.
     *
     * @param int $eventoAbiertoId ID de la apertura de evento.
     * @return int Cantidad de inscritos.
     */
    public function countInscritos(int $eventoAbiertoId): int
    {
        // Asumiendo que tu tabla de inscripciones se llama 'inscritos' y se relaciona con 'evento_abierto_id'
        $sql = "SELECT COUNT(*) FROM inscritos WHERE evento_abierto_id = :evento_abierto_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':evento_abierto_id', $eventoAbiertoId, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

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
     * Realiza el borrado lógico de la apertura de evento.
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