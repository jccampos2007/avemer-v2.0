<?php
// app/Modules/Capitulo/CapituloController.php
namespace App\Modules\Capitulo;

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\Capitulo\CapituloModel;
use App\Modules\Diplomado\DiplomadoModel; // Necesitamos el modelo de Diplomado para el select
use PDO; // Asegúrate de que PDO esté disponible

class CapituloController extends Controller
{
    private $capituloModel;
    private $diplomadoModel; // Para obtener la lista de diplomados

    public function __construct()
    {
        Auth::requireLogin();
        $pdo = \App\Core\Database::getInstance()->getConnection();
        $this->capituloModel = new CapituloModel();
        $this->diplomadoModel = new DiplomadoModel(); // Inicializa el modelo de Diplomado
    }

    /**
     * Muestra la lista de registros de Capítulo.
     * Permite seleccionar un diplomado para filtrar los capítulos.
     */
    public function index(): void
    {
        // Puedes pasar la lista de diplomados a la vista si quieres pre-llenar el select
        // $diplomados = $this->diplomadoModel->getAll(); // Asumiendo que existe un getAll en DiplomadoModel
        $this->view('Capitulo/list'); // Ruta de vista relativa al módulo
    }

    /**
     * Procesa la solicitud AJAX para obtener los datos de Capítulo para DataTables.
     * Requiere un diplomado_id para filtrar.
     */
    public function getCapituloData(): void
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
        $diplomadoId = $_POST['diplomado_id'] ?? null; // ¡CRUCIAL! Obtener el diplomado_id

        if (empty($diplomadoId)) {
            // Si no se selecciona un diplomado, no se devuelven capítulos
            echo json_encode([
                'draw' => (int) $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Seleccione un Diplomado para ver sus Capítulos.'
            ]);
            exit();
        }

        $params = [
            'draw' => $draw,
            'start' => $start,
            'length' => $length,
            'search' => ['value' => $searchValue],
            'order' => [['column' => $orderColumnIndex, 'dir' => $orderDir]],
            'columns' => $columns,
            'diplomado_id' => (int) $diplomadoId // Pasar el ID del diplomado al modelo
        ];

