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
            0 => 'u.usuario_id',
            1 => 'u.usuario_cedula',
            2 => 'u.usuario_nombre',
            3 => 'u.usuario_apellido',
            4 => 'u.usuario_user',
            5 => 'u.correo',
            6 => 'g.nombre_grupo'
        ];

        $sql = "SELECT u.usuario_id, u.usuario_cedula, u.usuario_nombre, u.usuario_apellido, u.usuario_user, u.correo, u.grupo_id, g.nombre_grupo FROM usuario u LEFT JOIN grupo g ON u.grupo_id = g.grupo_id";
        $countSql = "SELECT COUNT(*) FROM usuario u LEFT JOIN grupo g ON u.grupo_id = g.grupo_id";
        $where = [];
        $queryParams = [];

        // Búsqueda global
        if (!empty($searchValue)) {
            $where[] = "(u.usuario_cedula LIKE :usuario_cedula "
                . "OR u.usuario_nombre LIKE :usuario_nombre "
                . "OR u.usuario_apellido LIKE :usuario_apellido "
                . "OR u.usuario_user LIKE :usuario_user "
                . "OR g.nombre_grupo LIKE :nombre_grupo)";
            $like = '%' . $searchValue . '%';
            $queryParams[':usuario_cedula'] = $like;
            $queryParams[':usuario_nombre'] = $like;
            $queryParams[':usuario_apellido'] = $like;
            $queryParams[':usuario_user'] = $like;
            $queryParams[':nombre_grupo'] = $like;
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
        $orderColumnName = $columnMap[$orderColumnIndex] ?? 'u.usuario_id'; // Columna por defecto si no se encuentra
        $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? $orderDir : 'asc';

        // Ajuste para ordenar por nombre completo si la columna 2 es seleccionada
        if ($orderColumnName === 'u.usuario_nombre') {
            $sql .= " ORDER BY u.usuario_nombre {$orderDir}, u.usuario_user {$orderDir}";
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
                htmlspecialchars($row['correo'] ?? ''),
                $row['nombre_grupo'] ?? 'Sin Grupo',
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
        $sql = "SELECT u.*, g.nombre_grupo 
                FROM usuario u 
                LEFT JOIN grupo g ON u.grupo_id = g.grupo_id 
                WHERE u.usuario_user = :username";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO usuario (usuario_cedula, usuario_nombre, usuario_apellido, usuario_user, correo, usuario_pws, estatus_activo_id, grupo_id, usuario_idreg, usuario_fechareg, id_persona) VALUES (:cedula, :nombre, :apellido, :user, :correo, :pws, :estatus_id, :grupo_id, :idreg, :fechareg, :id_persona)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'cedula' => $data['usuario_cedula'],
            'nombre' => $data['usuario_nombre'],
            'apellido' => $data['usuario_apellido'],
            'user' => $data['usuario_user'],
            'correo' => $data['correo'] ?? null,
            'pws' => $data['usuario_pws'],
            'estatus_id' => $data['estatus_activo_id'] ?? 1,
            'grupo_id' => $data['grupo_id'],
            'idreg' => $data['usuario_idreg'],
            'fechareg' => $data['usuario_fechareg'],
            'id_persona' => $data['id_persona']
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE usuario SET usuario_cedula = :cedula, usuario_nombre = :nombre, usuario_apellido = :apellido, usuario_user = :user, correo = :correo, estatus_activo_id = :estatus_id, grupo_id = :grupo_id, id_persona = :id_persona WHERE usuario_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            'cedula' => $data['usuario_cedula'],
            'nombre' => $data['usuario_nombre'],
            'apellido' => $data['usuario_apellido'],
            'user' => $data['usuario_user'],
            'correo' => $data['correo'] ?? null,
            'estatus_id' => $data['estatus_activo_id'],
            'grupo_id' => $data['grupo_id'],
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

    public function findByUsernameOrEmail(string $input): ?array
    {
        $sql = "SELECT u.*, g.nombre_grupo 
                FROM usuario u 
                LEFT JOIN grupo g ON u.grupo_id = g.grupo_id 
                WHERE u.usuario_user = :input OR u.correo = :input2";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['input' => $input, 'input2' => $input]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function saveResetToken(int $userId, string $token): bool
    {
        $stmt = $this->pdo->prepare("UPDATE usuario SET token = :token WHERE usuario_id = :id");
        return $stmt->execute(['token' => $token, 'id' => $userId]);
    }

    public function clearResetToken(int $userId): bool
    {
        $stmt = $this->pdo->prepare("UPDATE usuario SET token = NULL WHERE usuario_id = :id");
        return $stmt->execute(['id' => $userId]);
    }

    public function updatePassword(int $userId, string $hashedPassword): bool
    {
        $stmt = $this->pdo->prepare("UPDATE usuario SET usuario_pws = :pws WHERE usuario_id = :id");
        return $stmt->execute(['pws' => $hashedPassword, 'id' => $userId]);
    }

    public function updateProfile(int $userId, array $data): bool
    {
        $fields = [];
        $params = ['id' => $userId];

        if (isset($data['usuario_nombre'])) {
            $fields[] = "usuario_nombre = :nombre";
            $params['nombre'] = $data['usuario_nombre'];
        }
        if (isset($data['usuario_apellido'])) {
            $fields[] = "usuario_apellido = :apellido";
            $params['apellido'] = $data['usuario_apellido'];
        }
        if (isset($data['correo'])) {
            $fields[] = "correo = :correo";
            $params['correo'] = $data['correo'];
        }
        if (array_key_exists('profile_image', $data)) {
            $fields[] = "profile_image = :profile_image";
            $params['profile_image'] = $data['profile_image'];
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE usuario SET " . implode(', ', $fields) . " WHERE usuario_id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
}
