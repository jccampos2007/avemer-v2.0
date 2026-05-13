<?php
// php_mvc_app/App/Modules/Banco/BancoController.php
namespace App\Modules\Banco;

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\Banco\BancoModel;

class BancoController extends Controller
{
    private $bancoModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->bancoModel = $this->model('Modules\Banco\BancoModel');
    }

    public function index(): void
    {
        $this->view('Banco/list');
    }

    public function getBancosData(): void
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
            $data = $this->bancoModel->getPaginatedBancos($params);
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
        $this->view('Banco/form', ['banco_data' => []]);
    }

    public function edit(int $id): void
    {
        $banco_data = $this->bancoModel->findById($id);
        if (!$banco_data) {
            Auth::setFlashMessage('error', 'Banco no encontrado.');
            $this->redirect('banco');
        }
        $this->view('Banco/form', ['banco_data' => $banco_data]);
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre' => $this->sanitizeInput($_POST['nombre'])
            ];

            try {
                if ($this->bancoModel->create($data)) {
                    Auth::setFlashMessage('success', 'Banco creado correctamente.');
                    $this->redirect('banco');
                } else {
                    Auth::setFlashMessage('error', 'Error al crear el banco.');
                    $this->redirect('banco/create');
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos: ' . $e->getMessage());
                $this->redirect('banco/create');
            }
        } else {
            $this->redirect('banco');
        }
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre' => $this->sanitizeInput($_POST['nombre'])
            ];

            try {
                if ($this->bancoModel->update($id, $data)) {
                    Auth::setFlashMessage('success', 'Banco actualizado correctamente.');
                    $this->redirect('banco');
                } else {
                    Auth::setFlashMessage('error', 'Error al actualizar el banco.');
                    $this->redirect('banco/edit/' . $id);
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos: ' . $e->getMessage());
                $this->redirect('banco/edit/' . $id);
            }
        } else {
            $this->redirect('banco');
        }
    }

    public function delete(int $id): void
    {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        try {
            if ($this->bancoModel->delete($id)) {
                if ($isAjax) {
                    echo json_encode(['success' => true, 'message' => 'Banco eliminado correctamente.']);
                    exit;
                }
                Auth::setFlashMessage('success', 'Banco eliminado correctamente.');
            } else {
                if ($isAjax) {
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar el banco.']);
                    exit;
                }
                Auth::setFlashMessage('error', 'Error al eliminar el banco.');
            }
        } catch (\PDOException $e) {
            if ($isAjax) {
                echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
                exit;
            }
            Auth::setFlashMessage('error', 'Error de base de datos: ' . $e->getMessage());
        }

        if (!$isAjax) {
            $this->redirect('banco');
        }
    }
}
