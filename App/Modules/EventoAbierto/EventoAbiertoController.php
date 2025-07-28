<?php
// app/Modules/EventoAbierto/EventoAbiertoController.php
namespace App\Modules\EventoAbierto;

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\EventoAbierto\EventoAbiertoModel;
use PDO; // Asegúrate de que PDO esté disponible

class EventoAbiertoController extends Controller
{
    private $eventoAbiertoModel;

    public function __construct()
    {
        Auth::requireLogin();
        // Asumiendo que App\Core\Database::getInstance()->getConnection() retorna una instancia PDO
        $this->eventoAbiertoModel = new EventoAbiertoModel(\App\Core\Database::getInstance()->getConnection());
    }

    /**
     * Muestra la lista de registros de EventoAbierto.
     */
    public function index(): void
    {
        $this->view('EventoAbierto/list'); // Ruta de vista relativa al módulo
    }

    /**
     * Procesa la solicitud AJAX para obtener los datos de EventoAbierto para DataTables.
     */
    public function getEventoAbiertoData(): void
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
            $data = $this->eventoAbiertoModel->getPaginatedEventoAbierto($params);

            // Formatear los datos para DataTables (array de arrays)
            $formattedData = [];
            foreach ($data['data'] as $row) {
                // Los nombres de las columnas aquí deben coincidir con los aliases en la consulta SQL del modelo
                $formattedData[] = [
                    $row['id'],
                    htmlspecialchars($row['numero']),
                    htmlspecialchars($row['evento_nombre'] ?? 'N/A'), // Nombre del Evento
                    htmlspecialchars($row['sede_nombre'] ?? 'N/A'),   // Nombre de la Sede
                    htmlspecialchars($row['estatus_nombre'] ?? 'N/A'), // Nombre del Estatus
                    htmlspecialchars($row['fecha_inicio']),
                    htmlspecialchars($row['fecha_fin']),
                    // No mostramos nombre_carta directamente en la tabla, pero puedes si lo deseas
                    // htmlspecialchars($row['nombre_carta']),
                    // Columna para acciones (editar/eliminar) - será renderizada en el JS
                    ''
                ];
            }
            $data['data'] = $formattedData; // Asigna los datos formateados de vuelta al array de DataTables

            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        } catch (\PDOException $e) {
            error_log('Error de BD en getEventoAbiertoData (EventoAbierto): ' . $e->getMessage());
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
     * Exibe el formulario para crear un nuevo registro de EventoAbierto
     * o procesa el envío del formulario.
     */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm();
        } else {
            $evento_abierto_data = []; // Datos vacíos para el formulario
            $this->view('EventoAbierto/form', ['evento_abierto_data' => $evento_abierto_data]);
        }
    }

    /**
     * Exibe el formulario para editar un registro de EventoAbierto existente
     * o procesa el envío del formulario.
     * @param int $id El ID del registro a editar.
     */
    public function edit(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm($id);
        } else {
            $evento_abierto_data = $this->eventoAbiertoModel->getById($id);
            if (!$evento_abierto_data) {
                Auth::setFlashMessage('error', 'Evento Abierto no encontrado.');
                $this->redirect('evento_abierto');
            }
            $this->view('EventoAbierto/form', ['evento_abierto_data' => $evento_abierto_data]);
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
                'numero' => $this->sanitizeInput($_POST['numero']),
                'evento_id' => !empty($_POST['evento_id']) ? (int)$this->sanitizeInput($_POST['evento_id']) : null,
                'sede_id' => !empty($_POST['sede_id']) ? (int)$this->sanitizeInput($_POST['sede_id']) : null,
                'estatus_id' => !empty($_POST['estatus_id']) ? (int)$this->sanitizeInput($_POST['estatus_id']) : null,
                'fecha_inicio' => $this->sanitizeInput($_POST['fecha_inicio']),
                'fecha_fin' => $this->sanitizeInput($_POST['fecha_fin']),
                'nombre_carta' => $_POST['nombre_carta'], // CKEditor content, no usar htmlspecialchars directamente aquí
            ];

            // Validación de campos obligatorios
            if (empty($data['numero']) || empty($data['evento_id']) || empty($data['sede_id']) || empty($data['estatus_id']) || empty($data['fecha_inicio']) || empty($data['fecha_fin']) || empty($data['nombre_carta'])) {
                Auth::setFlashMessage('error', 'Todos los campos son obligatorios.');
                $redirectPath = $id ? 'evento_abierto/edit/' . $id : 'evento_abierto/create';
                $this->redirect($redirectPath);
                return;
            }

            $success = false;
            if ($id) {
                // Actualizar
                $success = $this->eventoAbiertoModel->update($id, $data);
                $message = $success ? 'Registro de Evento Abierto actualizado con éxito.' : 'Error al actualizar el Registro de Evento Abierto.';
            } else {
                // Criar
                $success = $this->eventoAbiertoModel->create($data);
                $message = $success ? 'Registro de Evento Abierto creado con éxito.' : 'Error al crear el Registro de Evento Abierto.';
            }

            if ($success) {
                Auth::setFlashMessage('success', $message);
                $this->redirect('evento_abierto'); // Redirigir a la lista
            } else {
                Auth::setFlashMessage('error', $message);
                $redirectPath = $id ? 'evento_abierto/edit/' . $id : 'evento_abierto/create';
                $this->redirect($redirectPath); // Redirigir de vuelta para el formulario
            }
        } catch (\PDOException $e) {
            error_log('Error de BD en processForm (EventoAbierto): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Error de base de datos: ' . $e->getMessage());
            $redirectPath = $id ? 'evento_abierto/edit/' . $id : 'evento_abierto/create';
            $this->redirect($redirectPath);
        } catch (\Exception $e) {
            error_log('Error en processForm (EventoAbierto): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Ocurrió un error: ' . $e->getMessage());
            $redirectPath = $id ? 'evento_abierto/edit/' . $id : 'evento_abierto/create';
            $this->redirect($redirectPath);
        }
    }

    /**
     * Elimina un registro de EventoAbierto.
     * @param int $id El ID del registro a eliminar.
     */
    public function delete(int $id): void
    {
        // Para solicitudes AJAX de eliminación, responder con JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            try {
                if ($this->eventoAbiertoModel->delete($id)) {
                    echo json_encode(['success' => true, 'message' => 'Registro de Evento Abierto eliminado con éxito.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar el Registro de Evento Abierto.']);
                }
            } catch (\PDOException $e) {
                error_log("Error al eliminar evento_abierto: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error de base de datos al eliminar Evento Abierto: ' . $e->getMessage()]);
            }
            exit(); // Terminar a execução para a solicitação AJAX
        } else {
            // Comportamento original para solicitações não-AJAX (redirecionamento)
            try {
                if ($this->eventoAbiertoModel->delete($id)) {
                    Auth::setFlashMessage('success', 'Registro de Evento Abierto eliminado con éxito.');
                } else {
                    Auth::setFlashMessage('error', 'Error al eliminar el Registro de Evento Abierto.');
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos al eliminar Evento Abierto: ' . $e->getMessage());
            }
            $this->redirect('evento_abierto');
        }
    }
}
