<?php
// app/Modules/InscripcionEvento/InscripcionEventoController.php
namespace App\Modules\InscripcionEvento;

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\InscripcionEvento\InscripcionEventoModel;

class InscripcionEventoController extends Controller
{
    private $inscripcionEventoModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->inscripcionEventoModel = new InscripcionEventoModel();
    }

    /**
     * Muestra la lista de registros de InscripcionEvento.
     */
    public function index(): void
    {
        $this->view('InscripcionEvento/list'); // Ruta de vista relativa al módulo
    }

    /**
     * Procesa la solicitud AJAX para obtener los datos de InscripcionEvento para DataTables.
     */
    public function getInscripcionEventoData(): void
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
            $data = $this->inscripcionEventoModel->getPaginatedInscripcionEvento($params);

            // DataTables espera el formato específico de las acciones en el lado del cliente
            // Por lo tanto, necesitamos añadir las acciones aquí para cada fila
            $formattedData = [];
            foreach ($data['data'] as $row) {
                // Los nombres de las columnas aquí deben coincidir con los aliases en la consulta SQL del modelo
                $formattedData[] = [
                    $row['id'],
                    htmlspecialchars($row['evento_abierto_numero'] ?? 'N/A'),
                    htmlspecialchars($row['alumno_nombre_completo'] ?? 'N/A'),
                    htmlspecialchars($row['ci_pasapote'] ?? 'N/A'),
                    htmlspecialchars($row['alumno_telefono'] ?? 'N/A'),
                    htmlspecialchars($row['correo'] ?? 'N/A'),
                    htmlspecialchars($row['estatus_inscripcion_nombre'] ?? 'N/A'),
                    ''
                ];
            }
            $data['data'] = $formattedData; // Asigna los datos formateados de vuelta al array de DataTables

            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        } catch (\PDOException $e) {
            error_log('Error de BD en getInscripcionEventoData (InscripcionEvento): ' . $e->getMessage());
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
     * Exibe el formulario para crear un nuevo registro de InscripcionEvento
     * o procesa el envío del formulario.
     */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $this->processForm();
        } else {
            $inscripcion_evento_data = []; // Datos vacíos para el formulario
            $this->view('InscripcionEvento/form', ['inscripcion_evento_data' => $inscripcion_evento_data]);
        }
    }

    /**
     * Exibe el formulario para editar un registro de InscripcionEvento existente
     * o procesa el envío del formulario.
     * @param int $id El ID del registro a editar.
     */
    public function edit(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $this->processForm($id);
        } else {
            $inscripcion_evento_data = $this->inscripcionEventoModel->getById($id);
            if (!$inscripcion_evento_data) {
                Auth::setFlashMessage('error', 'Registro de Inscripción de Evento no encontrado.');
                $this->redirect('inscripcion_evento');
            }

            $pdo = \App\Core\Database::getInstance()->getConnection();

            $eventoAbiertoNombre = '';
            if (!empty($inscripcion_evento_data['evento_abierto_id'])) {
                $stmt = $pdo->prepare("SELECT CONCAT(numero, ' - ', (SELECT nombre FROM evento WHERE id = evento_abierto.evento_id)) AS texto FROM evento_abierto WHERE id = :id");
                $stmt->execute(['id' => $inscripcion_evento_data['evento_abierto_id']]);
                $row = $stmt->fetch();
                $eventoAbiertoNombre = $row['texto'] ?? '';
            }
            $inscripcion_evento_data['evento_abierto_nombre'] = $eventoAbiertoNombre;

            $this->view('InscripcionEvento/form', ['inscripcion_evento_data' => $inscripcion_evento_data]);
        }
    }

    /**
     * Procesa los datos del formulario (crear o actualizar).
     * @param int|null $id El ID del registro si es una actualización, null si es una creación.
     */
    private function processForm(?int $id = null): void
    {
        try {
            // Validação básica e sanitização
            $data = [
                'evento_abierto_id' => !empty($_POST['evento_abierto_id']) ? (int)$this->sanitizeInput($_POST['evento_abierto_id']) : null,
                'alumno_id' => !empty($_POST['alumno_id']) ? (int)$this->sanitizeInput($_POST['alumno_id']) : null,
                'estatus_inscripcion_id' => !empty($_POST['estatus_inscripcion_id']) ? (int)$this->sanitizeInput($_POST['estatus_inscripcion_id']) : null,
            ];

            // Validação de campos obrigatórios
            if (empty($data['evento_abierto_id']) || empty($data['alumno_id']) || empty($data['estatus_inscripcion_id'])) {
                Auth::setFlashMessage('error', 'Todos los campos son obligatorios.');
                $redirectPath = $id ? 'inscripcion_evento/edit/' . $id : 'inscripcion_evento/create';
                $this->redirect($redirectPath);
                return;
            }

            // Validar inscripción duplicada
            if ($this->inscripcionEventoModel->exists($data['alumno_id'], $data['evento_abierto_id'], $id)) {
                Auth::setFlashMessage('error', 'El alumno ya se encuentra inscrito en este evento.');
                $redirectPath = $id ? 'inscripcion_evento/edit/' . $id : 'inscripcion_evento/create';
                $this->redirect($redirectPath);
                return;
            }

            $success = false;
            if ($id) {
                // Atualizar
                $success = $this->inscripcionEventoModel->update($id, $data);
                $message = $success ? 'Registro de Inscripción de Evento actualizado con éxito.' : 'Error al actualizar el Registro de Inscripción de Evento.';
            } else {
                // Criar
                $success = $this->inscripcionEventoModel->create($data);
                $message = $success ? 'Registro de Inscripción de Evento creado con éxito.' : 'Error al crear el Registro de Inscripción de Evento.';
            }

            if ($success) {
                Auth::setFlashMessage('success', $message);
                $this->redirect('inscripcion_evento'); // Redirecionar para a lista
            } else {
                Auth::setFlashMessage('error', $message);
                $redirectPath = $id ? 'inscripcion_evento/edit/' . $id : 'inscripcion_evento/create';
                $this->redirect($redirectPath); // Redirecionar de volta para o formulário
            }
        } catch (\PDOException $e) {
            error_log('Error de BD en processForm (InscripcionEvento): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Error de base de datos: ' . $e->getMessage());
            $redirectPath = $id ? 'inscripcion_evento/edit/' . $id : 'inscripcion_evento/create';
            $this->redirect($redirectPath);
        } catch (\Exception $e) {
            error_log('Error en processForm (InscripcionEvento): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Ocurrió un error: ' . $e->getMessage());
            $redirectPath = $id ? 'inscripcion_evento/edit/' . $id : 'inscripcion_evento/create';
            $this->redirect($redirectPath);
        }
    }

    /**
     * Elimina un registro de InscripcionEvento.
     * @param int $id El ID del registro a eliminar.
     */
    public function delete(int $id): void
    {
        // Para solicitudes AJAX de eliminación, responder con JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            try {
                if ($this->inscripcionEventoModel->delete($id)) {
                    echo json_encode(['success' => true, 'message' => 'Registro de Inscripción de Evento eliminado con éxito.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erro ao eliminar o Registro de Inscripción de Evento.']);
                }
            } catch (\PDOException $e) {
                error_log("Erro ao eliminar inscripcion_evento: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Erro de base de dados ao eliminar Inscripción de Evento: ' . $e->getMessage()]);
            }
            exit(); // Terminar a execução para a solicitação AJAX
        } else {
            // Comportamento original para solicitações não-AJAX (redirecionamento)
            try {
                if ($this->inscripcionEventoModel->delete($id)) {
                    Auth::setFlashMessage('success', 'Registro de Inscripción de Evento eliminado con éxito.');
                } else {
                    Auth::setFlashMessage('error', 'Erro ao eliminar o Registro de Inscripción de Evento.');
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Erro de base de dados ao eliminar Inscripción de Evento: ' . $e->getMessage());
            }
            $this->redirect('inscripcion_evento');
        }
    }
}
