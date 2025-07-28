<?php
// app/Modules/Diplomado/DiplomadoController.php
namespace App\Modules\Diplomado;

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\Diplomado\DiplomadoModel;
use PDO; // Asegúrate de que PDO esté disponible

class DiplomadoController extends Controller
{
    private $diplomadoModel;

    public function __construct()
    {
        Auth::requireLogin();
        // Asumiendo que App\Core\Database::getInstance()->getConnection() retorna una instancia PDO
        $this->diplomadoModel = new DiplomadoModel(\App\Core\Database::getInstance()->getConnection());
    }

    /**
     * Muestra la lista de registros de Diplomado.
     */
    public function index(): void
    {
        $this->view('Diplomado/list'); // Ruta de vista relativa al módulo
    }

    /**
     * Procesa la solicitud AJAX para obtener los datos de Diplomado para DataTables.
     */
    public function getDiplomadoData(): void
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
            $data = $this->diplomadoModel->getPaginatedDiplomados($params);

            // Formatear los datos para DataTables (array de arrays)
            $formattedData = [];
            foreach ($data['data'] as $row) {
                // Los nombres de las columnas aquí deben coincidir con los aliases en la consulta SQL del modelo
                $formattedData[] = [
                    $row['id'],
                    htmlspecialchars($row['duracion_nombre'] ?? 'N/A'), // Nombre de la Duración
                    htmlspecialchars($row['nombre']),
                    htmlspecialchars($row['descripcion']),
                    htmlspecialchars($row['siglas']),
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
            error_log('Error de BD en getDiplomadoData (Diplomado): ' . $e->getMessage());
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
     * Exibe el formulario para crear un nuevo registro de Diplomado
     * o procesa el envío del formulario.
     */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm();
        } else {
            $diplomado_data = []; // Datos vacíos para el formulario
            $this->view('Diplomado/form', ['diplomado_data' => $diplomado_data]);
        }
    }

    /**
     * Exibe el formulario para editar un registro de Diplomado existente
     * o procesa el envío del formulario.
     * @param int $id El ID del registro a editar.
     */
    public function edit(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm($id);
        } else {
            $diplomado_data = $this->diplomadoModel->getById($id);
            if (!$diplomado_data) {
                Auth::setFlashMessage('error', 'Registro de Diplomado no encontrado.');
                $this->redirect('diplomado');
            }
            $this->view('Diplomado/form', ['diplomado_data' => $diplomado_data]);
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
                'duracion_id' => !empty($_POST['duracion_id']) ? (int)$this->sanitizeInput($_POST['duracion_id']) : null,
                'nombre' => $this->sanitizeInput($_POST['nombre']),
                'descripcion' => $_POST['descripcion'], // CKEditor content, no usar htmlspecialchars directamente aquí
                'siglas' => $this->sanitizeInput($_POST['siglas']),
                'costo' => (float)$this->sanitizeInput($_POST['costo']),
                'inicial' => (float)$this->sanitizeInput($_POST['inicial']),
            ];

            // Validación de campos obligatorios
            if (empty($data['duracion_id']) || empty($data['nombre']) || empty($data['siglas']) || !isset($data['costo']) || !isset($data['inicial'])) {
                Auth::setFlashMessage('error', 'Todos los campos son obligatorios.');
                $redirectPath = $id ? 'diplomado/edit/' . $id : 'diplomado/create';
                $this->redirect($redirectPath);
                return;
            }

            $success = false;
            if ($id) {
                // Actualizar
                $success = $this->diplomadoModel->update($id, $data);
                $message = $success ? 'Registro de Diplomado actualizado con éxito.' : 'Error al actualizar el Registro de Diplomado.';
            } else {
                // Crear
                $success = $this->diplomadoModel->create($data);
                $message = $success ? 'Registro de Diplomado creado con éxito.' : 'Error al crear el Registro de Diplomado.';
            }

            if ($success) {
                Auth::setFlashMessage('success', $message);
                $this->redirect('diplomado'); // Redirigir a la lista
            } else {
                Auth::setFlashMessage('error', $message);
                $redirectPath = $id ? 'diplomado/edit/' . $id : 'diplomado/create';
                $this->redirect($redirectPath); // Redirigir de vuelta para el formulario
            }
        } catch (\PDOException $e) {
            error_log('Error de BD en processForm (Diplomado): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Error de base de datos: ' . $e->getMessage());
            $redirectPath = $id ? 'diplomado/edit/' . $id : 'diplomado/create';
            $this->redirect($redirectPath);
        } catch (\Exception $e) {
            error_log('Error en processForm (Diplomado): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Ocurrió un error: ' . $e->getMessage());
            $redirectPath = $id ? 'diplomado/edit/' . $id : 'diplomado/create';
            $this->redirect($redirectPath);
        }
    }

    /**
     * Elimina un registro de Diplomado.
     * @param int $id El ID del registro a eliminar.
     */
    public function delete(int $id): void
    {
        // Para solicitudes AJAX de eliminación, responder con JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            try {
                if ($this->diplomadoModel->delete($id)) {
                    echo json_encode(['success' => true, 'message' => 'Registro de Diplomado eliminado con éxito.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar el Registro de Diplomado.']);
                }
            } catch (\PDOException $e) {
                error_log("Error al eliminar diplomado: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error de base de datos al eliminar Diplomado: ' . $e->getMessage()]);
            }
            exit(); // Terminar a execução para a solicitação AJAX
        } else {
            // Comportamento original para solicitações no-AJAX (redireccionamento)
            try {
                if ($this->diplomadoModel->delete($id)) {
                    Auth::setFlashMessage('success', 'Registro de Diplomado eliminado con éxito.');
                } else {
                    Auth::setFlashMessage('error', 'Error al eliminar el Registro de Diplomado.');
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos al eliminar Diplomado: ' . $e->getMessage());
            }
            $this->redirect('diplomado');
        }
    }
}
