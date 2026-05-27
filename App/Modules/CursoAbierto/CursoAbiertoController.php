<?php
// php_mvc_app/app/Modules/CursoAbierto/CursoAbiertoController.php
namespace App\Modules\CursoAbierto;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
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

    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $this->processForm();
        } else {
            $this->view('CursoAbierto/form', ['curso_abierto_data' => []]); // Ruta de vista relativa al módulo
        }
    }

    public function edit(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $this->processForm($id);
        } else {
            $curso_abierto_data = $this->cursoAbiertoModel->findById($id);
            if (!$curso_abierto_data) {
                Auth::setFlashMessage('error', 'Taller Abierto no encontrado.');
                $this->redirect('cursos_abiertos');
            }

            $pdo = Database::getInstance()->getConnection();

            $cursoNombre = '';
            if (!empty($curso_abierto_data['curso_id'])) {
                $stmt = $pdo->prepare("SELECT CONCAT(numero, ' - ', nombre) AS texto FROM curso WHERE id = :id");
                $stmt->execute(['id' => $curso_abierto_data['curso_id']]);
                $row = $stmt->fetch();
                $cursoNombre = $row['texto'] ?? '';
            }

            $docenteNombre = '';
            if (!empty($curso_abierto_data['docente_id'])) {
                $stmt = $pdo->prepare("SELECT CONCAT(primer_apellido, ', ', primer_nombre) AS texto FROM docente WHERE id = :id");
                $stmt->execute(['id' => $curso_abierto_data['docente_id']]);
                $row = $stmt->fetch();
                $docenteNombre = $row['texto'] ?? '';
            }

            // Obtener los alumnos inscritos asociados a este taller abierto
            $inscritos = $this->cursoAbiertoModel->getInscritos($id);
            
            $curso_abierto_data['curso_nombre'] = $cursoNombre;
            $curso_abierto_data['docente_nombre'] = $docenteNombre;

            $this->view('CursoAbierto/form', [
                'curso_abierto_data' => $curso_abierto_data,
                'inscritos' => $inscritos
            ]);
        }
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

    public function processForm(?int $id = null): void
    {
        $data = [
            'numero' => $this->sanitizeInput($_POST['numero']),
            'curso_id' => !empty($_POST['curso_id']) ? (int)$this->sanitizeInput($_POST['curso_id']) : null,
            'sede_id' => !empty($_POST['sede_id']) ? (int)$this->sanitizeInput($_POST['sede_id']) : null,
            'estatus_id' => !empty($_POST['estatus_id']) ? (int)$this->sanitizeInput($_POST['estatus_id']) : null,
            'docente_id' => !empty($_POST['docente_id']) ? (int)$this->sanitizeInput($_POST['docente_id']) : null,
            'fecha' => $this->sanitizeInput($_POST['fecha']),
            'nombre_carta' => $_POST['nombre_carta'],
            'convenio' => $this->sanitizeInput($_POST['convenio']),
        ];

        try {
            $success = false;
            if ($id) {
                // Actualizar
                $success = $this->cursoAbiertoModel->update($id, $data);
                $message = $success ? 'Taller Abierto actualizado correctamente.' : 'Error al actualizar el Taller Abierto.';
            } else {
                // Crear
                $success = $this->cursoAbiertoModel->create($data);
                $message = $success ? 'Registro de Taller Abierto creado con éxito.' : 'Error al crear el Registro de Taller Abierto.';
            }

            if ($success) {
                Auth::setFlashMessage('success', $message);
                $this->redirect('cursos_abiertos');
            } else {
                Auth::setFlashMessage('error', $message);
                $this->redirect('cursos_abiertos/edit/' . $id);
            }
        } catch (\PDOException $e) {
            Auth::setFlashMessage('error', 'Error de base de datos al actualizar Taller Abierto: ' . $e->getMessage());
            $redirectPath = $id ? 'cursos_abiertos/edit/' . $id : 'cursos_abiertos/create';
            $this->redirect($redirectPath);
        }
    }

    public function delete(int $id): void
    {
        // Para solicitudes AJAX de eliminación, responder con JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            try {
                if ($this->cursoAbiertoModel->delete($id)) {
                    echo json_encode(['success' => true, 'message' => 'Taller Abierto eliminado correctamente.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar el Taller Abierto.']);
                }
            } catch (\PDOException $e) {
                error_log("Error deleting Taller Abierto: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error de base de datos al eliminar Taller Abierto: ' . $e->getMessage()]);
            }
            exit(); // Terminar la ejecución para la solicitud AJAX
        } else {
            // Comportamiento original para solicitudes no-AJAX (redirección)
            try {
                if ($this->cursoAbiertoModel->delete($id)) {
                    Auth::setFlashMessage('success', 'Taller Abierto eliminado correctamente.');
                } else {
                    Auth::setFlashMessage('error', 'Error al eliminar el Taller Abierto.');
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos al eliminar Taller Abierto: ' . $e->getMessage());
            }
            $this->redirect('cursos_abiertos');
        }
    }
}