<?php
// php_mvc_app/app/Modules/Users/Controllers/UserController.php
namespace App\Modules\Users; // Nuevo namespace

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\Users\UserModel; // Ajustado para la nueva ubicación del modelo

class UserController extends Controller
{
    private $userModel;

    public function __construct()
    {
        Auth::requireLogin(); // Asegura que el usuario esté logueado para todas las acciones de usuario
        $this->userModel = $this->model('Modules\Users\UserModel'); // Ruta del modelo dentro del patrón
    }

    public function index(): void
    {
        $users = $this->userModel->getAll();
        $this->view('Users/list', ['users' => $users]); // Ruta de vista relativa al módulo
    }

    public function create(): void
    {
        $this->view('Users/form', ['user_data' => []]); // Ruta de vista relativa al módulo
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'usuario_cedula' => $this->sanitizeInput($_POST['usuario_cedula']),
                'usuario_nombre' => $this->sanitizeInput($_POST['usuario_nombre']),
                'usuario_apellido' => $this->sanitizeInput($_POST['usuario_apellido']),
                'usuario_user' => $this->sanitizeInput($_POST['usuario_user']),
                'usuario_pws' => $_POST['usuario_pws'], // Contraseña sin sanitizar aún
                'usuario_estatus_id' => (int)$this->sanitizeInput($_POST['usuario_estatus_id']),
                'tipo_usuario' => (int)$this->sanitizeInput($_POST['tipo_usuario']),
                'id_persona' => !empty($_POST['id_persona']) ? (int)$this->sanitizeInput($_POST['id_persona']) : null,
                'usuario_idreg' => Auth::user('user_id'), // Usuario que registra
                'usuario_fechareg' => date('Y-m-d H:i:s')
            ];

            if (empty($data['usuario_pws'])) {
                Auth::setFlashMessage('error', 'La contraseña no puede estar vacía al crear un usuario.');
                $this->redirect('users/create');
            }
            $data['usuario_pws'] = password_hash($data['usuario_pws'], PASSWORD_DEFAULT);

            try {
                if ($this->userModel->create($data)) {
                    Auth::setFlashMessage('success', 'Usuario creado correctamente.');
                    $this->redirect('users');
                } else {
                    Auth::setFlashMessage('error', 'Error al crear el usuario.');
                    $this->redirect('users/create');
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos al crear usuario: ' . $e->getMessage());
                $this->redirect('users/create');
            }
        } else {
            $this->redirect('users');
        }
    }

    public function edit(int $id): void
    {
        $user_data = $this->userModel->findById($id);
        if (!$user_data) {
            Auth::setFlashMessage('error', 'Usuario no encontrado.');
            $this->redirect('users');
        }
        $this->view('Users/form', ['user_data' => $user_data]); // Ruta de vista relativa al módulo
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'usuario_cedula' => $this->sanitizeInput($_POST['usuario_cedula']),
                'usuario_nombre' => $this->sanitizeInput($_POST['usuario_nombre']),
                'usuario_apellido' => $this->sanitizeInput($_POST['usuario_apellido']),
                'usuario_user' => $this->sanitizeInput($_POST['usuario_user']),
                'usuario_estatus_id' => (int)$this->sanitizeInput($_POST['usuario_estatus_id']),
                'tipo_usuario' => (int)$this->sanitizeInput($_POST['tipo_usuario']),
                'id_persona' => !empty($_POST['id_persona']) ? (int)$this->sanitizeInput($_POST['id_persona']) : null,
            ];

            // Si se proporciona una nueva contraseña, hashearla
            if (!empty($_POST['usuario_pws'])) {
                $data['usuario_pws'] = password_hash($_POST['usuario_pws'], PASSWORD_DEFAULT);
            }

            try {
                if ($this->userModel->update($id, $data)) {
                    Auth::setFlashMessage('success', 'Usuario actualizado correctamente.');
                    $this->redirect('users');
                } else {
                    Auth::setFlashMessage('error', 'Error al actualizar el usuario.');
                    $this->redirect('users/edit/' . $id);
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos al actualizar usuario: ' . $e->getMessage());
                $this->redirect('users/edit/' . $id);
            }
        } else {
            $this->redirect('users');
        }
    }

    public function delete(int $id): void
    {
        try {
            if ($this->userModel->delete($id)) {
                Auth::setFlashMessage('success', 'Usuario eliminado correctamente.');
            } else {
                Auth::setFlashMessage('error', 'Error al eliminar el usuario.');
            }
        } catch (\PDOException $e) {
            Auth::setFlashMessage('error', 'Error de base de datos al eliminar usuario: ' . $e->getMessage());
        }
        $this->redirect('users');
    }
}
