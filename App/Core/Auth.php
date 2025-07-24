<?php
// php_mvc_app/app/Core/Auth.php
namespace App\Core;

use App\Modules\Users\UserModel; // Ajustado para la nueva ubicación del modelo de usuario

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
