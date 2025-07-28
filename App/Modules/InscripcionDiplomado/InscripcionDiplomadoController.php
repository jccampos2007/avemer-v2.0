<?php
// app/Modules/InscripcionDiplomado/InscripcionDiplomadoController.php
namespace App\Modules\InscripcionDiplomado;

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\InscripcionDiplomado\InscripcionDiplomadoModel;

class InscripcionDiplomadoController extends Controller
{
    private $inscripcionDiplomadoModel;

    public function __construct()
    {
        Auth::requireLogin();
        $pdo = \App\Core\Database::getInstance()->getConnection();
        $this->inscripcionDiplomadoModel = new InscripcionDiplomadoModel();
    }

    /**
     * Muestra la lista de registros de InscripcionDiplomado.
     */
    public function index(): void
    {
        $this->view('InscripcionDiplomado/list'); // Usa el helper 'view' de Controller
    }

    /**
     * Procesa la solicitud AJAX para obtener los datos de InscripcionDiplomado para DataTables.
     */
    public function getInscripcionDiplomadoData(): void
    {
        Auth::requireLogin();

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
            $data = $this->inscripcionDiplomadoModel->getPaginatedInscripcionDiplomado($params);

            $formattedData = [];
            foreach ($data['data'] as $row) {
                $formattedData[] = [
                    $row['id'],
                    htmlspecialchars($row['diplomado_abierto_numero'] ?? 'N/A'),
                    htmlspecialchars($row['alumno_nombre_completo'] ?? 'N/A'),
                    htmlspecialchars($row['estatus_inscripcion_nombre'] ?? 'N/A'),
                    ''
                ];
            }
            $data['data'] = $formattedData; // Asigna los datos formateados de vuelta al array de DataTables

            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        } catch (\PDOException $e) {
            error_log('Error de BD en getInscripcionDiplomadoData (InscripcionDiplomado): ' . $e->getMessage());
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
     * Exibe el formulario para crear un nuevo registro de InscripcionDiplomado
     * o procesa el envío del formulario.
     */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm();
        } else {
            $inscripcion_diplomado_data = []; // Datos vacíos para el formulario
            $this->view('InscripcionDiplomado/form', ['inscripcion_diplomado_data' => $inscripcion_diplomado_data]);
        }
    }

    /**
     * Exibe el formulario para editar un registro de InscripcionDiplomado existente
     * o procesa el envío del formulario.
     * @param int $id El ID del registro a editar.
     */
    public function edit(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm($id);
        } else {
            $inscripcion_diplomado_data = $this->inscripcionDiplomadoModel->getById($id);
            if (!$inscripcion_diplomado_data) {
                Auth::setFlashMessage('error', 'Inscripción de Diplomado no encontrada.');
                $this->redirect('inscripcion_diplomado');
            }
            $this->view('InscripcionDiplomado/form', ['inscripcion_diplomado_data' => $inscripcion_diplomado_data]);
        }
    }

    /**
     * Procesa los datos del formulario (crear o actualizar).
     * @param int|null $id El ID del registro si es una actualización, null si es una creación.
     */
    private function processForm(?int $id = null): void
    {
        try {
            // Validación básica y sanitización (asume sanitizeInput de Controller)
            $data = [
                'diplomado_abierto_id' => !empty($_POST['diplomado_abierto_id']) ? (int)$this->sanitizeInput($_POST['diplomado_abierto_id']) : null,
                'alumno_id' => !empty($_POST['alumno_id']) ? (int)$this->sanitizeInput($_POST['alumno_id']) : null,
                'estatus_inscripcion_id' => !empty($_POST['estatus_inscripcion_id']) ? (int)$this->sanitizeInput($_POST['estatus_inscripcion_id']) : null,
            ];

            // Validación de campos obligatorios
            if (empty($data['diplomado_abierto_id']) || empty($data['alumno_id']) || empty($data['estatus_inscripcion_id'])) {
                Auth::setFlashMessage('error', 'Todos los campos son obligatorios.');
                $redirectPath = $id ? 'inscripcion_diplomado/edit/' . $id : 'inscripcion_diplomado/create';
                $this->redirect($redirectPath);
                return;
            }

            $success = false;
            if ($id) {
                // Actualizar
                $success = $this->inscripcionDiplomadoModel->update($id, $data);
                $message = $success ? 'Inscripción de Diplomado actualizada con éxito.' : 'Error al actualizar la Inscripción de Diplomado.';
            } else {
                // Crear
                $success = $this->inscripcionDiplomadoModel->create($data);
                $message = $success ? 'Inscripción de Diplomado creada con éxito.' : 'Error al crear la Inscripción de Diplomado.';
            }

            if ($success) {
                Auth::setFlashMessage('success', $message);
                $this->redirect('inscripcion_diplomado'); // Redirigir a la lista
            } else {
                Auth::setFlashMessage('error', $message);
                $redirectPath = $id ? 'inscripcion_diplomado/edit/' . $id : 'inscripcion_diplomado/create';
                $this->redirect($redirectPath); // Redirigir de vuelta al formulario
            }
        } catch (\PDOException $e) {
            error_log('Error de BD en processForm (InscripcionDiplomado): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Error de base de datos: ' . $e->getMessage());
            $redirectPath = $id ? 'inscripcion_diplomado/edit/' . $id : 'inscripcion_diplomado/create';
            $this->redirect($redirectPath);
        } catch (\Exception $e) {
            error_log('Error en processForm (InscripcionDiplomado): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Ocurrió un error: ' . $e->getMessage());
            $redirectPath = $id ? 'inscripcion_diplomado/edit/' . $id : 'inscripcion_diplomado/create';
            $this->redirect($redirectPath);
        }
    }

    /**
     * Elimina un registro de InscripcionDiplomado.
     * @param int $id El ID del registro a eliminar.
     */
    public function delete(int $id): void
    {
        // Para solicitudes AJAX de eliminación, responder con JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            try {
                if ($this->inscripcionDiplomadoModel->delete($id)) {
                    echo json_encode(['success' => true, 'message' => 'Inscripción de Diplomado eliminada con éxito.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar la Inscripción de Diplomado.']);
                }
            } catch (\PDOException $e) {
                error_log("Error al eliminar inscripcion_diplomado: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error de base de datos al eliminar Inscripción de Diplomado: ' . $e->getMessage()]);
            }
            exit(); // Terminar la ejecución para la solicitud AJAX
        } else {
            // Comportamiento original para solicitudes no-AJAX (redireccionamiento)
            try {
                if ($this->inscripcionDiplomadoModel->delete($id)) {
                    Auth::setFlashMessage('success', 'Inscripción de Diplomado eliminada con éxito.');
                } else {
                    Auth::setFlashMessage('error', 'Error al eliminar la Inscripción de Diplomado.');
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos al eliminar Inscripción de Diplomado: ' . $e->getMessage());
            }
            $this->redirect('inscripcion_diplomado');
        }
    }
}
