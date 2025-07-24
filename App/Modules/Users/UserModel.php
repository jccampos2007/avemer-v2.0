<?php
// php_mvc_app/app/Modules/Users/Models/UserModel.php
namespace App\Modules\Users; // Nuevo namespace

use App\Core\Database;
use PDO;

class UserModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM usuario ORDER BY usuario_id DESC");
        return $stmt->fetchAll();
    }

    /**
     * Obtiene datos de alumnos para DataTables con paginación, búsqueda y ordenación.
     *
     * @param array $params Parámetros de DataTables (start, length, search, order, columns).
     * @return array Un array asociativo con 'data', 'recordsFiltered', 'recordsTotal'.
     */
    public function getPaginatedUsers(array $params): array
    {
        $draw = $params['draw'] ?? 1;
        $start = $params['start'] ?? 0;
        $length = $params['length'] ?? 10;
        $searchValue = $params['search']['value'] ?? '';
        $orderColumnIndex = $params['order'][0]['column'] ?? 0;
        $orderDir = $params['order'][0]['dir'] ?? 'asc';
        $columns = $params['columns'] ?? [];

        // Mapeo de índices de columna a nombres de columna reales en la base de datos
        // usuario_cedula, usuario_nombre, usuario_apellido, usuario_user
        $columnMap = [
            0 => 'usuario_id',
            1 => 'usuario_cedula',
            2 => 'usuario_nombre', // Se usará para búsqueda combinada de nombre completo
            3 => 'usuario_apellido',
            3 => 'usuario_user',
        ];

        // Construir la consulta base
        $sql = "SELECT usuario_id, usuario_cedula, usuario_nombre, usuario_apellido, usuario_user, tipo_usuario FROM usuario";
        $countSql = "SELECT COUNT(*) FROM usuario";
        $where = [];
        $queryParams = [];

        // Búsqueda global
        if (!empty($searchValue)) {
            $where[] = "(usuario_cedula LIKE :usuario_cedula "
                . "OR usuario_nombre LIKE :usuario_nombre "
                . "OR usuario_apellido LIKE :usuario_apellido "
                . "OR usuario_user LIKE :usuario_user "
                . "OR tipo_usuario LIKE :tipo_usuario)";
            $like = '%' . $searchValue . '%';
            $queryParams[':usuario_cedula'] = $like;
            $queryParams[':usuario_nombre'] = $like;
            $queryParams[':usuario_apellido'] = $like;
            $queryParams[':usuario_user'] = $like;
            $queryParams[':tipo_usuario'] = $like;
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
        $orderColumnName = $columnMap[$orderColumnIndex] ?? 'usuario_id'; // Columna por defecto si no se encuentra
        $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? $orderDir : 'asc';

        // Ajuste para ordenar por nombre completo si la columna 2 es seleccionada
        if ($orderColumnName === 'usuario_nombre') {
            $sql .= " ORDER BY usuario_nombre {$orderDir}, usuario_user {$orderDir}";
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

            $formattedData[] = [
                $row['usuario_id'],
                $row['usuario_cedula'],
                htmlspecialchars($row['usuario_nombre']  . ' ' . $row['usuario_apellido']),
                $row['usuario_user'],
                $row['tipo_usuario'] == 1 ? 'Usuario' : 'Alumno',
                ''
            ];
        }

        // Obtener el total de registros sin filtrar (para 'recordsTotal')
        $totalRecordsStmt = $this->pdo->query("SELECT COUNT(*) FROM usuario");
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
        $stmt = $this->pdo->prepare("SELECT * FROM usuario WHERE usuario_id = :id");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuario WHERE usuario_user = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO usuario (usuario_cedula, usuario_nombre, usuario_apellido, usuario_user, usuario_pws, estatus_activo_id, tipo_usuario, usuario_idreg, usuario_fechareg, id_persona) VALUES (:cedula, :nombre, :apellido, :user, :pws, :estatus_id, :tipo_usuario, :idreg, :fechareg, :id_persona)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'cedula' => $data['usuario_cedula'],
            'nombre' => $data['usuario_nombre'],
            'apellido' => $data['usuario_apellido'],
            'user' => $data['usuario_user'],
            'pws' => $data['usuario_pws'], // Ya debe venir hasheada
            'estatus_id' => $data['estatus_activo_id'],
            'tipo_usuario' => $data['tipo_usuario'],
            'idreg' => $data['usuario_idreg'],
            'fechareg' => $data['usuario_fechareg'],
            'id_persona' => $data['id_persona']
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE usuario SET usuario_cedula = :cedula, usuario_nombre = :nombre, usuario_apellido = :apellido, usuario_user = :user, estatus_activo_id = :estatus_id, tipo_usuario = :tipo_usuario, id_persona = :id_persona WHERE usuario_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            'cedula' => $data['usuario_cedula'],
            'nombre' => $data['usuario_nombre'],
            'apellido' => $data['usuario_apellido'],
            'user' => $data['usuario_user'],
            'estatus_id' => $data['estatus_activo_id'],
            'tipo_usuario' => $data['tipo_usuario'],
            'id_persona' => $data['id_persona'],
            'id' => $id
        ]);

        // Si se proporciona una nueva contraseña, actualizarla
        if (isset($data['usuario_pws']) && !empty($data['usuario_pws'])) {
            $sql_pws = "UPDATE usuario SET usuario_pws = :pws WHERE usuario_id = :id";
            $stmt_pws = $this->pdo->prepare($sql_pws);
            $stmt_pws->execute([
                'pws' => $data['usuario_pws'], // Ya debe venir hasheada
                'id' => $id
            ]);
        }
        return $result;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM usuario WHERE usuario_id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
