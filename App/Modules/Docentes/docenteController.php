<?php
// app/Modules/docentes/DocenteController.php
namespace App\Modules\Docentes;

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\Docentes\DocenteModel;
use PDO; // Asegúrate de que PDO esté disponible

class DocenteController extends Controller
{
    private $docenteModel;

    public function __construct()
    {
        Auth::requireLogin();
        // Asumiendo que App\Core\Database::getInstance()->getConnection() retorna una instancia PDO
        $this->docenteModel = new DocenteModel();
    }

    /**
     * Muestra la lista de registros de Docente.
     */
    public function index(): void
    {
        $this->view('docentes/list'); // Ruta de vista relativa al módulo
    }

    /**
     * Exibe el formulario para crear un nuevo registro de Docente
     * o procesa el envío del formulario.
     */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm();
        } else {
            $docente_data = []; // Datos vacíos para el formulario
            $this->view('docentes/form', ['docente_data' => $docente_data]);
        }
    }

    /**
     * Exibe el formulario para editar un registro de Docente
     * o procesa el envío del formulario.
     * @param int $id El ID del registro a editar.
     */
    public function edit(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm($id);
        } else {
            $docente_data = $this->docenteModel->findById($id);
            if (!$docente_data) {
                Auth::setFlashMessage('error', 'Registro de Docente no encontrado.');
                $this->redirect('docentes');
            }
            $this->view('docentes/form', ['docente_data' => $docente_data]);
        }
    }

    /**
     * Procesa la solicitud AJAX para obtener los datos de Docente para DataTables.
     */
    public function getDocentesData(): void
    {
        Auth::requireLogin(); // Asegura que el usuario esté logueado para acceder a los datos

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
            $data = $this->docenteModel->getPaginatedDocentes($params);

            // Formatear los datos para DataTables (array de arrays)
            $formattedData = [];
            foreach ($data['data'] as $row) {
                $formattedData[] = [
                    $row[0],
                    $row[1],
                    htmlspecialchars($row[2]),
                    htmlspecialchars($row[3]),
                    htmlspecialchars($row[4] ?? 'N/A'),
                    ''
                ];
            }
            $data['data'] = $formattedData; // Asigna los datos formateados de vuelta al array de DataTables

            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        } catch (\PDOException $e) {
            error_log('Error de BD en getDocenteData (Docente): ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'draw' => (int) $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error al cargar los datos: ' . $e->getMessage()
            ]);
            exit();
        }
    }

    /**
     * Procesa los datos del formulario (crear o actualizar).
     * @param int|null $id El ID del registro si es una actualización, null si es una creación.
     */
    private function processForm(?int $id = null): void
    {
        try {
            // Validación básica y sanitización
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
                'direccion' => $this->sanitizeInput($_POST['direccion']),
                'tlf_habitacion' => $this->sanitizeInput($_POST['tlf_habitacion']),
                'tlf_trabajo' => $this->sanitizeInput($_POST['tlf_trabajo']),
                'tlf_celular' => $this->sanitizeInput($_POST['tlf_celular']),
                'estatus_activo_id' => !empty($_POST['estatus_activo_id']) ? (int)$this->sanitizeInput($_POST['estatus_activo_id']) : null,
                'fecha_nacimiento' => $this->sanitizeInput($_POST['fecha_nacimiento']),
            ];

            // Manejo de subida de archivos para 'foto' y 'imagen' (longblob)
            // Solo se actualizan si se proporciona un nuevo archivo
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $data['foto'] = file_get_contents($_FILES['foto']['tmp_name']);
            }
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $data['imagen'] = file_get_contents($_FILES['imagen']['tmp_name']);
            }

            // Validación de campos obligatorios
            if (empty($data['ci_pasapote']) || empty($data['primer_nombre']) || empty($data['primer_apellido'])) {
                Auth::setFlashMessage('error', 'Cédula/Pasaporte, Primer Nombre y Primer Apellido son obligatorios.');
                $redirectPath = $id ? 'docentes/edit/' . $id : 'docentes/create';
                $this->redirect($redirectPath);
                return;
            }

            $success = false;
            if ($id) {
                // Actualizar
                $success = $this->docenteModel->update($id, $data);
                $message = $success ? 'Registro de Docente actualizado con éxito.' : 'Error al actualizar el Registro de Docente.';
            } else {
                // Crear
                $success = $this->docenteModel->create($data);
                $message = $success ? 'Registro de Docente creado con éxito.' : 'Error al crear el Registro de Docente.';
            }

            if ($success) {
                Auth::setFlashMessage('success', $message);
                $this->redirect('docentes'); // Redirigir a la lista
            } else {
                Auth::setFlashMessage('error', $message);
                $redirectPath = $id ? 'docentes/edit/' . $id : 'docentes/create';
                $this->redirect($redirectPath); // Redirigir de vuelta al formulario
            }
        } catch (\PDOException $e) {
            error_log('Error de BD en processForm (Docente): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Error de base de datos: ' . $e->getMessage());
            $redirectPath = $id ? 'docentes/edit/' . $id : 'docentes/create';
            $this->redirect($redirectPath);
        } catch (\Exception $e) {
            error_log('Error en processForm (Docente): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Ocurrió un error: ' . $e->getMessage());
            $redirectPath = $id ? 'docentes/edit/' . $id : 'docentes/create';
            $this->redirect($redirectPath);
        }
    }

    /**
     * Elimina un registro de Docente.
     * @param int $id El ID del registro a eliminar.
     */
    public function delete(int $id): void
    {
        // Para solicitudes AJAX de eliminación, responder con JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            try {
                if ($this->docenteModel->delete($id)) {
                    echo json_encode(['success' => true, 'message' => 'Registro de Docente eliminado con éxito.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar el Registro de Docente.']);
                }
            } catch (\PDOException $e) {
                error_log("Error al eliminar docente: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error de base de datos al eliminar Docente: ' . $e->getMessage()]);
            }
            exit(); // Terminar la ejecución para la solicitud AJAX
        } else {
            // Comportamiento original para solicitudes no-AJAX (redireccionamiento)
            try {
                if ($this->docenteModel->delete($id)) {
                    Auth::setFlashMessage('success', 'Registro de Docente eliminado con éxito.');
                } else {
                    Auth::setFlashMessage('error', 'Error al eliminar el Registro de Docente.');
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos al eliminar Docente: ' . $e->getMessage());
            }
            $this->redirect('docentes');
        }
    }
}
