<?php
// php_mvc_app/app/Core/Auth.php
namespace App\Core;

use App\Modules\Users\UserModel;
use App\Core\Database;
use PDO;

class Auth
{
    public static function login(string $username, string $password): bool
    {
        $userModel = new UserModel();
        $user = $userModel->findByUsername($username);

        if ($user && password_verify($password, $user['usuario_pws'])) {
            $_SESSION['user_id'] = $user['usuario_id'];
            $_SESSION['username'] = $user['usuario_user'];
            $_SESSION['user_name'] = $user['usuario_nombre'];
            $_SESSION['user_type'] = $user['tipo_usuario'];
            $_SESSION['grupo_id'] = $user['grupo_id'];
            
            // Load permissions into session
            self::loadPermissions($user['grupo_id']);
            
            return true;
        }
        return false;
    }

    public static function logout(): void
    {
        session_unset();
        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function user(string $key = null)
    {
        if (self::check()) {
            if ($key) {
                return $_SESSION[$key] ?? null;
            }
            return $_SESSION; // Retorna todos los datos de sesión del usuario
        }
        return null;
    }

    public static function hasPermission(string $keyword, string $action = 'listar'): bool
    {
        if (!self::check()) return false;
        
        $permissions = $_SESSION['permissions'] ?? [];
        
        // Find permission by window keyword
        if (isset($permissions[$keyword])) {
            return (bool)($permissions[$keyword]['permisos_' . $action] ?? false);
        }

        return false;
    }

    private static function loadPermissions($grupo_id): void
    {
        if (!$grupo_id) {
            $_SESSION['permissions'] = [];
            return;
        }

        $db = Database::getInstance()->getConnection();
        $sql = "SELECT v.key_word, p.permisos_crear, p.permisos_modificar, p.permisos_eliminar, p.permisos_listar 
                FROM permisos p
                JOIN ventana v ON p.ventana_id = v.ventana_id
                WHERE p.grupo_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$grupo_id]);
        $perms = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $mapped = [];
        foreach ($perms as $p) {
            $mapped[$p['key_word']] = $p;
        }
        $_SESSION['permissions'] = $mapped;

        // Log the roles/permissions for debugging
        $logFile = '/var/www/html/php_mvc_app/logs/permissions.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] PERMISOS CARGADOS PARA GRUPO ID: " . $grupo_id . "\n" . print_r($mapped, true) . "\n" . str_repeat('-', 50) . "\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            self::setFlashMessage('error', 'Debes iniciar sesión para acceder a esta página.');
            header('Location: ' . BASE_URL . 'login');
            exit();
        }
    }

    public static function setFlashMessage(string $type, string $message): void
    {
        $_SESSION['flash_message'] = ['type' => $type, 'text' => $message];
    }

    public static function getFlashMessage(): ?array
    {
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            return $message;
        }
        return null;
    }
}
