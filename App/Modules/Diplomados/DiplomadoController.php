<?php
// php_mvc_app/app/Modules/Diplomados/Controllers/DiplomadoController.php
namespace App\Modules\Diplomados; // Nuevo namespace

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\Diplomados\DiplomadoModel; // Ajustado para la nueva ubicación del modelo

class DiplomadoController extends Controller
{
    private $diplomadoModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->diplomadoModel = $this->model('Modules\Diplomados\DiplomadoModel'); // Ruta del modelo dentro del patrón
    }

    public function index(): void
    {
        $diplomados = $this->diplomadoModel->getAll();
        $this->view('Diplomados/list', ['diplomados' => $diplomados]); // Ruta de vista relativa al módulo
    }

    public function create(): void
    {
        $this->view('Diplomados/form', ['diplomado_data' => []]); // Ruta de vista relativa al módulo
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'duracion_id' => (int)$this->sanitizeInput($_POST['duracion_id']),
                'nombre' => $this->sanitizeInput($_POST['nombre']),
                'descripcion' => $this->sanitizeInput($_POST['descripcion']),
                'siglas' => $this->sanitizeInput($_POST['siglas']),
                'costo' => (float)$this->sanitizeInput($_POST['costo']),
                'inicial' => (float)$this->sanitizeInput($_POST['inicial'])
            ];

            try {
                if ($this->diplomadoModel->create($data)) {
                    Auth::setFlashMessage('success', 'Diplomado creado correctamente.');
                    $this->redirect('diplomados');
                } else {
                    Auth::setFlashMessage('error', 'Error al crear el diplomado.');
                    $this->redirect('diplomados/create');
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos al crear diplomado: ' . $e->getMessage());
                $this->redirect('diplomados/create');
            }
        } else {
            $this->redirect('diplomados');
        }
    }

    public function edit(int $id): void
    {
        $diplomado_data = $this->diplomadoModel->findById($id);
        if (!$diplomado_data) {
            Auth::setFlashMessage('error', 'Diplomado no encontrado.');
            $this->redirect('diplomados');
        }
        $this->view('Diplomados/form', ['diplomado_data' => $diplomado_data]); // Ruta de vista relativa al módulo
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'duracion_id' => (int)$this->sanitizeInput($_POST['duracion_id']),
                'nombre' => $this->sanitizeInput($_POST['nombre']),
                'descripcion' => $this->sanitizeInput($_POST['descripcion']),
                'siglas' => $this->sanitizeInput($_POST['siglas']),
                'costo' => (float)$this->sanitizeInput($_POST['costo']),
                'inicial' => (float)$this->sanitizeInput($_POST['inicial'])
            ];

            try {
                if ($this->diplomadoModel->update($id, $data)) {
                    Auth::setFlashMessage('success', 'Diplomado actualizado correctamente.');
                    $this->redirect('diplomados');
                } else {
                    Auth::setFlashMessage('error', 'Error al actualizar el diplomado.');
                    $this->redirect('diplomados/edit/' . $id);
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos al actualizar diplomado: ' . $e->getMessage());
                $this->redirect('diplomados/edit/' . $id);
            }
        } else {
            $this->redirect('diplomados');
        }
    }

    public function delete(int $id): void
    {
        try {
            if ($this->diplomadoModel->delete($id)) {
                Auth::setFlashMessage('success', 'Diplomado eliminado correctamente.');
            } else {
                Auth::setFlashMessage('error', 'Error al eliminar el diplomado.');
            }
        } catch (\PDOException $e) {
            Auth::setFlashMessage('error', 'Error de base de datos al eliminar diplomado: ' . $e->getMessage());
        }
        $this->redirect('diplomados');
    }
}
