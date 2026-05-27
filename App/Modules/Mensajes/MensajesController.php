<?php
// app/Modules/Mensajes/MensajesController.php
namespace App\Modules\Mensajes;

use App\Core\Controller; // Asume que Controller provee los helpers (sanitizeInput, redirect, view)
use App\Core\Auth;
use App\Modules\Mensajes\MensajesModel;

class MensajesController extends Controller
{
    private $MensajesModel;

    public function __construct()
    {
        Auth::requireLogin(); // Requiere que el usuario esté logueado para usar este módulo
        $this->MensajesModel = new MensajesModel();
    }

    /**
     * Muestra la lista de registros de Maestría.
     */
    public function index(): void
    {
        $this->view('Mensajes/list'); // Ruta de vista relativa al módulo
    }

    /**
     * Procesa la solicitud AJAX para obtener los datos de Maestría para DataTables.
     */
    public function getMensajesData(): void
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
            $data = $this->MensajesModel->getPaginatedMensajes($params);

            // DataTables espera el formato específico de las acciones en el lado del cliente
            // Por lo tanto, necesitamos añadir las acciones aquí para cada fila
            $formattedData = [];
            foreach ($data['data'] as $row) {
                // Los nombres de las columnas aquí deben coincidir con los de la consulta SQL del modelo
                $formattedData[] = [
                    $row['id'],
                    htmlspecialchars($row['titulo']),
                    // htmlspecialchars($row['mensaje']),
                    // Columna para acciones (editar/eliminar) - será renderizada en el JS
                    ''
                ];
            }
            $data['data'] = $formattedData; // Asigna los datos formateados de vuelta al array de DataTables

            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        } catch (\PDOException $e) {
            error_log('Error de BD en getMensajesData (Mensajes): ' . $e->getMessage());
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
            $this->validateCsrf();
            $this->processForm();
        } else {
            $Mensajes_data = []; // Datos vacíos para el formulario
            $this->view('Mensajes/form', ['Mensajes_data' => $Mensajes_data]);
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
            $this->validateCsrf();
            $this->processForm($id);
        } else {
            $Mensajes_data = $this->MensajesModel->getById($id);
            if (!$Mensajes_data) {
                Auth::setFlashMessage('error', 'Registro de Mensaje no encontrado.');
                $this->redirect('Mensajes');
            }
            $this->view('Mensajes/form', ['mensajes_data' => $Mensajes_data]);
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
                'titulo' => $this->sanitizeInput($_POST['titulo']),
                'mensaje' => $this->sanitizeInput($_POST['mensaje'] ?? ''),
            ];

            // Validación de campos obligatorios
            if (empty($data['titulo']) || empty($data['mensaje'])) {
                Auth::setFlashMessage('error', 'Título y Mensaje son campos obligatorios.');
                $redirectPath = $id ? 'Mensajes/edit/' . $id : 'Mensajes/create';
                $this->redirect($redirectPath);
                return;
            }

            $success = false;
            if ($id) {
                // Actualizar
                $success = $this->MensajesModel->update($id, $data);
                $message = $success ? 'Registro de Mensaje actualizado con éxito.' : 'Error al actualizar el Registro de Mensaje.';
            } else {
                // Crear
                $success = $this->MensajesModel->create($data);
                $message = $success ? 'Registro de Mensaje creado con éxito.' : 'Error al crear el Registro de Mensaje.';
            }

            if ($success) {
                Auth::setFlashMessage('success', $message);
                $this->redirect('mensajes'); // Redireccionar a la lista
            } else {
                Auth::setFlashMessage('error', $message);
                $redirectPath = $id ? 'mensajes/edit/' . $id : 'mensajes/create';
                $this->redirect($redirectPath); // Redireccionar de vuelta al formulario
            }
        } catch (\PDOException $e) {
            error_log('Error de BD en processForm (mensajes): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Error de base de datos: ' . $e->getMessage());
            $redirectPath = $id ? 'mensajes/edit/' . $id : 'mensajes/create';
            $this->redirect($redirectPath);
        } catch (\Exception $e) {
            error_log('Error en processForm (mensajes): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Ocurrió un error: ' . $e->getMessage());
            $redirectPath = $id ? 'mensajes/edit/' . $id : 'mensajes/create';
            $this->redirect($redirectPath);
        }
    }

    /**
     * Elimina un registro de Mensaje.
     * @param int $id El ID del registro a eliminar.
     */
    public function delete(int $id): void
    {
        // Para solicitudes AJAX de eliminación, responder con JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            try {
                if ($this->MensajesModel->delete($id)) {
                    echo json_encode(['success' => true, 'message' => 'Registro de Mensaje eliminado con éxito.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar el Registro de Mensaje.']);
                }
            } catch (\PDOException $e) {
                error_log("Error al eliminar Mensajes: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error de base de datos al eliminar Mensaje: ' . $e->getMessage()]);
            }
            exit(); // Terminar la ejecución para la solicitud AJAX
        } else {
            // Comportamiento original para solicitudes no-AJAX (redireccionamiento)
            try {
                if ($this->MensajesModel->delete($id)) {
                    Auth::setFlashMessage('success', 'Registro de Mensaje eliminado con éxito.');
                } else {
                    Auth::setFlashMessage('error', 'Error al eliminar el Registro de Mensaje.');
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos al eliminar Mensaje: ' . $e->getMessage());
            }
            $this->redirect('mensajes');
        }
    }
}
