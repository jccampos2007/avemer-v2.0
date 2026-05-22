<?php
// app/Modules/MaestriaAbierto/MaestriaAbiertoModel.php
namespace App\Modules\MaestriaAbierto;

use App\Core\Database; // Asumiendo que tienes una clase Database
use PDO;

class MaestriaAbiertoModel
{
    private $pdo;
    private $table = 'maestria_abierto';

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene datos de Maestría Abierta para DataTables con paginación, búsqueda y ordenación.
     * Excluye los registros que han sido borrados de forma lógica.
     *
     * @param array $params Parámetros de DataTables (start, length, search, order, columns).
     * @return array Un array asociativo con 'data', 'recordsFiltered', 'recordsTotal'.
     */
    public function getPaginatedMaestriaAbierto(array $params): array
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
            1 => 'ma.numero',
            2 => 'maestria_nombre',   
            3 => 'sede_nombre',       
            4 => 'estatus_nombre',    
            5 => 'docente_nombre_completo', 
            6 => 'ma.fecha',
            7 => 'ma.convenio',
            8 => 'ma.nombre_carta',
        ];

        // Construir la consulta filtrando solo los que no están eliminados (ma.deleted_at IS NULL)
        $sql = "
            SELECT
                ma.id,
                ma.numero,
                ma.maestria_id,
                m.nombre AS maestria_nombre,
                ma.sede_id,
                s.nombre AS sede_nombre,
                ma.estatus_id,
                e.nombre AS estatus_nombre,
                ma.docente_id,
                CONCAT(d.primer_nombre, ' ', d.primer_apellido) AS docente_nombre_completo,
                ma.fecha
            FROM
                {$this->table} ma
            LEFT JOIN
                maestria m ON ma.maestria_id = m.id
            LEFT JOIN
                sede s ON ma.sede_id = s.id
            LEFT JOIN
                estatus e ON ma.estatus_id = e.id
            LEFT JOIN
                docente d ON ma.docente_id = d.id
            WHERE
                ma.deleted_at IS NULL
        ";
        
        $countSql = "
            SELECT COUNT(*)
            FROM
                {$this->table} ma
            LEFT JOIN
                maestria m ON ma.maestria_id = m.id
            LEFT JOIN
                sede s ON ma.sede_id = s.id
            LEFT JOIN
                estatus e ON ma.estatus_id = e.id
            LEFT JOIN
                docente d ON ma.docente_id = d.id
            WHERE
                ma.deleted_at IS NULL
        ";

        $where = [];
        $queryParams = [];

        // Búsqueda global
        if (!empty($searchValue)) {
            $where[] = "(ma.numero LIKE :search_numero "
                . "OR m.nombre LIKE :search_maestria_nombre "
                . "OR s.nombre LIKE :search_sede_nombre "
                . "OR e.nombre LIKE :search_estatus_nombre "
                . "OR CONCAT(d.primer_nombre, ' ', d.primer_apellido) LIKE :search_docente_nombre "
                . "OR ma.fecha LIKE :search_fecha)";
            $like = '%' . $searchValue . '%';
            $queryParams[':search_numero'] = $like;
            $queryParams[':search_maestria_nombre'] = $like;
            $queryParams[':search_sede_nombre'] = $like;
            $queryParams[':search_estatus_nombre'] = $like;
            $queryParams[':search_docente_nombre'] = $like;
            $queryParams[':search_fecha'] = $like;
        }

        if (!empty($where)) {
            $sql .= " AND " . implode(' AND ', $where);
            $countSql .= " AND " . implode(' AND ', $where);
        }

        // Obtener el total de registros filtrados
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($queryParams);
        $recordsFiltered = $stmt->fetchColumn();

        // Ordenación
        $orderColumnName = $columnMap[$orderColumnIndex] ?? 'ma.id';
        $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? $orderDir : 'asc';
        $sql .= " ORDER BY {$orderColumnName} {$orderDir}";

        // Paginación con binds manuales por compatibilidad de tipos
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

        // Obtener el total de registros activos sin filtrar
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
     * Obtiene un registro activo de maestria_abierto por su ID.
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
     * Obtiene la lista de alumnos inscritos en esta maestría abierta específica.
     *
     * @param int $maestriaAbiertoId ID de la apertura de la maestría.
     * @return array Alumnos inscritos con sus datos personales y estatus.
     */
    public function getInscritos(int $maestriaAbiertoId): array
    {
        $sql = "SELECT 
                    im.id AS inscripcion_id,
                    im.fecha AS fecha_inscripcion,
                    a.ci_pasapote,
                    CONCAT(a.primer_nombre, ' ', COALESCE(a.segundo_nombre, ''), ' ', a.primer_apellido, ' ', COALESCE(a.segundo_apellido, '')) AS alumno_nombre,
                    a.correo,
                    ei.nombre AS estatus_inscripcion
                FROM inscripcion_maestria im
                INNER JOIN alumno a ON im.alumno_id = a.id
                LEFT JOIN estatus_inscripcion ei ON im.estatus_inscripcion_id = ei.id
                WHERE im.maestria_abierto_id = :maestria_abierto_id
                ORDER BY im.id DESC";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':maestria_abierto_id', $maestriaAbiertoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cuenta el número de alumnos inscritos en esta apertura de maestría para evitar su eliminación si ya posee alumnos.
     *
     * @param int $maestriaAbiertoId El ID de la apertura de maestría.
     * @return int Cantidad de inscripciones.
     */
    public function countInscritos(int $maestriaAbiertoId): int
    {
        $sql = "SELECT COUNT(*) FROM inscripcion_maestria WHERE maestria_abierto_id = :maestria_abierto_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':maestria_abierto_id', $maestriaAbiertoId, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /**
     * Crea un nuevo registro en maestria_abierto.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (numero, maestria_id, sede_id, estatus_id, docente_id, fecha, nombre_carta, convenio) VALUES (:numero, :maestria_id, :sede_id, :estatus_id, :docente_id, :fecha, :nombre_carta, :convenio)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':numero' => $data['numero'],
            ':maestria_id' => $data['maestria_id'],
            ':sede_id' => $data['sede_id'],
            ':estatus_id' => $data['estatus_id'],
            ':docente_id' => $data['docente_id'],
            ':fecha' => $data['fecha'],
            ':nombre_carta' => $data['nombre_carta'],
            ':convenio' => $data['convenio'] ?? null
        ]);
    }

    /**
     * Actualiza un registro existente en maestria_abierto.
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} SET numero = :numero, maestria_id = :maestria_id, sede_id = :sede_id, estatus_id = :estatus_id, docente_id = :docente_id, fecha = :fecha, nombre_carta = :nombre_carta, convenio = :convenio WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':numero' => $data['numero'],
            ':maestria_id' => $data['maestria_id'],
            ':sede_id' => $data['sede_id'],
            ':estatus_id' => $data['estatus_id'],
            ':docente_id' => $data['docente_id'],
            ':fecha' => $data['fecha'],
            ':nombre_carta' => $data['nombre_carta'],
            ':convenio' => $data['convenio'] ?? null,
            ':id' => $id
        ]);
    }

    /**
     * Realiza un BORRADO LÓGICO de la apertura de maestría.
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