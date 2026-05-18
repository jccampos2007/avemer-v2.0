<?php
namespace App\Modules\Ciudad;

use App\Core\Controller;
use App\Core\Auth;

class CiudadController extends Controller
{
    private $ciudadModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->ciudadModel = new CiudadModel();
    }

    public function index(): void
    {
        $this->view('Ciudad/Views/list');
    }

    public function getData(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $params = $_POST;
            $data = $this->ciudadModel->getPaginated($params);
            header('Content-Type: application/json');
            echo json_encode($data);
            exit;
        }
    }

    public function create(): void
    {
        $this->view('Ciudad/Views/form');
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'pais_id' => (int)($_POST['pais_id'] ?? 1)
            ];

            if (empty($data['nombre'])) {
                header('Location: ' . BASE_URL . 'ciudad/create?error=empty_fields');
                exit;
            }

            if ($this->ciudadModel->create($data)) {
                header('Location: ' . BASE_URL . 'ciudad?success=created');
            } else {
                header('Location: ' . BASE_URL . 'ciudad/create?error=creation_failed');
            }
            exit;
        }
    }

    public function edit(int $id): void
    {
        $ciudad = $this->ciudadModel->findById($id);
        if (!$ciudad) {
            header('Location: ' . BASE_URL . 'ciudad?error=not_found');
            exit;
        }

        $this->view('Ciudad/Views/form', ['ciudad' => $ciudad]);
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'pais_id' => (int)($_POST['pais_id'] ?? 1)
            ];

            if (empty($data['nombre'])) {
                header('Location: ' . BASE_URL . 'ciudad/edit/' . $id . '?error=empty_fields');
                exit;
            }

            if ($this->ciudadModel->update($id, $data)) {
                header('Location: ' . BASE_URL . 'ciudad?success=updated');
            } else {
                header('Location: ' . BASE_URL . 'ciudad/edit/' . $id . '?error=update_failed');
            }
            exit;
        }
    }

    public function delete(int $id): void
    {
        header('Content-Type: application/json');
        
        if ($this->ciudadModel->delete($id)) {
            echo json_encode(['success' => true, 'message' => 'Ciudad eliminada correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar la ciudad.']);
        }
        exit;
    }
}
