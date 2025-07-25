<?php
// php_mvc_app/app/Modules/CursoAbierto/CursoAbiertoController.php
namespace App\Modules\CursoAbierto;

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\CursoAbierto\CursoAbiertoModel; // Asegúrate de que el namespace sea correcto

class CursoAbiertoController extends Controller
{
    private $cursoAbiertoModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->cursoAbiertoModel = new CursoAbiertoModel(); // Ruta del modelo dentro del patrón
    }

    public function index(): void
    {
        $this->view('CursoAbierto/list'); // Ruta de vista relativa al módulo
    }

    public function getCursoAbiertoData(): void
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
            $data = $this->cursoAbiertoModel->getPaginatedCursoAbierto($params);

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

    public function create(): void
    {
        $this->view('CursoAbierto/form', ['curso_abierto_data' => []]); // Ruta de vista relativa al módulo
    }

    // public function store(): void
    // {
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $data = [
    //             'numero' => $this->sanitizeInput($_POST['numero']),
    //             'curso_id' => !empty($_POST['curso_id']) ? (int)$this->sanitizeInput($_POST['curso_id']) : null,
    //             'sede_id' => !empty($_POST['sede_id']) ? (int)$this->sanitizeInput($_POST['sede_id']) : null,
    //             'estatus_id' => !empty($_POST['estatus_id']) ? (int)$this->sanitizeInput($_POST['estatus_id']) : null,
    //             'docente_id' => !empty($_POST['docente_id']) ? (int)$this->sanitizeInput($_POST['docente_id']) : null,
    //             'fecha' => $this->sanitizeInput($_POST['fecha']),
    //             'nombre_carta' => $this->sanitizeInput($_POST['nombre_carta']),
    //             'convenio' => $this->sanitizeInput($_POST['convenio']),
    //         ];

    //         try {
    //             if ($this->cursoAbiertoModel->create($data)) {
    //                 Auth::setFlashMessage('success', 'Curso Abierto creado correctamente.');
    //                 $this->redirect('cursos_abiertos');
    //             } else {
    //                 Auth::setFlashMessage('error', 'Error al crear el Curso Abierto.');
    //                 $this->redirect('cursos_abiertos/create');
    //             }
    //         } catch (\PDOException $e) {
    //             Auth::setFlashMessage('error', 'Error de base de datos al crear Curso Abierto: ' . $e->getMessage());
    //             $this->redirect('cursos_abiertos/create');
    //         }
    //     } else {
    //         $this->redirect('cursos_abiertos');
    //     }
    // }

    public function edit(int $id): void
    {
        $curso_abierto_data = $this->cursoAbiertoModel->findById($id);
        if (!$curso_abierto_data) {
            Auth::setFlashMessage('error', 'Curso Abierto no encontrado.');
            $this->redirect('cursos_abiertos');
        }
        $this->view('CursoAbierto/form', ['curso_abierto_data' => $curso_abierto_data]); // Ruta de vista relativa al módulo
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'numero' => $this->sanitizeInput($_POST['numero']),
                'curso_id' => !empty($_POST['curso_id']) ? (int)$this->sanitizeInput($_POST['curso_id']) : null,
                'sede_id' => !empty($_POST['sede_id']) ? (int)$this->sanitizeInput($_POST['sede_id']) : null,
                'estatus_id' => !empty($_POST['estatus_id']) ? (int)$this->sanitizeInput($_POST['estatus_id']) : null,
                'docente_id' => !empty($_POST['docente_id']) ? (int)$this->sanitizeInput($_POST['docente_id']) : null,
                'fecha' => $this->sanitizeInput($_POST['fecha']),
                'nombre_carta' => $this->sanitizeInput($_POST['nombre_carta']),
                'convenio' => $this->sanitizeInput($_POST['convenio']),
            ];

            try {
                if ($this->cursoAbiertoModel->update($id, $data)) {
                    Auth::setFlashMessage('success', 'Curso Abierto actualizado correctamente.');
                    $this->redirect('cursos_abiertos');
                } else {
                    Auth::setFlashMessage('error', 'Error al actualizar el Curso Abierto.');
                    $this->redirect('cursos_abiertos/edit/' . $id);
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos al actualizar Curso Abierto: ' . $e->getMessage());
                $this->redirect('cursos_abiertos/edit/' . $id);
            }
        } else {
            $this->redirect('cursos_abiertos');
        }
    }

    public function delete(int $id): void
    {
        // Para solicitudes AJAX de eliminación, responder con JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            try {
                if ($this->cursoAbiertoModel->delete($id)) {
                    echo json_encode(['success' => true, 'message' => 'Curso Abierto eliminado correctamente.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar el Curso Abierto.']);
                }
            } catch (\PDOException $e) {
                error_log("Error deleting curso abierto: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error de base de datos al eliminar Curso Abierto: ' . $e->getMessage()]);
            }
            exit(); // Terminar la ejecución para la solicitud AJAX
        } else {
            // Comportamiento original para solicitudes no-AJAX (redirección)
            try {
                if ($this->cursoAbiertoModel->delete($id)) {
                    Auth::setFlashMessage('success', 'Curso Abierto eliminado correctamente.');
                } else {
                    Auth::setFlashMessage('error', 'Error al eliminar el Curso Abierto.');
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos al eliminar Curso Abierto: ' . $e->getMessage());
            }
            $this->redirect('cursos_abiertos');
        }
    }
}
