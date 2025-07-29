<?php
// app/Modules/MaestriaAbierto/MaestriaAbiertoController.php
namespace App\Modules\MaestriaAbierto;

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\MaestriaAbierto\MaestriaAbiertoModel;

class MaestriaAbiertoController extends Controller
{
    private $maestriaAbiertoModel;

    public function __construct()
    {
        Auth::requireLogin(); // Requiere que el usuario esté logueado para usar este módulo
        $this->maestriaAbiertoModel = new MaestriaAbiertoModel();
    }

    /**
     * Muestra la lista de registros de Maestría Abierta.
     */
    public function index(): void
    {
        $this->view('MaestriaAbierto/list'); // Ruta de vista relativa al módulo
    }

    /**
     * Procesa la solicitud AJAX para obtener los datos de Maestría Abierta para DataTables.
     */
    public function getMaestriaAbiertoData(): void
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
            $data = $this->maestriaAbiertoModel->getPaginatedMaestriaAbierto($params);

            // DataTables espera el formato específico de las acciones en el lado del cliente
            // Por lo tanto, necesitamos añadir las acciones aquí para cada fila
            $formattedData = [];
            foreach ($data['data'] as $row) {
                // Los nombres de las columnas aquí deben coincidir con los de la consulta SQL del modelo
                $formattedData[] = [
                    $row['id'],
                    htmlspecialchars($row['numero']),
                    htmlspecialchars($row['maestria_nombre'] ?? 'N/A'),
                    htmlspecialchars($row['sede_nombre'] ?? 'N/A'),
                    htmlspecialchars($row['estatus_nombre'] ?? 'N/A'),
                    htmlspecialchars($row['docente_nombre_completo'] ?? 'N/A'),
                    htmlspecialchars($row['fecha']),
                    ''
                ];
            }
            $data['data'] = $formattedData; // Asigna los datos formateados de vuelta al array de DataTables

            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        } catch (\PDOException $e) {
            error_log('Error de BD en getMaestriaAbiertoData (MaestriaAbierto): ' . $e->getMessage());
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
     * Exibe el formulario para crear un nuevo registro de Maestría Abierta
     * o procesa el envío del formulario.
     */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm();
        } else {
            $maestria_abierto_data = []; // Datos vacíos para el formulario
            $this->view('MaestriaAbierto/form', ['maestria_abierto_data' => $maestria_abierto_data]);
        }
    }

    /**
     * Exibe el formulario para editar un registro de Maestría Abierta existente
     * o procesa el envío del formulario.
     * @param int $id El ID del registro a editar.
     */
    public function edit(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm($id);
        } else {
            $maestria_abierto_data = $this->maestriaAbiertoModel->getById($id);
            if (!$maestria_abierto_data) {
                Auth::setFlashMessage('error', 'Registro de Maestría Abierta no encontrado.');
                $this->redirect('maestria_abierto');
            }
            $this->view('MaestriaAbierto/form', ['maestria_abierto_data' => $maestria_abierto_data]);
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
                'maestria_id' => !empty($_POST['maestria_id']) ? (int)$this->sanitizeInput($_POST['maestria_id']) : null,
                'sede_id' => !empty($_POST['sede_id']) ? (int)$this->sanitizeInput($_POST['sede_id']) : null,
                'estatus_id' => !empty($_POST['estatus_id']) ? (int)$this->sanitizeInput($_POST['estatus_id']) : null,
                'docente_id' => !empty($_POST['docente_id']) ? (int)$this->sanitizeInput($_POST['docente_id']) : null,
                'fecha' => $this->sanitizeInput($_POST['fecha']),
                'nombre_carta' => $_POST['nombre_carta'] ?? '', // CKEditor ya maneja el HTML, solo sanitiza si es necesario
                'convenio' => $this->sanitizeInput($_POST['convenio']),
            ];

            // Validación de campos obligatorios
            if (
                empty($data['numero']) || empty($data['maestria_id']) || empty($data['sede_id']) ||
                empty($data['estatus_id']) || empty($data['docente_id']) || empty($data['fecha']) ||
                empty($data['nombre_carta'])
            ) {
                Auth::setFlashMessage('error', 'Todos los campos marcados con * son obligatorios.');
                $redirectPath = $id ? 'maestria_abierto/edit/' . $id : 'maestria_abierto/create';
                $this->redirect($redirectPath);
                return;
            }

            $success = false;
            if ($id) {
                // Actualizar
                $success = $this->maestriaAbiertoModel->update($id, $data);
                $message = $success ? 'Registro de Maestría Abierta actualizado con éxito.' : 'Error al actualizar el Registro de Maestría Abierta.';
            } else {
                // Crear
                $success = $this->maestriaAbiertoModel->create($data);
                $message = $success ? 'Registro de Maestría Abierta creado con éxito.' : 'Error al crear el Registro de Maestría Abierta.';
            }

            if ($success) {
                Auth::setFlashMessage('success', $message);
                $this->redirect('maestria_abierto'); // Redireccionar a la lista
            } else {
                Auth::setFlashMessage('error', $message);
                $redirectPath = $id ? 'maestria_abierto/edit/' . $id : 'maestria_abierto/create';
                $this->redirect($redirectPath); // Redireccionar de vuelta al formulario
            }
        } catch (\PDOException $e) {
            error_log('Error de BD en processForm (MaestriaAbierto): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Error de base de datos: ' . $e->getMessage());
            $redirectPath = $id ? 'maestria_abierto/edit/' . $id : 'maestria_abierto/create';
            $this->redirect($redirectPath);
        } catch (\Exception $e) {
            error_log('Error en processForm (MaestriaAbierto): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Ocurrió un error: ' . $e->getMessage());
            $redirectPath = $id ? 'maestria_abierto/edit/' . $id : 'maestria_abierto/create';
            $this->redirect($redirectPath);
        }
    }

    /**
     * Elimina un registro de Maestría Abierta.
     * @param int $id El ID del registro a eliminar.
     */
    public function delete(int $id): void
    {
        // Para solicitudes AJAX de eliminación, responder con JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            try {
                if ($this->maestriaAbiertoModel->delete($id)) {
                    echo json_encode(['success' => true, 'message' => 'Registro de Maestría Abierta eliminado con éxito.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar el Registro de Maestría Abierta.']);
                }
            } catch (\PDOException $e) {
                error_log("Error al eliminar maestria_abierto: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error de base de datos al eliminar Maestría Abierta: ' . $e->getMessage()]);
            }
            exit(); // Terminar la ejecución para la solicitud AJAX
        } else {
            // Comportamiento original para solicitudes no-AJAX (redireccionamiento)
            try {
                if ($this->maestriaAbiertoModel->delete($id)) {
                    Auth::setFlashMessage('success', 'Registro de Maestría Abierta eliminado con éxito.');
                } else {
                    Auth::setFlashMessage('error', 'Error al eliminar el Registro de Maestría Abierta.');
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos al eliminar Maestría Abierta: ' . $e->getMessage());
            }
            $this->redirect('maestria_abierto');
        }
    }
}
