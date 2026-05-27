<?php
// php_mvc_app/App/Modules/ProfesionOficio/ProfesionOficioController.php
namespace App\Modules\ProfesionOficio;

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\ProfesionOficio\ProfesionOficioModel;

class ProfesionOficioController extends Controller
{
    private $profesionModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->profesionModel = $this->model('Modules\ProfesionOficio\ProfesionOficioModel');
    }

    public function index(): void
    {
        $this->view('ProfesionOficio/list');
    }

    public function getProfesionesData(): void
    {
        Auth::requireLogin();

        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['error' => 'Acceso denegado.']);
            exit();
        }

        $params = [
            'draw' => $_POST['draw'] ?? 1,
            'start' => $_POST['start'] ?? 0,
            'length' => $_POST['length'] ?? 10,
            'search' => $_POST['search'] ?? ['value' => ''],
            'order' => $_POST['order'] ?? [['column' => 0, 'dir' => 'asc']],
            'columns' => $_POST['columns'] ?? []
        ];

        try {
            $data = $this->profesionModel->getPaginatedProfesiones($params);
            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        } catch (\PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'draw' => (int) ($params['draw'] ?? 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error al cargar los datos: ' . $e->getMessage()
            ]);
            exit();
        }
    }

    public function create(): void
    {
        $this->view('ProfesionOficio/form', ['profesion_data' => []]);
    }

    public function edit(int $id): void
    {
        $profesion_data = $this->profesionModel->findById($id);
        if (!$profesion_data) {
            Auth::setFlashMessage('error', 'Profesión u Oficio no encontrado.');
            $this->redirect('profesion_oficio');
        }
        $this->view('ProfesionOficio/form', ['profesion_data' => $profesion_data]);
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $data = ['nombre' => $this->sanitizeInput($_POST['nombre'])];

            try {
                if ($this->profesionModel->create($data)) {
                    Auth::setFlashMessage('success', 'Profesión u Oficio creado correctamente.');
                    $this->redirect('profesion_oficio');
                } else {
                    Auth::setFlashMessage('error', 'Error al crear la profesión u oficio.');
                    $this->redirect('profesion_oficio/create');
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos: ' . $e->getMessage());
                $this->redirect('profesion_oficio/create');
            }
        } else {
            $this->redirect('profesion_oficio');
        }
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $data = ['nombre' => $this->sanitizeInput($_POST['nombre'])];

            try {
                if ($this->profesionModel->update($id, $data)) {
                    Auth::setFlashMessage('success', 'Profesión u Oficio actualizado correctamente.');
                    $this->redirect('profesion_oficio');
                } else {
                    Auth::setFlashMessage('error', 'Error al actualizar la profesión u oficio.');
                    $this->redirect('profesion_oficio/edit/' . $id);
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos: ' . $e->getMessage());
                $this->redirect('profesion_oficio/edit/' . $id);
            }
        } else {
            $this->redirect('profesion_oficio');
        }
    }

    public function delete(int $id): void
    {
        $this->validateCsrf();
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        try {
            if ($this->profesionModel->delete($id)) {
                if ($isAjax) {
                    echo json_encode(['success' => true, 'message' => 'Profesión u Oficio eliminado correctamente.']);
                    exit;
                }
                Auth::setFlashMessage('success', 'Profesión u Oficio eliminado correctamente.');
            } else {
                if ($isAjax) {
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar la profesión u oficio.']);
                    exit;
                }
                Auth::setFlashMessage('error', 'Error al eliminar la profesión u oficio.');
            }
        } catch (\PDOException $e) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
                exit;
            }
            Auth::setFlashMessage('error', 'Error de base de datos: ' . $e->getMessage());
        }

        if (!$isAjax) {
            $this->redirect('profesion_oficio');
        }
    }
}
