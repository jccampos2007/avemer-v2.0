<?php
// php_mvc_app/app/Modules/Cursos/CursoController.php
namespace App\Modules\Cursos;

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\Cursos\CursoModel; // Asegúrate de que el namespace sea correcto

class CursoController extends Controller
{
    private $cursosModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->cursosModel = new CursoModel(); // Ruta del modelo dentro del patrón
    }

    public function index(): void
    {
        $this->view('Cursos/list');
    }

    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm();
        } else {
            $this->view('Cursos/form', ['curso_data' => []]);
        }
    }

    public function edit(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm($id);
        } else {
            $curso_data = $this->cursosModel->findById($id);
            if (!$curso_data) {
                Auth::setFlashMessage('error', 'Curso no encontrado.');
                $this->redirect('cursos');
            }
            $this->view('Cursos/form', ['curso_data' => $curso_data]);
        }
    }

    public function getCursosData(): void
    {
        Auth::requireLogin(); // Asegura que el usuario esté logueado para acceder a los datos

        // Asegúrate de que la solicitud sea AJAX (opcional, pero buena práctica)
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['error' => 'Acceso denegado.']);
            exit();
        }

        // Obtener parámetros de DataTables
        $draw = $_POST['draw'] ?? 1;
        $start = $_POST['start'] ?? 0;
        $length = $_POST['length'] ?? 10;
        $searchValue = $_POST['search']['value'] ?? '';
        $orderColumnIndex = $_POST['order'][0]['column'] ?? 0;
        $orderDir = $_POST['order'][0]['dir'] ?? 'asc';
        $columns = $_POST['columns'] ?? [];

        error_log("searchValue: " . $searchValue . " orderColumnIndex: " . $orderColumnIndex . " orderDir: " . $orderDir . " length: " . $length . " start: " . $start);

        $params = [
            'draw' => $draw,
            'start' => $start,
            'length' => $length,
            'search' => ['value' => $searchValue],
            'order' => [['column' => $orderColumnIndex, 'dir' => $orderDir]],
            'columns' => $columns
        ];

        try {
            $data = $this->cursosModel->getPaginatedCursos($params);

            // DataTables espera el formato específico de las acciones en el lado del cliente
            // Por lo tanto, necesitamos añadir las acciones aquí para cada fila
            $formattedData = [];
            foreach ($data['data'] as $row) {
                $formattedData[] = $row;
            }
            $data['data'] = $formattedData;

            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        } catch (\PDOException $e) {
            // Return an error response to DataTables
            header('Content-Type: application/json');
            echo json_encode([
                'draw' => (int) $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error al cargar los datos: ' . $e->getMessage() // Mensaje de error para depuración
            ]);
            exit();
        }
    }

    public function processForm(?int $id = null): void
    {
        $data = [
            'nombre' => $this->sanitizeInput($_POST['nombre']),
            'numero' => $this->sanitizeInput($_POST['numero']),
            'horas' => (int)$this->sanitizeInput($_POST['horas']),
            'convenio' => $this->sanitizeInput($_POST['convenio']),
        ];

        try {
            $success = false;
            if ($id) {
                // Actualizar
                $success = $this->cursosModel->update($id, $data);
                $message = $success ? 'Taller actualizado correctamente.' : 'Error al actualizar el Taller.';
            } else {
                // Crear
                $success = $this->cursosModel->create($data);
                $message = $success ? 'Registro de Taller creado con éxito.' : 'Error al crear el Registro de Taller.';
            }

            if ($success) {
                Auth::setFlashMessage('success', $message);
                $this->redirect('cursos');
            } else {
                Auth::setFlashMessage('error', $message);
                $redirectPath = $id ? 'cursos/edit/' . $id : 'cursos/create';
                $this->redirect($redirectPath);
            }
        } catch (\PDOException $e) {
            Auth::setFlashMessage('error', 'Error de base de datos al actualizar curso: ' . $e->getMessage());
            $this->redirect('cursos/edit/' . $id);
        }
    }

    public function delete(int $id): void
    {
        // Para solicitudes AJAX de eliminación, responder con JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            try {
                if ($this->cursosModel->delete($id)) {
                    echo json_encode(['success' => true, 'message' => 'Curso eliminado correctamente.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar el curso.']);
                }
            } catch (\PDOException $e) {
                error_log("Error deleting curso: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error de base de datos al eliminar curso: ' . $e->getMessage()]);
            }
            exit(); // Terminar la ejecución para la solicitud AJAX
        } else {
            // Comportamiento original para solicitudes no-AJAX (redirección)
            try {
                if ($this->cursosModel->delete($id)) {
                    Auth::setFlashMessage('success', 'Curso eliminado correctamente.');
                } else {
                    Auth::setFlashMessage('error', 'Error al eliminar el curso.');
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos al eliminar curso: ' . $e->getMessage());
            }
            $this->redirect('cursos');
        }
    }
}
