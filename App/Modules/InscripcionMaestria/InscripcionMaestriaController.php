<?php
// app/Modules/InscripcionMaestria/InscripcionMaestriaController.php
namespace App\Modules\InscripcionMaestria;

use App\Core\Controller; // Asume que Controller provee los helpers (sanitizeInput, redirect, view)
use App\Core\Auth;
use App\Core\Database; // Necesario para obtener la conexión PDO
use App\Modules\InscripcionMaestria\InscripcionMaestriaModel;
use PDO;

class InscripcionMaestriaController extends Controller
{
    private $inscripcionMaestriaModel;

    public function __construct()
    {
        Auth::requireLogin(); // Requiere que el usuario esté logueado para usar este módulo
        $this->inscripcionMaestriaModel = new InscripcionMaestriaModel(Database::getInstance()->getConnection());
    }

    /**
     * Muestra la lista de registros de Inscripción de Maestría.
     */
    public function index(): void
    {
        $this->view('InscripcionMaestria/list'); // Ruta de vista relativa al módulo
    }

    /**
     * Procesa la solicitud AJAX para obtener los datos de Inscripción de Maestría para DataTables.
     */
    public function getInscripcionMaestriaData(): void
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
            $data = $this->inscripcionMaestriaModel->getPaginatedInscripcionMaestria($params);

            // DataTables espera el formato específico de las acciones en el lado del cliente
            // Por lo tanto, necesitamos añadir las acciones aquí para cada fila
            $formattedData = [];
            foreach ($data['data'] as $row) {
                // Los nombres de las columnas aquí deben coincidir con los aliases en la consulta SQL del modelo
                $formattedData[] = [
                    $row['id'], // Mantener el ID en los datos para referencia interna (ej. para botones de acción)
                    htmlspecialchars($row['maestria_abierto_numero'] ?? 'N/A'), // Número de Maestría Abierta
                    htmlspecialchars($row['alumno_nombre_completo'] ?? 'N/A'), // Nombre completo del Alumno
                    htmlspecialchars($row['estatus_inscripcion_nombre'] ?? 'N/A'), // Nombre del Estatus de Inscripción
                    // Columna para acciones (editar/eliminar) - será renderizada en el JS
                    ''
                ];
            }
            $data['data'] = $formattedData; // Asigna los datos formateados de vuelta al array de DataTables

            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        } catch (\PDOException $e) {
            error_log('Error de BD en getInscripcionMaestriaData (InscripcionMaestria): ' . $e->getMessage());
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
     * Exibe el formulario para crear un nuevo registro de Inscripción de Maestría
     * o procesa el envío del formulario.
     */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm();
        } else {
            $inscripcion_maestria_data = []; // Datos vacíos para el formulario
            $this->view('InscripcionMaestria/form', ['inscripcion_maestria_data' => $inscripcion_maestria_data]);
        }
    }

    /**
     * Exibe el formulario para editar un registro de Inscripción de Maestría existente
     * o procesa el envío del formulario.
     * @param int $id El ID del registro a editar.
     */
    public function edit(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm($id);
        } else {
            $inscripcion_maestria_data = $this->inscripcionMaestriaModel->getById($id);
            if (!$inscripcion_maestria_data) {
                Auth::setFlashMessage('error', 'Registro de Inscripción de Maestría no encontrado.');
                $this->redirect('inscripcion_maestria');
            }
            $this->view('InscripcionMaestria/form', ['inscripcion_maestria_data' => $inscripcion_maestria_data]);
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
                'maestria_abierto_id' => !empty($_POST['maestria_abierto_id']) ? (int)$this->sanitizeInput($_POST['maestria_abierto_id']) : null,
                'alumno_id' => !empty($_POST['alumno_id']) ? (int)$this->sanitizeInput($_POST['alumno_id']) : null,
                'estatus_inscripcion_id' => !empty($_POST['estatus_inscripcion_id']) ? (int)$this->sanitizeInput($_POST['estatus_inscripcion_id']) : null,
            ];

            // Validación de campos obligatorios
            if (empty($data['maestria_abierto_id']) || empty($data['alumno_id']) || empty($data['estatus_inscripcion_id'])) {
                Auth::setFlashMessage('error', 'Todos los campos son obligatorios.');
                $redirectPath = $id ? 'inscripcion_maestria/edit/' . $id : 'inscripcion_maestria/create';
                $this->redirect($redirectPath);
                return;
            }

            $success = false;
            if ($id) {
                // Actualizar
                $success = $this->inscripcionMaestriaModel->update($id, $data);
                $message = $success ? 'Registro de Inscripción de Maestría actualizado con éxito.' : 'Error al actualizar el Registro de Inscripción de Maestría.';
            } else {
                // Crear
                $success = $this->inscripcionMaestriaModel->create($data);
                $message = $success ? 'Registro de Inscripción de Maestría creado con éxito.' : 'Error al crear el Registro de Inscripción de Maestría.';
            }

            if ($success) {
                Auth::setFlashMessage('success', $message);
                $this->redirect('inscripcion_maestria'); // Redireccionar a la lista
            } else {
                Auth::setFlashMessage('error', $message);
                $redirectPath = $id ? 'inscripcion_maestria/edit/' . $id : 'inscripcion_maestria/create';
                $this->redirect($redirectPath); // Redireccionar de vuelta al formulario
            }
        } catch (\PDOException $e) {
            error_log('Error de BD en processForm (InscripcionMaestria): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Error de base de datos: ' . $e->getMessage());
            $redirectPath = $id ? 'inscripcion_maestria/edit/' . $id : 'inscripcion_maestria/create';
            $this->redirect($redirectPath);
        } catch (\Exception $e) {
            error_log('Error en processForm (InscripcionMaestria): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Ocurrió un error: ' . $e->getMessage());
            $redirectPath = $id ? 'inscripcion_maestria/edit/' . $id : 'inscripcion_maestria/create';
            $this->redirect($redirectPath);
        }
    }

    /**
     * Elimina un registro de Inscripción de Maestría.
     * @param int $id El ID del registro a eliminar.
     */
    public function delete(int $id): void
    {
        // Para solicitudes AJAX de eliminación, responder con JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            try {
                if ($this->inscripcionMaestriaModel->delete($id)) {
                    echo json_encode(['success' => true, 'message' => 'Registro de Inscripción de Maestría eliminado con éxito.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar el Registro de Inscripción de Maestría.']);
                }
            } catch (\PDOException $e) {
                error_log("Error al eliminar inscripcion_maestria: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error de base de datos al eliminar Inscripción de Maestría: ' . $e->getMessage()]);
            }
            exit(); // Terminar la ejecución para la solicitud AJAX
        } else {
            // Comportamiento original para solicitudes no-AJAX (redireccionamiento)
            try {
                if ($this->inscripcionMaestriaModel->delete($id)) {
                    Auth::setFlashMessage('success', 'Registro de Inscripción de Maestría eliminado con éxito.');
                } else {
                    Auth::setFlashMessage('error', 'Error al eliminar el Registro de Inscripción de Maestría.');
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos al eliminar Inscripción de Maestría: ' . $e->getMessage());
            }
            $this->redirect('inscripcion_maestria');
        }
    }
}
