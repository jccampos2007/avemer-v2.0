<?php
// app/Modules/Grupo/GrupoModel.php
namespace App\Modules\Grupo;

use App\Core\Database;
use PDO;

class GrupoModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllGroups(): array
    {
        $stmt = $this->db->query("SELECT * FROM grupo ORDER BY grupo_id DESC");
        return $stmt->fetchAll();
    }

    public function getGroupById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM grupo WHERE grupo_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createGroup(array $data): int
    {
        $stmt = $this->db->prepare("INSERT INTO grupo (nombre_grupo, descripcion_grupo, usuario_idreg, grupo_fechareg) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $data['nombre_grupo'],
            $data['descripcion_grupo'],
            $data['usuario_idreg'],
            date('Y-m-d')
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updateGroup(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("UPDATE grupo SET nombre_grupo = ?, descripcion_grupo = ? WHERE grupo_id = ?");
        return $stmt->execute([
            $data['nombre_grupo'],
            $data['descripcion_grupo'],
            $id
        ]);
    }

    public function deleteGroup(int $id): bool
    {
        // Delete permissions first
        $stmt = $this->db->prepare("DELETE FROM permisos WHERE grupo_id = ?");
        $stmt->execute([$id]);

        $stmt = $this->db->prepare("DELETE FROM grupo WHERE grupo_id = ?");
        return $stmt->execute([$id]);
    }

    public function getPermissionsByGroup(int $grupo_id): array
    {
        // Join with ventana to get descriptions
        $sql = "SELECT p.*, v.ventana_titulo 
                FROM permisos p
                JOIN ventana v ON p.ventana_id = v.ventana_id
                WHERE p.grupo_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$grupo_id]);
        return $stmt->fetchAll();
    }

    public function getAllWindows(): array
    {
        return $this->db->query("SELECT * FROM ventana ORDER BY ventana_titulo ASC")->fetchAll();
    }

    public function syncPermissions(int $grupo_id, array $permissions, int $usuario_id): bool
    {
        $this->db->beginTransaction();
        try {
            // Delete existing permissions for this group
            $stmt = $this->db->prepare("DELETE FROM permisos WHERE grupo_id = ?");
            $stmt->execute([$grupo_id]);

            // Insert new permissions
            $sql = "INSERT INTO permisos (
                        grupo_id, ventana_id, aplicacion_id, 
                        permisos_crear, permisos_modificar, 
                        permisos_eliminar, permisos_listar, 
                        usuario_idreg, permisos_fechareg
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);

            foreach ($permissions as $p) {
                // Usamos filter_var para manejar correctamente los strings "true" y "false" que envía AJAX
                $stmt->execute([
                    $grupo_id,
                    $p['ventana_id'],
                    $p['aplicacion_id'] ?? 1,
                    filter_var($p['crear'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                    filter_var($p['modificar'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                    filter_var($p['eliminar'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                    filter_var($p['listar'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                    $usuario_id,
                    date('Y-m-d')
                ]);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Error syncing permissions: " . $e->getMessage());
            return false;
        }
    }
}
