<?php
// php_mvc_app/app/Modules/Alumnos/Controllers/AlumnoController.php
namespace App\Modules\Alumnos;

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\Alumnos\AlumnoModel; // Asegúrate de que el namespace sea correcto

class AlumnoController extends Controller
{
    private $alumnoModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->alumnoModel = new AlumnoModel(); // Ruta del modelo dentro del patrón
    }

    public function index(): void
    {
        $this->view('Alumnos/list'); // Ruta de vista relativa al módulo
    }

    public function getAlumnosData(): void
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
            $data = $this->alumnoModel->getPaginatedAlumnos($params);

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

    /**
     * Exibe el formulario para crear un nuevo registro de Alumno
     * o procesa el envío del formulario.
     */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm();
        } else {
            $Alumno_data = []; // Datos vacíos para el formulario
            $this->view('Alumnos/form', ['Alumno_data' => $Alumno_data]);
        }
    }

    public function edit(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm($id);
        } else {
            $alumno_data = $this->alumnoModel->findById($id);
            if (!$alumno_data) {
                Auth::setFlashMessage('error', 'Alumno no encontrado.');
                $this->redirect('alumnos');
            }
            $this->view('Alumnos/form', ['alumno_data' => $alumno_data]); // Ruta de vista relativa al módulo
        }
    }

    public function processForm(?int $id = null): void
    {
        $data = [
            'profesion_oficio_id' => !empty($_POST['profesion_oficio_id']) ? (int)$this->sanitizeInput($_POST['profesion_oficio_id']) : null,
            'estado_id' => !empty($_POST['estado_id']) ? (int)$this->sanitizeInput($_POST['estado_id']) : null,
            'nacionalidad_id' => !empty($_POST['nacionalidad_id']) ? (int)$this->sanitizeInput($_POST['nacionalidad_id']) : null,
            'usuario_id' => !empty($_POST['usuario_id']) ? (int)$this->sanitizeInput($_POST['usuario_id']) : null,
            'ci_pasapote' => $this->sanitizeInput($_POST['ci_pasapote']),
            'primer_nombre' => $this->sanitizeInput($_POST['primer_nombre']),
            'segundo_nombre' => $this->sanitizeInput($_POST['segundo_nombre']),
            'primer_apellido' => $this->sanitizeInput($_POST['primer_apellido']),
            'segundo_apellido' => $this->sanitizeInput($_POST['segundo_apellido']),
            'correo' => $this->sanitizeInput($_POST['correo']),
            'tlf_habitacion' => $this->sanitizeInput($_POST['tlf_habitacion']),
            'tlf_trabajo' => $this->sanitizeInput($_POST['tlf_trabajo']),
            'tlf_celular' => $this->sanitizeInput($_POST['tlf_celular']),
            'calle_avenida' => $this->sanitizeInput($_POST['calle_avenida']),
            'casa_apartamento' => $this->sanitizeInput($_POST['casa_apartamento']),
            'fecha_nacimiento' => $this->sanitizeInput($_POST['fecha_nacimiento']),
            'estatus_activo_id' => !empty($_POST['estatus_activo_id']) ? (int)$this->sanitizeInput($_POST['estatus_activo_id']) : null,
            'direccion' => $this->sanitizeInput($_POST['direccion']),
            'chk_planilla' => isset($_POST['chk_planilla']) ? 1 : 0,
            'chk_cedula' => isset($_POST['chk_cedula']) ? 1 : 0,
            'chk_notas' => isset($_POST['chk_notas']) ? 1 : 0,
            'chk_titulo' => isset($_POST['chk_titulo']) ? 1 : 0,
            'chk_partida' => isset($_POST['chk_partida']) ? 1 : 0,
            'nombre_universidad' => $this->sanitizeInput($_POST['nombre_universidad']),
            'nombre_especialidad' => $this->sanitizeInput($_POST['nombre_especialidad']),
            'foto' => null,
            'imagen' => null
        ];

        // Manejo de archivos BLOB (foto, imagen) para actualización
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $data['foto'] = file_get_contents($_FILES['foto']['tmp_name']);
        }
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $data['imagen'] = file_get_contents($_FILES['imagen']['tmp_name']);
        }

        try {
            $success = false;
            if ($id) {
                $success = $this->alumnoModel->update($id, $data);
                $message = $success ? 'Registro de Alumno actualizado con éxito.' : 'Error al actualizar el Registro de Alumno.';
            } else {
                $success = $this->alumnoModel->create($data);
                $message = $success ? 'Registro de Alumno creado con éxito.' : 'Error al crear el Registro de Alumno.';
            }

            if ($success) {
                Auth::setFlashMessage('success', $message);
                $this->redirect('alumnos');
            } else {
                Auth::setFlashMessage('error', $message);
                $redirectPath = $id ? 'alumnos/edit/' . $id : 'alumnos/create';
                $this->redirect($redirectPath);
            }
        } catch (\PDOException $e) {
            Auth::setFlashMessage('error', 'Error de base de datos al actualizar alumno: ' . $e->getMessage());
            $this->redirect('alumnos/edit/' . $id);
        }
    }

    public function delete(int $id): void
    {
        // Para solicitudes AJAX de eliminación, responder con JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            try {
                if ($this->alumnoModel->delete($id)) {
                    echo json_encode(['success' => true, 'message' => 'Alumno eliminado correctamente.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar el alumno.']);
                }
            } catch (\PDOException $e) {
                error_log("Error deleting Alumno: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error de base de datos al eliminar alumno: ' . $e->getMessage()]);
            }
            exit(); // Terminar la ejecución para la solicitud AJAX
        } else {
            // Comportamiento original para solicitudes no-AJAX (redirección)
            try {
                if ($this->alumnoModel->delete($id)) {
                    Auth::setFlashMessage('success', 'Alumno eliminado correctamente.');
                } else {
                    Auth::setFlashMessage('error', 'Error al eliminar el alumno.');
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos al eliminar alumno: ' . $e->getMessage());
            }
            $this->redirect('alumnos');
        }
    }
}
