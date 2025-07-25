<?php
// php_mvc_app/app/Modules/Coordinadores/CoordinadorController.php
namespace App\Modules\Coordinadores; // Nuevo namespace

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\Coordinadores\CoordinadorModel; // Asegúrate de que el namespace sea correcto

class CoordinadorController extends Controller
{
    private $coordinadoresModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->coordinadoresModel = new CoordinadorModel(); // Ruta del modelo dentro del patrón
    }

    public function index(): void
    {
        $this->view('Coordinadores/list'); // Ruta de vista relativa al módulo
    }

    public function getCoordinadoresData(): void
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
            $data = $this->coordinadoresModel->getPaginatedCoordinadores($params);

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
        $this->view('Coordinadores/form', ['coordinador_data' => []]); // Ruta de vista relativa al módulo
    }

    public function edit(int $id): void
    {
        $coordinador_data = $this->coordinadoresModel->findById($id);
        if (!$coordinador_data) {
            Auth::setFlashMessage('error', 'Coordinador no encontrado.');
            $this->redirect('coordinadores');
        }
        $this->view('Coordinadores/form', ['coordinador_data' => $coordinador_data]); // Ruta de vista relativa al módulo
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                'foto' => null, // Por defecto null, se actualiza si se sube nuevo archivo
                'imagen' => null // Por defecto null, se actualiza si se sube nuevo archivo
            ];

            // Manejo de archivos BLOB (foto, imagen) para actualización
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $data['foto'] = file_get_contents($_FILES['foto']['tmp_name']);
            }
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $data['imagen'] = file_get_contents($_FILES['imagen']['tmp_name']);
            }

            try {
                if ($this->coordinadoresModel->update($id, $data)) {
                    Auth::setFlashMessage('success', 'Coordinador actualizado correctamente.');
                    $this->redirect('coordinadores');
                } else {
                    Auth::setFlashMessage('error', 'Error al actualizar el coordinador.');
                    $this->redirect('coordinadores/edit/' . $id);
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos al actualizar coordinador: ' . $e->getMessage());
                $this->redirect('coordinadores/edit/' . $id);
            }
        } else {
            $this->redirect('coordinadores');
        }
    }

    public function delete(int $id): void
    {
        // Para solicitudes AJAX de eliminación, responder con JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            try {
                if ($this->coordinadoresModel->delete($id)) {
                    echo json_encode(['success' => true, 'message' => 'Coordinador eliminado correctamente.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar el coordinador.']);
                }
            } catch (\PDOException $e) {
                error_log("Error deleting coordinador: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error de base de datos al eliminar coordinador: ' . $e->getMessage()]);
            }
            exit(); // Terminar la ejecución para la solicitud AJAX
        } else {
            // Comportamiento original para solicitudes no-AJAX (redirección)
            try {
                if ($this->coordinadoresModel->delete($id)) {
                    Auth::setFlashMessage('success', 'Coordinador eliminado correctamente.');
                } else {
                    Auth::setFlashMessage('error', 'Error al eliminar el coordinador.');
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos al eliminar coordinador: ' . $e->getMessage());
            }
            $this->redirect('coordinadores');
        }
    }
}
