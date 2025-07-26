<?php
// app/Modules/CursoControl/CursoControlController.php
namespace App\Modules\CursoControl;

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\CursoControl\CursoControlModel;

class CursoControlController extends Controller
{
    private $cursoControlModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->cursoControlModel = new CursoControlModel();
    }

    /**
     * Muestra la lista de registros de CursoControl.
     */
    public function index(): void
    {
        $this->view('CursoControl/list'); // Ruta de vista relativa al módulo
    }

    /**
     * Procesa la solicitud AJAX para obtener los datos de CursoControl para DataTables.
     */
    public function getCursoControlData(): void
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
            $data = $this->cursoControlModel->getPaginatedCursoControl($params);

            // DataTables espera el formato específico de las acciones en el lado del cliente
            // Por lo tanto, necesitamos añadir las acciones aquí para cada fila
            $formattedData = [];
            foreach ($data['data'] as $row) {
                // Los nombres de las columnas aquí deben coincidir con los aliases en la consulta SQL del modelo
                $formattedData[] = [
                    $row['id'],
                    htmlspecialchars($row['curso_abierto_numero'] ?? 'N/A'), // Número del Taller Control
                    htmlspecialchars($row['docente_nombre_completo'] ?? 'N/A'), // Nombre completo del Docente
                    htmlspecialchars($row['tema']),
                    htmlspecialchars($row['fecha']),
                    // Columna para acciones (editar/eliminar) - será renderizada en el JS
                    ''
                ];
            }
            $data['data'] = $formattedData; // Asigna los datos formateados de vuelta al array de DataTables

            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        } catch (\PDOException $e) {
            error_log('Error de BD en getCursoControlData (CursoControl): ' . $e->getMessage());
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
     * Exibe el formulario para crear un nuevo registro de CursoControl
     * o procesa el envío del formulario.
     */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm();
        } else {
            $curso_control_data = []; // Datos vacíos para el formulario
            $this->view('CursoControl/form', ['curso_control_data' => $curso_control_data]);
        }
    }

    /**
     * Exibe el formulario para editar un registro de CursoControl existente
     * o procesa el envío del formulario.
     * @param int $id El ID del registro a editar.
     */
    public function edit(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm($id);
        } else {
            $curso_control_data = $this->cursoControlModel->getById($id);
            if (!$curso_control_data) {
                Auth::setFlashMessage('error', 'Registro de Taller Control no encontrado.');
                $this->redirect('curso_control');
            }
            $this->view('CursoControl/form', ['curso_control_data' => $curso_control_data]);
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
                'curso_abierto_id' => !empty($_POST['curso_abierto_id']) ? (int)$this->sanitizeInput($_POST['curso_abierto_id']) : null,
                'docente_id' => !empty($_POST['docente_id']) ? (int)$this->sanitizeInput($_POST['docente_id']) : null,
                'tema' => $this->sanitizeInput($_POST['tema']),
                'fecha' => $this->sanitizeInput($_POST['fecha']),
            ];

            // Validação de campos obrigatórios
            if (empty($data['curso_abierto_id']) || empty($data['docente_id']) || empty($data['tema']) || empty($data['fecha'])) {
                Auth::setFlashMessage('error', 'Todos os campos são obrigatórios.');
                $redirectPath = $id ? 'curso_control/edit/' . $id : 'curso_control/create';
                $this->redirect($redirectPath);
                return;
            }

            $success = false;
            if ($id) {
                // Atualizar
                $success = $this->cursoControlModel->update($id, $data);
                $message = $success ? 'Registro de Taller Control actualizado con éxito.' : 'Erro ao actualizar o Registro de Taller Control.';
            } else {
                // Criar
                $success = $this->cursoControlModel->create($data);
                $message = $success ? 'Registro de Taller Control creado con éxito.' : 'Erro ao crear o Registro de Taller Control.';
            }

            if ($success) {
                Auth::setFlashMessage('success', $message);
                $this->redirect('curso_control'); // Redirecionar para a lista
            } else {
                Auth::setFlashMessage('error', $message);
                $redirectPath = $id ? 'curso_control/edit/' . $id : 'curso_control/create';
                $this->redirect($redirectPath); // Redirecionar de volta para o formulário
            }
        } catch (\PDOException $e) {
            error_log('Erro de BD em processForm (CursoControl): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Erro de base de dados: ' . $e->getMessage());
            $redirectPath = $id ? 'curso_control/edit/' . $id : 'curso_control/create';
            $this->redirect($redirectPath);
        } catch (\Exception $e) {
            error_log('Erro em processForm (CursoControl): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Ocorreu um erro: ' . $e->getMessage());
            $redirectPath = $id ? 'curso_control/edit/' . $id : 'curso_control/create';
            $this->redirect($redirectPath);
        }
    }

    /**
     * Elimina um registro de CursoControl.
     * @param int $id O ID do registro a eliminar.
     */
    public function delete(int $id): void
    {
        // Para solicitações AJAX de eliminação, responder com JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            try {
                if ($this->cursoControlModel->delete($id)) {
                    echo json_encode(['success' => true, 'message' => 'Registro de Taller Control eliminado con éxito.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erro ao eliminar o Registro de Taller Control.']);
                }
            } catch (\PDOException $e) {
                error_log("Erro ao eliminar Taller Control: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Erro de base de dados ao eliminar Taller Control: ' . $e->getMessage()]);
            }
            exit(); // Terminar a execução para a solicitação AJAX
        } else {
            // Comportamento original para solicitações não-AJAX (redirecionamento)
            try {
                if ($this->cursoControlModel->delete($id)) {
                    Auth::setFlashMessage('success', 'Registro de Taller Control eliminado com sucesso.');
                } else {
                    Auth::setFlashMessage('error', 'Erro ao eliminar o Registro de Taller Control.');
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Erro de base de dados ao eliminar Taller Control: ' . $e->getMessage());
            }
            $this->redirect('curso_control');
        }
    }
}
