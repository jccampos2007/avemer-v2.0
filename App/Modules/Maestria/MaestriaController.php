<?php
// app/Modules/Maestria/MaestriaController.php
namespace App\Modules\Maestria;

use App\Core\Controller; // Asume que Controller provee los helpers (sanitizeInput, redirect, view)
use App\Core\Auth;
use App\Core\Database; // Necesario para obtener la conexión PDO
use App\Modules\Maestria\MaestriaModel;
use PDO;

class MaestriaController extends Controller
{
    private $maestriaModel;

    public function __construct()
    {
        Auth::requireLogin(); // Requiere que el usuario esté logueado para usar este módulo
        $this->maestriaModel = new MaestriaModel(Database::getInstance()->getConnection());
    }

    /**
     * Muestra la lista de registros de Maestría.
     */
    public function index(): void
    {
        $this->view('Maestria/list'); // Ruta de vista relativa al módulo
    }

    /**
     * Procesa la solicitud AJAX para obtener los datos de Maestría para DataTables.
     */
    public function getMaestriaData(): void
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
            $data = $this->maestriaModel->getPaginatedMaestria($params);

            // DataTables espera el formato específico de las acciones en el lado del cliente
            // Por lo tanto, necesitamos añadir las acciones aquí para cada fila
            $formattedData = [];
            foreach ($data['data'] as $row) {
                // Los nombres de las columnas aquí deben coincidir con los de la consulta SQL del modelo
                $formattedData[] = [
                    $row['id'],
                    htmlspecialchars($row['nombre']),
                    $row['numero'],
                    $row['duracion_nombre'],
                    htmlspecialchars($row['convenio']),
                    // Columna para acciones (editar/eliminar) - será renderizada en el JS
                    ''
                ];
            }
            $data['data'] = $formattedData; // Asigna los datos formateados de vuelta al array de DataTables

            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        } catch (\PDOException $e) {
            error_log('Error de BD en getMaestriaData (Maestria): ' . $e->getMessage());
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
     * Exibe el formulario para crear un nuevo registro de Maestría
     * o procesa el envío del formulario.
     */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm();
        } else {
            $maestria_data = []; // Datos vacíos para el formulario
            $this->view('Maestria/form', ['maestria_data' => $maestria_data]);
        }
    }

    /**
     * Exibe el formulario para editar un registro de Maestría existente
     * o procesa el envío del formulario.
     * @param int $id El ID del registro a editar.
     */
    public function edit(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm($id);
        } else {
            $maestria_data = $this->maestriaModel->getById($id);
            if (!$maestria_data) {
                Auth::setFlashMessage('error', 'Registro de Maestría no encontrado.');
                $this->redirect('maestria');
            }
            $this->view('Maestria/form', ['maestria_data' => $maestria_data]);
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
                'nombre' => $this->sanitizeInput($_POST['nombre']),
                'numero' => $this->sanitizeInput($_POST['numero']),
                'duracion_id' => !empty($_POST['duracion_id']) ? (int)$this->sanitizeInput($_POST['duracion_id']) : 0,
                'convenio' => $this->sanitizeInput($_POST['convenio']),
            ];

            // Validación de campos obligatorios
            if (empty($data['nombre']) || empty($data['duracion_id'])) {
                Auth::setFlashMessage('error', 'Nombre y Duracion son campos obligatorios.');
                $redirectPath = $id ? 'maestria/edit/' . $id : 'maestria/create';
                $this->redirect($redirectPath);
                return;
            }

            $success = false;
            if ($id) {
                // Actualizar
                $success = $this->maestriaModel->update($id, $data);
                $message = $success ? 'Registro de Maestría actualizado con éxito.' : 'Error al actualizar el Registro de Maestría.';
            } else {
                // Crear
                $success = $this->maestriaModel->create($data);
                $message = $success ? 'Registro de Maestría creado con éxito.' : 'Error al crear el Registro de Maestría.';
            }

            if ($success) {
                Auth::setFlashMessage('success', $message);
                $this->redirect('maestria'); // Redireccionar a la lista
            } else {
                Auth::setFlashMessage('error', $message);
                $redirectPath = $id ? 'maestria/edit/' . $id : 'maestria/create';
                $this->redirect($redirectPath); // Redireccionar de vuelta al formulario
            }
        } catch (\PDOException $e) {
            error_log('Error de BD en processForm (Maestria): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Error de base de datos: ' . $e->getMessage());
            $redirectPath = $id ? 'maestria/edit/' . $id : 'maestria/create';
            $this->redirect($redirectPath);
        } catch (\Exception $e) {
            error_log('Error en processForm (Maestria): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Ocurrió un error: ' . $e->getMessage());
            $redirectPath = $id ? 'maestria/edit/' . $id : 'maestria/create';
            $this->redirect($redirectPath);
        }
    }

    /**
     * Elimina un registro de Maestría.
     * @param int $id El ID del registro a eliminar.
     */
    public function delete(int $id): void
    {
        // Para solicitudes AJAX de eliminación, responder con JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            try {
                if ($this->maestriaModel->delete($id)) {
                    echo json_encode(['success' => true, 'message' => 'Registro de Maestría eliminado con éxito.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar el Registro de Maestría.']);
                }
            } catch (\PDOException $e) {
                error_log("Error al eliminar maestria: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error de base de datos al eliminar Maestría: ' . $e->getMessage()]);
            }
            exit(); // Terminar la ejecución para la solicitud AJAX
        } else {
            // Comportamiento original para solicitudes no-AJAX (redireccionamiento)
            try {
                if ($this->maestriaModel->delete($id)) {
                    Auth::setFlashMessage('success', 'Registro de Maestría eliminado con éxito.');
                } else {
                    Auth::setFlashMessage('error', 'Error al eliminar el Registro de Maestría.');
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos al eliminar Maestría: ' . $e->getMessage());
            }
            $this->redirect('maestria');
        }
    }
}
