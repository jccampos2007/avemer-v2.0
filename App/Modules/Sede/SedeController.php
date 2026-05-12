<?php
// php_mvc_app/App/Modules/Sede/SedeController.php
namespace App\Modules\Sede;

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\Sede\SedeModel;

class SedeController extends Controller
{
    private $sedeModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->sedeModel = $this->model('Modules\Sede\SedeModel');
    }

    public function index(): void
    {
        $this->view('Sede/list');
    }

    public function getSedesData(): void
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
            $data = $this->sedeModel->getPaginatedSedes($params);
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
        $this->view('Sede/form', ['sede_data' => []]);
    }

    public function edit(int $id): void
    {
        $sede_data = $this->sedeModel->findById($id);
        if (!$sede_data) {
            Auth::setFlashMessage('error', 'Sede no encontrada.');
            $this->redirect('sede');
        }
        $this->view('Sede/form', ['sede_data' => $sede_data]);
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre' => $this->sanitizeInput($_POST['nombre']),
                'tlf_sede' => $this->sanitizeInput($_POST['tlf_sede']),
                'correo' => $this->sanitizeInput($_POST['correo']),
                'estado_id' => (int)$this->sanitizeInput($_POST['estado_id']),
            ];

            try {
                if ($this->sedeModel->create($data)) {
                    Auth::setFlashMessage('success', 'Sede creada correctamente.');
                    $this->redirect('sede');
                } else {
                    Auth::setFlashMessage('error', 'Error al crear la sede.');
                    $this->redirect('sede/create');
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos: ' . $e->getMessage());
                $this->redirect('sede/create');
            }
        } else {
            $this->redirect('sede');
        }
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre' => $this->sanitizeInput($_POST['nombre']),
                'tlf_sede' => $this->sanitizeInput($_POST['tlf_sede']),
                'correo' => $this->sanitizeInput($_POST['correo']),
                'estado_id' => (int)$this->sanitizeInput($_POST['estado_id']),
            ];

            try {
                if ($this->sedeModel->update($id, $data)) {
                    Auth::setFlashMessage('success', 'Sede actualizada correctamente.');
                    $this->redirect('sede');
                } else {
                    Auth::setFlashMessage('error', 'Error al actualizar la sede.');
                    $this->redirect('sede/edit/' . $id);
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos: ' . $e->getMessage());
                $this->redirect('sede/edit/' . $id);
            }
        } else {
            $this->redirect('sede');
        }
    }

    public function delete(int $id): void
    {
        try {
            if ($this->sedeModel->delete($id)) {
                Auth::setFlashMessage('success', 'Sede eliminada correctamente.');
            } else {
                Auth::setFlashMessage('error', 'Error al eliminar la sede.');
            }
        } catch (\PDOException $e) {
            Auth::setFlashMessage('error', 'Error de base de datos: ' . $e->getMessage());
        }
        $this->redirect('sede');
    }
}