        try {
            $data = $this->capituloModel->getPaginatedCapitulos($params);

            $formattedData = [];
            foreach ($data['data'] as $row) {
                $formattedData[] = [
                    $row['id'],
                    htmlspecialchars($row['numero']),
                    htmlspecialchars($row['nombre']),
                    htmlspecialchars($row['descripcion']),
                    ($row['activo'] ? 'Sí' : 'No'),
                    htmlspecialchars($row['orden']),
                    ''
                ];
            }
            $data['data'] = $formattedData;

            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        } catch (\PDOException $e) {
            error_log('Error de BD en getCapituloData (Capitulo): ' . $e->getMessage());
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
     * Exibe el formulario para crear un nuevo registro de Capítulo.
     * Requiere un diplomado_id.
     * @param int $diplomadoId El ID del diplomado al que pertenece el capítulo.
     */
    public function create(?int $diplomadoId = null): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm();
        } else {
            $diplomado = $this->diplomadoModel->getById($diplomadoId);
            if (!$diplomado) {
                Auth::setFlashMessage('error', 'Diplomado no encontrado para crear capítulo.');
                $this->redirect('capitulo'); // Redirigir a la lista general de capítulos
            }

            $capitulo_data = [
                'diplomado_id' => $diplomadoId,
                'diplomado_nombre' => $diplomado['nombre'] // Asumiendo que el modelo de Diplomado devuelve el nombre
            ];
            $this->view('Capitulo/form', ['capitulo_data' => $capitulo_data]);
        }
    }

    /**
     * Exibe el formulario para editar un registro de Capítulo existente.
     * @param int $id El ID del registro a editar.
     */
    public function edit(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm($id);
        } else {
            $capitulo_data = $this->capituloModel->getById($id);
            if (!$capitulo_data) {
                Auth::setFlashMessage('error', 'Capítulo no encontrado.');
                $this->redirect('capitulo');
            }
            // Para la edición, también necesitamos el nombre del diplomado para mostrarlo
            $diplomado = $this->diplomadoModel->getById($capitulo_data['diplomado_id']);
            if ($diplomado) {
                $capitulo_data['diplomado_nombre'] = $diplomado['nombre'];
            } else {
                $capitulo_data['diplomado_nombre'] = 'Diplomado Desconocido';
            }

            $this->view('Capitulo/form', ['capitulo_data' => $capitulo_data]);
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
                'diplomado_id' => !empty($_POST['diplomado_id']) ? (int)$this->sanitizeInput($_POST['diplomado_id']) : null,
                'numero' => $this->sanitizeInput($_POST['numero']),
                'nombre' => $this->sanitizeInput($_POST['nombre']),
                'descripcion' => $_POST['descripcion'], // CKEditor content, no usar htmlspecialchars directamente aquí
                'activo' => isset($_POST['activo']) ? 1 : 0, // Checkbox
                'orden' => !empty($_POST['orden']) ? (int)$this->sanitizeInput($_POST['orden']) : 0,
            ];

            // Validação de campos obrigatórios
            if (empty($data['diplomado_id']) || empty($data['numero']) || empty($data['nombre']) || empty($data['descripcion']) || !isset($data['activo']) || !isset($data['orden'])) {
                Auth::setFlashMessage('error', 'Todos los campos obligatorios deben ser completados.');
                $redirectPath = $id ? 'capitulo/edit/' . $id : 'capitulo/create/' . $data['diplomado_id']; // Redirigir con diplomado_id
                $this->redirect($redirectPath);
                return;
            }

            $success = false;
            if ($id) {
                // Actualizar
                $success = $this->capituloModel->update($id, $data);
                $message = $success ? 'Capítulo actualizado con éxito.' : 'Error al actualizar el Capítulo.';
            } else {
                // Crear
                $success = $this->capituloModel->create($data);
                $message = $success ? 'Capítulo creado con éxito.' : 'Error al crear el Capítulo.';
            }

            if ($success) {
                Auth::setFlashMessage('success', $message);
                $this->redirect('capitulo?diplomado_id=' . $data['diplomado_id']); // Redirigir a la lista filtrada
            } else {
                Auth::setFlashMessage('error', $message);
                $redirectPath = $id ? 'capitulo/edit/' . $id : 'capitulo/create/' . $data['diplomado_id'];
                $this->redirect($redirectPath);
            }
        } catch (\PDOException $e) {
            error_log('Error de BD en processForm (Capitulo): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Error de base de datos: ' . $e->getMessage());
            $redirectPath = $id ? 'capitulo/edit/' . $id : 'capitulo/create/' . ($_POST['diplomado_id'] ?? '');
            $this->redirect($redirectPath);
        } catch (\Exception $e) {
            error_log('Error en processForm (Capitulo): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Ocurrió un error: ' . $e->getMessage());
            $redirectPath = $id ? 'capitulo/edit/' . $id : 'capitulo/create/' . ($_POST['diplomado_id'] ?? '');
            $this->redirect($redirectPath);
        }
    }

    /**
     * Elimina un registro de Capítulo.
     * @param int $id El ID del registro a eliminar.
     */
    public function delete(int $id): void
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            try {
                if ($this->capituloModel->delete($id)) {
                    echo json_encode(['success' => true, 'message' => 'Capítulo eliminado con éxito.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar el Capítulo.']);
                }
            } catch (\PDOException $e) {
                error_log("Error al eliminar capitulo: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error de base de datos al eliminar Capítulo: ' . $e->getMessage()]);
            }
            exit();
        } else {
            try {
                if ($this->capituloModel->delete($id)) {
                    Auth::setFlashMessage('success', 'Capítulo eliminado con éxito.');
                } else {
                    Auth::setFlashMessage('error', 'Error al eliminar el Capítulo.');
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos al eliminar Capítulo: ' . $e->getMessage());
            }
            // Redirigir a la lista, no podemos saber el diplomado_id original aquí sin más datos
            $this->redirect('capitulo');
        }
    }
}
