<?php
// php_mvc_app/app/Modules/Docentes/docentesModel.php
namespace App\Modules\Docentes; // Nuevo namespace

use App\Core\Database;
use PDO;

class DocenteModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getAll(): array
    {
        // Se eliminan los campos calle_avenida, casa_apartamento, nombre_universidad, nombre_especialidad
        $stmt = $this->pdo->query("SELECT id, ci_pasapote, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, correo FROM docente ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    /**
     * Obtiene datos de Docentes para DataTables con paginación, búsqueda y ordenación.
     *
     * @param array $params Parámetros de DataTables (start, length, search, order, columns).
     * @return array Un array asociativo con 'data', 'recordsFiltered', 'recordsTotal'.
     */
    public function getPaginatedDocentes(array $params): array
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
            0 => 'id',
            1 => 'ci_pasapote',
            2 => 'primer_nombre', // Se usará para búsqueda combinada de nombre completo
            3 => 'correo',
        ];

        // Construir la consulta base
        // Se eliminan los campos calle_avenida, casa_apartamento, nombre_universidad, nombre_especialidad
        $sql = "SELECT id, foto, ci_pasapote, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, correo FROM docente";
        $countSql = "SELECT COUNT(*) FROM docente";
        $where = [];
        $queryParams = [];

        // Búsqueda global
        if (!empty($searchValue)) {
            $where[] = "(ci_pasapote LIKE :ci_pasapote "
                . "OR primer_nombre LIKE :primer_nombre "
                . "OR segundo_nombre LIKE :segundo_nombre "
                . "OR primer_apellido LIKE :primer_apellido "
                . "OR segundo_apellido LIKE :segundo_apellido "
                . "OR correo LIKE :correo)";
            $like = '%' . $searchValue . '%';
            $queryParams[':ci_pasapote'] = $like;
            $queryParams[':primer_nombre'] = $like;
            $queryParams[':segundo_nombre'] = $like;
            $queryParams[':primer_apellido'] = $like;
            $queryParams[':segundo_apellido'] = $like;
            $queryParams[':correo'] = $like;
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
        $orderColumnName = $columnMap[$orderColumnIndex] ?? 'id'; // Columna por defecto si no se encuentra
        $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? $orderDir : 'asc';

        // Ajuste para ordenar por nombre completo si la columna 2 es seleccionada
        if ($orderColumnName === 'primer_nombre') {
            $sql .= " ORDER BY primer_nombre {$orderDir}, primer_apellido {$orderDir}";
        } else {
            $sql .= " ORDER BY {$orderColumnName} {$orderDir}";
        }


        // Paginación
        $sql .= " LIMIT :start, :length";
        $queryParams[':start'] = (int) $start;
        $queryParams[':length'] = (int) $length;


        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($queryParams);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Formatear los datos para DataTables
        $formattedData = [];
        foreach ($data as $row) {
            $raw_blob = $row['foto'];
            $finfo = new \finfo(FILEINFO_MIME_TYPE); // Crea una nueva instancia de finfo
            $detected_mime_type = $finfo->buffer($raw_blob); // Detecta el MIME type del BLOB


            // Codifica el BLOB en Base64
            if ($raw_blob === null || $raw_blob === 'NO-IMAGE' || $detected_mime_type === false || !str_starts_with($detected_mime_type, 'image/')) {
                $foto_base64 = ''; // Si no hay foto, deja el campo vacío
            } else {
                $foto_base64 = 'data:' . $detected_mime_type . ';base64,' . base64_encode($raw_blob);
            }

            $formattedData[] = [
                $row['id'],
                $foto_base64,
                $row['ci_pasapote'],
                htmlspecialchars($row['primer_nombre'] . ' ' . $row['segundo_nombre'] . ' ' . $row['primer_apellido'] . ' ' . $row['segundo_apellido']),
                $row['correo'],
                ''
            ];
        }

        // Obtener el total de registros sin filtrar (para 'recordsTotal')
        $totalRecordsStmt = $this->pdo->query("SELECT COUNT(*) FROM docente");
        $recordsTotal = $totalRecordsStmt->fetchColumn();

        return [
            'draw' => (int) $draw,
            'recordsTotal' => (int) $recordsTotal,
            'recordsFiltered' => (int) $recordsFiltered,
            'data' => $formattedData,
        ];
    }

    public function findById(int $id): ?array
    {
        // Se mantiene SELECT * ya que no se especificó qué campos eliminar de la selección.
        // Si los campos se eliminan de la base de datos, SELECT * se adaptará automáticamente.
        $stmt = $this->pdo->prepare("SELECT * FROM docente WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $docente = $stmt->fetch();
        return $docente ?: null;
    }

    public function create(array $data): bool
    {
        // Se eliminan los campos calle_avenida, casa_apartamento, nombre_universidad, nombre_especialidad, chk_planilla, chk_cedula, chk_notas, chk_titulo, chk_partida
        // Se regresa el campo direccion
        $sql = "INSERT INTO docente (profesion_oficio_id, estado_id, nacionalidad_id, usuario_id, ci_pasapote, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, correo, tlf_habitacion, tlf_trabajo, tlf_celular, fecha_nacimiento, estatus_activo_id, direccion, foto, imagen) VALUES (:profesion_oficio_id, :estado_id, :nacionalidad_id, :usuario_id, :ci_pasapote, :primer_nombre, :segundo_nombre, :primer_apellido, :segundo_apellido, :correo, :tlf_habitacion, :tlf_trabajo, :tlf_celular, :fecha_nacimiento, :estatus_activo_id, :direccion, :foto, :imagen)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'profesion_oficio_id' => $data['profesion_oficio_id'],
            'estado_id' => $data['estado_id'],
            'nacionalidad_id' => $data['nacionalidad_id'],
            'usuario_id' => $_SESSION['user_id'],
            'ci_pasapote' => $data['ci_pasapote'],
            'primer_nombre' => $data['primer_nombre'],
            'segundo_nombre' => $data['segundo_nombre'],
            'primer_apellido' => $data['primer_apellido'],
            'segundo_apellido' => $data['segundo_apellido'],
            'correo' => $data['correo'],
            'tlf_habitacion' => $data['tlf_habitacion'],
            'tlf_trabajo' => $data['tlf_trabajo'],
            'tlf_celular' => $data['tlf_celular'],
            'fecha_nacimiento' => $data['fecha_nacimiento'],
            'estatus_activo_id' => $data['estatus_activo_id'],
            'direccion' => $data['direccion'],
            'foto' => $data['foto'],
            'imagen' => $data['imagen'],
        ]);
    }

    public function update(int $id, array $data): bool
    {
        // Se eliminan los campos calle_avenida, casa_apartamento, nombre_universidad, nombre_especialidad, chk_planilla, chk_cedula, chk_notas, chk_titulo, chk_partida
        // Se regresa el campo direccion
        $sql = "UPDATE docente SET profesion_oficio_id = :profesion_oficio_id, estado_id = :estado_id, nacionalidad_id = :nacionalidad_id, usuario_id = :usuario_id, ci_pasapote = :ci_pasapote, primer_nombre = :primer_nombre, segundo_nombre = :segundo_nombre, primer_apellido = :primer_apellido, segundo_apellido = :segundo_apellido, correo = :correo, tlf_habitacion = :tlf_habitacion, tlf_trabajo = :tlf_trabajo, tlf_celular = :tlf_celular, fecha_nacimiento = :fecha_nacimiento, estatus_activo_id = :estatus_activo_id, direccion = :direccion";

        // Solo actualiza BLOBs si se envió un nuevo archivo
        if ($data['foto'] !== null) $sql .= ", foto = :foto";
        if ($data['imagen'] !== null) $sql .= ", imagen = :imagen";

        $sql .= " WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        $params = [
            'profesion_oficio_id' => $data['profesion_oficio_id'],
            'estado_id' => $data['estado_id'],
            'nacionalidad_id' => $data['nacionalidad_id'],
            'usuario_id' => $_SESSION['user_id'],
            'ci_pasapote' => $data['ci_pasapote'],
            'primer_nombre' => $data['primer_nombre'],
            'segundo_nombre' => $data['segundo_nombre'],
            'primer_apellido' => $data['primer_apellido'],
            'segundo_apellido' => $data['segundo_apellido'],
            'correo' => $data['correo'],
            'tlf_habitacion' => $data['tlf_habitacion'],
            'tlf_trabajo' => $data['tlf_trabajo'],
            'tlf_celular' => $data['tlf_celular'],
            'fecha_nacimiento' => $data['fecha_nacimiento'],
            'estatus_activo_id' => $data['estatus_activo_id'],
            'direccion' => $data['direccion'],
            'id' => $id
        ];

        if ($data['foto'] !== null) $params['foto'] = $data['foto'];
        if ($data['imagen'] !== null) $params['imagen'] = $data['imagen'];

        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM docente WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
