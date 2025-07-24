<?php
// php_mvc_app/app/Modules/Auth/Controllers/AuthController.php
namespace App\Modules\Auth; // Nuevo namespace

use App\Core\Controller;
use App\Core\Auth;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        // Si ya está logueado, redirigir al dashboard
        if (Auth::check()) {
            $this->redirect('dashboard');
        }
        $this->view('Auth/login', ['isLogin' => true]); // Ruta de vista relativa al módulo
    }

    public function processLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $this->sanitizeInput($_POST['username']);
            $password = $_POST['password']; // No sanitizar la contraseña antes de verificarla

            if (empty($username) || empty($password)) {
                Auth::setFlashMessage('error', 'Por favor, ingresa tu usuario y contraseña.');
                $this->redirect('login');
            }

            if (Auth::login($username, $password)) {
                Auth::setFlashMessage('success', '¡Bienvenido, ' . Auth::user('user_name') . '!');
                $this->redirect('dashboard');
            } else {
                Auth::setFlashMessage('error', 'Usuario o contraseña incorrectos.');
                $this->redirect('login');
            }
        } else {
            $this->redirect('login'); // Redirigir si no es un POST
        }
    }

    public function logout(): void
    {
        Auth::logout();
        Auth::setFlashMessage('success', 'Has cerrado sesión correctamente.');
        $this->redirect('login');
    }
}
