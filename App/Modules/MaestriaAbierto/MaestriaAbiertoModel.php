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
        $columns = $params['columns'] ?? [];

        // Mapeo de índices de columna a nombres de columna reales en la base de datos
        $columnMap = [
            0 => 'ma.id',
            1 => 'ma.numero',
            2 => 'maestria_nombre',   // Alias de la columna unida
            3 => 'sede_nombre',       // Alias de la columna unida
            4 => 'estatus_nombre',    // Alias de la columna unida
            5 => 'docente_nombre_completo', // Alias de la columna unida
            6 => 'ma.fecha',
            7 => 'ma.convenio',
            8 => 'ma.nombre_carta',
        ];

        // Construir la consulta base
        $sql = "
            SELECT
                ma.id,
                ma.numero,
                ma.maestria_id,
                m.nombre AS maestria_nombre, -- Asumimos 'nombre' es el campo a mostrar de la tabla 'maestria'
                ma.sede_id,
                s.nombre AS sede_nombre,   -- Asumimos 'nombre' es el campo a mostrar de la tabla 'sede'
                ma.estatus_id,
                e.nombre AS estatus_nombre, -- Asumimos 'nombre' es el campo a mostrar de la tabla 'estatus'
                ma.docente_id,
                CONCAT(d.primer_nombre, ' ', d.primer_apellido) AS docente_nombre_completo, -- Asumimos campos en tabla 'docente'
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
     * Obtiene un registro de maestria_abierto por su ID.
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
     * Crea un nuevo registro en maestria_abierto.
     * @param array $data Los datos del nuevo registro.
     * @return bool True si se creó correctamente, false en caso contrario.
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
     * @param int $id El ID del registro a actualizar.
     * @param array $data Los nuevos datos del registro.
     * @return bool True si se actualizó correctamente, false en caso contrario.
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
     * Elimina un registro de maestria_abierto.
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
