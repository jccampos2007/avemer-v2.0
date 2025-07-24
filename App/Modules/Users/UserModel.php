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
        $sql = "INSERT INTO usuario (usuario_cedula, usuario_nombre, usuario_apellido, usuario_user, usuario_pws, usuario_estatus_id, tipo_usuario, usuario_idreg, usuario_fechareg, id_persona) VALUES (:cedula, :nombre, :apellido, :user, :pws, :estatus_id, :tipo_usuario, :idreg, :fechareg, :id_persona)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'cedula' => $data['usuario_cedula'],
            'nombre' => $data['usuario_nombre'],
            'apellido' => $data['usuario_apellido'],
            'user' => $data['usuario_user'],
            'pws' => $data['usuario_pws'], // Ya debe venir hasheada
            'estatus_id' => $data['usuario_estatus_id'],
            'tipo_usuario' => $data['tipo_usuario'],
            'idreg' => $data['usuario_idreg'],
            'fechareg' => $data['usuario_fechareg'],
            'id_persona' => $data['id_persona']
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE usuario SET usuario_cedula = :cedula, usuario_nombre = :nombre, usuario_apellido = :apellido, usuario_user = :user, usuario_estatus_id = :estatus_id, tipo_usuario = :tipo_usuario, id_persona = :id_persona WHERE usuario_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            'cedula' => $data['usuario_cedula'],
            'nombre' => $data['usuario_nombre'],
            'apellido' => $data['usuario_apellido'],
            'user' => $data['usuario_user'],
            'estatus_id' => $data['usuario_estatus_id'],
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
