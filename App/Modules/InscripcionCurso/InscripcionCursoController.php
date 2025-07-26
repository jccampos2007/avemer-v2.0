<?php
// app/Modules/InscripcionCurso/InscripcionCursoController.php
namespace App\Modules\InscripcionCurso;

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\InscripcionCurso\InscripcionCursoModel;

class InscripcionCursoController extends Controller
{
    private $inscripcionCursoModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->inscripcionCursoModel = new InscripcionCursoModel();
    }

    /**
     * Muestra la lista de registros de InscripcionCurso.
     */
    public function index(): void
    {
        $this->view('InscripcionCurso/list'); // Ruta de vista relativa al módulo
    }

    /**
     * Procesa la solicitud AJAX para obtener los datos de InscripcionCurso para DataTables.
     */
    public function getInscripcionCursoData(): void
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
            $data = $this->inscripcionCursoModel->getPaginatedInscripcionCurso($params);

            // DataTables espera el formato específico de las acciones en el lado del cliente
            // Por lo tanto, necesitamos añadir las acciones aquí para cada fila
            $formattedData = [];
            foreach ($data['data'] as $row) {
                // Los nombres de las columnas aquí deben coincidir con los aliases en la consulta SQL del modelo
                $formattedData[] = [
                    $row['id'],
                    htmlspecialchars($row['curso_abierto_numero'] ?? 'N/A'), // Número del Curso Abierto
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
            error_log('Error de BD en getInscripcionCursoData (InscripcionCurso): ' . $e->getMessage());
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
     * Exibe el formulario para crear un nuevo registro de InscripcionCurso
     * o procesa el envío del formulario.
     */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm();
        } else {
            $inscripcion_curso_data = []; // Datos vacíos para el formulario
            $this->view('InscripcionCurso/form', ['inscripcion_curso_data' => $inscripcion_curso_data]);
        }
    }

    /**
     * Exibe el formulario para editar un registro de InscripcionCurso existente
     * o procesa el envío del formulario.
     * @param int $id El ID del registro a editar.
     */
    public function edit(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm($id);
        } else {
            $inscripcion_curso_data = $this->inscripcionCursoModel->getById($id);
            if (!$inscripcion_curso_data) {
                Auth::setFlashMessage('error', 'Registro de Inscripción de Curso no encontrado.');
                $this->redirect('inscripcion_curso');
            }
            $this->view('InscripcionCurso/form', ['inscripcion_curso_data' => $inscripcion_curso_data]);
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
                'alumno_id' => !empty($_POST['alumno_id']) ? (int)$this->sanitizeInput($_POST['alumno_id']) : null,
                'estatus_inscripcion_id' => !empty($_POST['estatus_inscripcion_id']) ? (int)$this->sanitizeInput($_POST['estatus_inscripcion_id']) : null,
            ];

            // Validação de campos obrigatórios
            if (empty($data['curso_abierto_id']) || empty($data['alumno_id']) || empty($data['estatus_inscripcion_id'])) {
                Auth::setFlashMessage('error', 'Todos los campos son obligatorios.');
                $redirectPath = $id ? 'inscripcion_curso/edit/' . $id : 'inscripcion_curso/create';
                $this->redirect($redirectPath);
                return;
            }

            $success = false;
            if ($id) {
                // Atualizar
                $success = $this->inscripcionCursoModel->update($id, $data);
                $message = $success ? 'Registro de Inscripción de Curso actualizado con éxito.' : 'Error al actualizar el Registro de Inscripción de Curso.';
            } else {
                // Criar
                $success = $this->inscripcionCursoModel->create($data);
                $message = $success ? 'Registro de Inscripción de Curso creado con éxito.' : 'Error al crear el Registro de Inscripción de Curso.';
            }

            if ($success) {
                Auth::setFlashMessage('success', $message);
                $this->redirect('inscripcion_curso'); // Redirecionar para a lista
            } else {
                Auth::setFlashMessage('error', $message);
                $redirectPath = $id ? 'inscripcion_curso/edit/' . $id : 'inscripcion_curso/create';
                $this->redirect($redirectPath); // Redirecionar de volta para o formulário
            }
        } catch (\PDOException $e) {
            error_log('Error de BD en processForm (InscripcionCurso): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Error de base de datos: ' . $e->getMessage());
            $redirectPath = $id ? 'inscripcion_curso/edit/' . $id : 'inscripcion_curso/create';
            $this->redirect($redirectPath);
        } catch (\Exception $e) {
            error_log('Error en processForm (InscripcionCurso): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Ocurrió un error: ' . $e->getMessage());
            $redirectPath = $id ? 'inscripcion_curso/edit/' . $id : 'inscripcion_curso/create';
            $this->redirect($redirectPath);
        }
    }

    /**
     * Elimina un registro de InscripcionCurso.
     * @param int $id El ID del registro a eliminar.
     */
    public function delete(int $id): void
    {
        // Para solicitudes AJAX de eliminación, responder con JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            try {
                if ($this->inscripcionCursoModel->delete($id)) {
                    echo json_encode(['success' => true, 'message' => 'Registro de Inscripción de Curso eliminado con éxito.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erro ao eliminar o Registro de Inscripción de Curso.']);
                }
            } catch (\PDOException $e) {
                error_log("Erro ao eliminar inscripcion_curso: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Erro de base de dados ao eliminar Inscripción de Curso: ' . $e->getMessage()]);
            }
            exit(); // Terminar a execução para a solicitação AJAX
        } else {
            // Comportamento original para solicitações não-AJAX (redirecionamento)
            try {
                if ($this->inscripcionCursoModel->delete($id)) {
                    Auth::setFlashMessage('success', 'Registro de Inscripción de Curso eliminado con éxito.');
                } else {
                    Auth::setFlashMessage('error', 'Erro ao eliminar o Registro de Inscripción de Curso.');
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Erro de base de dados ao eliminar Inscripción de Curso: ' . $e->getMessage());
            }
            $this->redirect('inscripcion_curso');
        }
    }
}
