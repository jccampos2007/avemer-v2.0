<?php
// php_mvc_app/App/Modules/Duracion/DuracionController.php
namespace App\Modules\Duracion;

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\Duracion\DuracionModel;

class DuracionController extends Controller
{
    private $duracionModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->duracionModel = $this->model('Modules\Duracion\DuracionModel');
    }

    public function index(): void
    {
        $this->view('Duracion/list');
    }

    public function getDuracionesData(): void
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
            $data = $this->duracionModel->getPaginatedDuraciones($params);
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
        $this->view('Duracion/form', ['duracion_data' => []]);
    }

    public function edit(int $id): void
    {
        $duracion_data = $this->duracionModel->findById($id);
        if (!$duracion_data) {
            Auth::setFlashMessage('error', 'Duración no encontrada.');
            $this->redirect('duracion');
        }
        $this->view('Duracion/form', ['duracion_data' => $duracion_data]);
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $data = ['nombre' => $this->sanitizeInput($_POST['nombre'])];

            try {
                if ($this->duracionModel->create($data)) {
                    Auth::setFlashMessage('success', 'Duración creada correctamente.');
                    $this->redirect('duracion');
                } else {
                    Auth::setFlashMessage('error', 'Error al crear la duración.');
                    $this->redirect('duracion/create');
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos: ' . $e->getMessage());
                $this->redirect('duracion/create');
            }
        } else {
            $this->redirect('duracion');
        }
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $data = ['nombre' => $this->sanitizeInput($_POST['nombre'])];

            try {
                if ($this->duracionModel->update($id, $data)) {
                    Auth::setFlashMessage('success', 'Duración actualizada correctamente.');
                    $this->redirect('duracion');
                } else {
                    Auth::setFlashMessage('error', 'Error al actualizar la duración.');
                    $this->redirect('duracion/edit/' . $id);
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos: ' . $e->getMessage());
                $this->redirect('duracion/edit/' . $id);
            }
        } else {
            $this->redirect('duracion');
        }
    }

    public function delete(int $id): void
    {
        $this->validateCsrf();
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        try {
            if ($this->duracionModel->delete($id)) {
                if ($isAjax) {
                    echo json_encode(['success' => true, 'message' => 'Duración eliminada correctamente.']);
                    exit;
                }
                Auth::setFlashMessage('success', 'Duración eliminada correctamente.');
            } else {
                if ($isAjax) {
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar la duración.']);
                    exit;
                }
                Auth::setFlashMessage('error', 'Error al eliminar la duración.');
            }
        } catch (\PDOException $e) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
                exit;
            }
            Auth::setFlashMessage('error', 'Error de base de datos: ' . $e->getMessage());
        }

        if (!$isAjax) {
            $this->redirect('duracion');
        }
    }
}
