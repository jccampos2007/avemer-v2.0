<?php
// app/Modules/EventoAbierto/EventoAbiertoModel.php
namespace App\Modules\EventoAbierto;

use App\Core\Database;
use PDO;

class EventoAbiertoModel
{
    private $pdo;
    private $table = 'evento_abierto';

    public function __construct(PDO $pdo = null)
    {
        // Se preserva la inicialización de la conexión de forma segura
        $this->pdo = $pdo ?? Database::getInstance()->getConnection();
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
            5 => 'docente_nombre_completo',
            6 => 'ea.fecha_inicio',
            7 => 'ea.fecha_fin',
            8 => 'ea.nombre_carta',
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
                CONCAT(d.primer_apellido, ', ', d.primer_nombre) AS docente_nombre_completo,
                ea.fecha_inicio,
                ea.fecha_fin,
                ea.nombre_carta,
                ea.costo,
                ea.inicial
            FROM
                {$this->table} ea
            LEFT JOIN
                evento e ON ea.evento_id = e.id
            LEFT JOIN
                sede s ON ea.sede_id = s.id
            LEFT JOIN
                estatus st ON ea.estatus_id = st.id
            LEFT JOIN
                docente d ON ea.docente_id = d.id
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
            LEFT JOIN
                docente d ON ea.docente_id = d.id
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
                . "OR CONCAT(d.primer_apellido, ', ', d.primer_nombre) LIKE :search_docente_nombre "
                . "OR ea.fecha_inicio LIKE :search_fecha_inicio "
                . "OR ea.fecha_fin LIKE :search_fecha_fin "
                . "OR ea.nombre_carta LIKE :search_nombre_carta)";
            $like = '%' . $searchValue . '%';
            $queryParams[':search_numero'] = $like;
            $queryParams[':search_evento_nombre'] = $like;
            $queryParams[':search_sede_nombre'] = $like;
            $queryParams[':search_estatus_nombre'] = $like;
            $queryParams[':search_docente_nombre'] = $like;
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
        if ((int)$length !== -1) {
            $sql .= " LIMIT :start, :length";
            $queryParams[':start'] = (int) $start;
            $queryParams[':length'] = (int) $length;
        }

        $stmt = $this->pdo->prepare($sql);
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
     * Obtiene la lista de alumnos inscritos en este evento abierto específico.
     *
     * @param int $eventoAbiertoId ID de la apertura del evento.
     * @return array Alumnos inscritos con sus datos personales y estatus.
     */
    public function getInscritos(int $eventoAbiertoId): array
    {
        $sql = "SELECT 
                    ie.id AS inscripcion_id,
                    ie.fecha AS fecha_inscripcion,
                    a.ci_pasapote,
                    CONCAT(a.primer_nombre, ' ', COALESCE(a.segundo_nombre, ''), ' ', a.primer_apellido, ' ', COALESCE(a.segundo_apellido, '')) AS alumno_nombre,
                    a.correo,
                    ei.nombre AS estatus_inscripcion
                FROM inscripcion_evento ie
                INNER JOIN alumno a ON ie.alumno_id = a.id
                LEFT JOIN estatus_inscripcion ei ON ie.estatus_inscripcion_id = ei.id
                WHERE ie.evento_abierto_id = :evento_abierto_id
                ORDER BY ie.id DESC";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':evento_abierto_id', $eventoAbiertoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        // Se actualiza el nombre de la tabla a 'inscripcion_evento' de acuerdo al esquema real
        $sql = "SELECT COUNT(*) FROM inscripcion_evento WHERE evento_abierto_id = :evento_abierto_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':evento_abierto_id', $eventoAbiertoId, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (numero, evento_id, sede_id, estatus_id, docente_id, fecha_inicio, fecha_fin, nombre_carta, costo, inicial) VALUES (:numero, :evento_id, :sede_id, :estatus_id, :docente_id, :fecha_inicio, :fecha_fin, :nombre_carta, :costo, :inicial)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':numero' => $data['numero'],
            ':evento_id' => $data['evento_id'],
            ':sede_id' => $data['sede_id'],
            ':estatus_id' => $data['estatus_id'],
            ':docente_id' => $data['docente_id'],
            ':fecha_inicio' => $data['fecha_inicio'],
            ':fecha_fin' => $data['fecha_fin'],
            ':nombre_carta' => $data['nombre_carta'],
            ':costo' => $data['costo'],
            ':inicial' => $data['inicial']
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET numero = :numero, evento_id = :evento_id, sede_id = :sede_id, estatus_id = :estatus_id, docente_id = :docente_id, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, nombre_carta = :nombre_carta, costo = :costo, inicial = :inicial WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':numero' => $data['numero'],
            ':evento_id' => $data['evento_id'],
            ':sede_id' => $data['sede_id'],
            ':estatus_id' => $data['estatus_id'],
            ':docente_id' => $data['docente_id'],
            ':fecha_inicio' => $data['fecha_inicio'],
            ':fecha_fin' => $data['fecha_fin'],
            ':nombre_carta' => $data['nombre_carta'],
            ':costo' => $data['costo'],
            ':inicial' => $data['inicial'],
            ':id' => $id
        ]);
    }

    /**
     * Realiza el borrado lógico de la apertura de evento.
     */
    public function delete(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}