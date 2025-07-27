<?php
// app/Modules/Evento/EventoController.php
namespace App\Modules\Evento;

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\Evento\EventoModel;
use PDO; // Asegúrate de que PDO esté disponible

class EventoController extends Controller
{
    private $eventoModel;

    public function __construct()
    {
        Auth::requireLogin();
        // Asumiendo que App\Core\Database::getInstance()->getConnection() retorna una instancia PDO
        $this->eventoModel = new EventoModel(\App\Core\Database::getInstance()->getConnection());
    }

    /**
     * Muestra la lista de registros de Evento.
     */
    public function index(): void
    {
        $this->view('Evento/list'); // Ruta de vista relativa al módulo
    }

    /**
     * Exibe el formulario para crear un nuevo registro de Evento
     * o procesa el envío del formulario.
     */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm();
        } else {
            $evento_data = []; // Datos vacíos para el formulario
            $this->view('Evento/form', ['evento_data' => $evento_data]);
        }
    }

    /**
     * Exibe el formulario para editar un registro de Evento existente
     * o procesa el envío del formulario.
     * @param int $id El ID del registro a editar.
     */
    public function edit(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm($id);
        } else {
            $evento_data = $this->eventoModel->getById($id);
            if (!$evento_data) {
                Auth::setFlashMessage('error', 'Registro de Evento no encontrado.');
                $this->redirect('evento');
            }
            $this->view('Evento/form', ['evento_data' => $evento_data]);
        }
    }

    /**
     * Procesa la solicitud AJAX para obtener los datos de Evento para DataTables.
     */
    public function getEventoData(): void
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
            $data = $this->eventoModel->getPaginatedEvento($params);

            // Formatear los datos para DataTables (array de arrays)
            $formattedData = [];
            foreach ($data['data'] as $row) {
                // Los nombres de las columnas aquí deben coincidir con los aliases en la consulta SQL del modelo
                $formattedData[] = [
                    $row['id'],
                    htmlspecialchars($row['siglas']),
                    htmlspecialchars($row['nombre']),
                    htmlspecialchars($row['duracion_nombre'] ?? 'N/A'), // Nombre de la Duración
                    htmlspecialchars($row['descripcion']),
                    htmlspecialchars($row['costo']),
                    htmlspecialchars($row['inicial']),
                    // Columna para acciones (editar/eliminar) - será renderizada en el JS
                    ''
                ];
            }
            $data['data'] = $formattedData; // Asigna los datos formateados de vuelta al array de DataTables

            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        } catch (\PDOException $e) {
            error_log('Error de BD en getEventoData (Evento): ' . $e->getMessage());
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
            // Validação básica y sanitización
            $data = [
                'duracion_id' => !empty($_POST['duracion_id']) ? (int)$this->sanitizeInput($_POST['duracion_id']) : null,
                'nombre' => $this->sanitizeInput($_POST['nombre']),
                'descripcion' => $this->sanitizeInput($_POST['descripcion']), // CKEditor content, handle carefully
                'siglas' => $this->sanitizeInput($_POST['siglas']),
                'costo' => (float)$this->sanitizeInput($_POST['costo']),
                'inicial' => (float)$this->sanitizeInput($_POST['inicial']),
            ];

            // Validação de campos obrigatórios
            if (empty($data['duracion_id']) || empty($data['nombre']) || empty($data['descripcion']) || empty($data['siglas']) || !isset($data['costo']) || !isset($data['inicial'])) {
                Auth::setFlashMessage('error', 'Todos los campos son obligatorios.');
                $redirectPath = $id ? 'evento/edit/' . $id : 'evento/create';
                $this->redirect($redirectPath);
                return;
            }

            $success = false;
            if ($id) {
                // Actualizar
                $success = $this->eventoModel->update($id, $data);
                $message = $success ? 'Registro de Evento actualizado con éxito.' : 'Error al actualizar el Registro de Evento.';
            } else {
                // Criar
                $success = $this->eventoModel->create($data);
                $message = $success ? 'Registro de Evento creado con éxito.' : 'Error al crear el Registro de Evento.';
            }

            if ($success) {
                Auth::setFlashMessage('success', $message);
                $this->redirect('evento'); // Redirecionar para a lista
            } else {
                Auth::setFlashMessage('error', $message);
                $redirectPath = $id ? 'evento/edit/' . $id : 'evento/create';
                $this->redirect($redirectPath); // Redirecionar de volta para o formulário
            }
        } catch (\PDOException $e) {
            error_log('Error de BD en processForm (Evento): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Error de base de datos: ' . $e->getMessage());
            $redirectPath = $id ? 'evento/edit/' . $id : 'evento/create';
            $this->redirect($redirectPath);
        } catch (\Exception $e) {
            error_log('Error en processForm (Evento): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Ocurrió un error: ' . $e->getMessage());
            $redirectPath = $id ? 'evento/edit/' . $id : 'evento/create';
            $this->redirect($redirectPath);
        }
    }

    /**
     * Elimina un registro de Evento.
     * @param int $id El ID del registro a eliminar.
     */
    public function delete(int $id): void
    {
        // Para solicitudes AJAX de eliminación, responder con JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            try {
                if ($this->eventoModel->delete($id)) {
                    echo json_encode(['success' => true, 'message' => 'Registro de Evento eliminado con éxito.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erro ao eliminar o Registro de Evento.']);
                }
            } catch (\PDOException $e) {
                error_log("Erro ao eliminar evento: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Erro de base de dados ao eliminar Evento: ' . $e->getMessage()]);
            }
            exit(); // Terminar a execução para a solicitação AJAX
        } else {
            // Comportamento original para solicitações não-AJAX (redirecionamento)
            try {
                if ($this->eventoModel->delete($id)) {
                    Auth::setFlashMessage('success', 'Registro de Evento eliminado con éxito.');
                } else {
                    Auth::setFlashMessage('error', 'Erro ao eliminar o Registro de Evento.');
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Erro de base de datos ao eliminar Evento: ' . $e->getMessage());
            }
            $this->redirect('evento');
        }
    }
}
