<?php
// app/Modules/DiplomadoAbierto/DiplomadoAbiertoController.php
namespace App\Modules\DiplomadoAbierto;

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\DiplomadoAbierto\DiplomadoAbiertoModel;

class DiplomadoAbiertoController extends Controller
{
    private $diplomadoAbiertoModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->diplomadoAbiertoModel = new DiplomadoAbiertoModel();
    }

    /**
     * Muestra la lista de registros de DiplomadoAbierto.
     */
    public function index(): void
    {
        $this->view('DiplomadoAbierto/list'); // Ruta de vista relativa al módulo
    }

    /**
     * Procesa la solicitud AJAX para obtener los datos de DiplomadoAbierto para DataTables.
     */
    public function getDiplomadoAbiertoData(): void
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
            $data = $this->diplomadoAbiertoModel->getPaginatedDiplomadoAbierto($params);

            // Formatear los datos para DataTables (array de arrays)
            $formattedData = [];
            foreach ($data['data'] as $row) {
                // Los nombres de las columnas aquí deben coincidir con los aliases en la consulta SQL del modelo
                $formattedData[] = [
                    $row['id'],
                    htmlspecialchars($row['numero']), // Truncar número
                    htmlspecialchars($row['diplomado_nombre'] ?? 'N/A'), // Truncar nombre Diplomado
                    htmlspecialchars($row['sede_nombre'] ?? 'N/A'),   // Truncar nombre Sede
                    htmlspecialchars($row['estatus_nombre'] ?? 'N/A'), // Truncar nombre Estatus
                    htmlspecialchars($row['fecha_inicio']),
                    htmlspecialchars($row['fecha_fin']),
                    ''
                ];
            }
            $data['data'] = $formattedData; // Asigna los datos formateados de vuelta al array de DataTables

            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        } catch (\PDOException $e) {
            error_log('Error de BD en getDiplomadoAbiertoData (DiplomadoAbierto): ' . $e->getMessage());
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
     * Exibe el formulario para crear un nuevo registro de DiplomadoAbierto
     * o procesa el envío del formulario.
     */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm();
        } else {
            $diplomado_abierto_data = []; // Datos vacíos para el formulario
            $this->view('DiplomadoAbierto/form', ['diplomado_abierto_data' => $diplomado_abierto_data]);
        }
    }

    /**
     * Exibe el formulario para editar un registro de DiplomadoAbierto existente
     * o procesa el envío del formulario.
     * @param int $id El ID del registro a editar.
     */
    public function edit(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm($id);
        } else {
            $diplomado_abierto_data = $this->diplomadoAbiertoModel->getById($id);
            if (!$diplomado_abierto_data) {
                Auth::setFlashMessage('error', 'Diplomado Abierto no encontrado.');
                $this->redirect('diplomado_abierto');
            }
            $this->view('DiplomadoAbierto/form', ['diplomado_abierto_data' => $diplomado_abierto_data]);
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
                'diplomado_id' => !empty($_POST['diplomado_id']) ? (int)$this->sanitizeInput($_POST['diplomado_id']) : null,
                'sede_id' => !empty($_POST['sede_id']) ? (int)$this->sanitizeInput($_POST['sede_id']) : null,
                'estatus_id' => !empty($_POST['estatus_id']) ? (int)$this->sanitizeInput($_POST['estatus_id']) : null,
                'fecha_inicio' => $this->sanitizeInput($_POST['fecha_inicio']),
                'fecha_fin' => $this->sanitizeInput($_POST['fecha_fin']),
                'nombre_carta' => $_POST['nombre_carta'], // CKEditor content, no usar htmlspecialchars directamente aquí
            ];

            // Validación de campos obligatorios
            if (empty($data['numero']) || empty($data['diplomado_id']) || empty($data['sede_id']) || empty($data['estatus_id']) || empty($data['fecha_inicio']) || empty($data['fecha_fin'])) {
                Auth::setFlashMessage('error', 'Todos los campos son obligatorios.');
                $redirectPath = $id ? 'diplomado_abierto/edit/' . $id : 'diplomado_abierto/create';
                $this->redirect($redirectPath);
                return;
            }

            $success = false;
            if ($id) {
                // Actualizar
                $success = $this->diplomadoAbiertoModel->update($id, $data);
                $message = $success ? 'Registro de Diplomado Abierto actualizado con éxito.' : 'Error al actualizar el Registro de Diplomado Abierto.';
            } else {
                // Crear
                $success = $this->diplomadoAbiertoModel->create($data);
                $message = $success ? 'Registro de Diplomado Abierto creado con éxito.' : 'Error al crear el Registro de Diplomado Abierto.';
            }

            if ($success) {
                Auth::setFlashMessage('success', $message);
                $this->redirect('diplomado_abierto'); // Redirigir a la lista
            } else {
                Auth::setFlashMessage('error', $message);
                $redirectPath = $id ? 'diplomado_abierto/edit/' . $id : 'diplomado_abierto/create';
                $this->redirect($redirectPath); // Redirigir de vuelta para el formulario
            }
        } catch (\PDOException $e) {
            error_log('Error de BD en processForm (DiplomadoAbierto): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Error de base de datos: ' . $e->getMessage());
            $redirectPath = $id ? 'diplomado_abierto/edit/' . $id : 'diplomado_abierto/create';
            $this->redirect($redirectPath);
        } catch (\Exception $e) {
            error_log('Error en processForm (DiplomadoAbierto): ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Ocurrió un error: ' . $e->getMessage());
            $redirectPath = $id ? 'diplomado_abierto/edit/' . $id : 'diplomado_abierto/create';
            $this->redirect($redirectPath);
        }
    }

    /**
     * Elimina un registro de DiplomadoAbierto.
     * @param int $id El ID del registro a eliminar.
     */
    public function delete(int $id): void
    {
        // Para solicitudes AJAX de eliminación, responder con JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            try {
                if ($this->diplomadoAbiertoModel->delete($id)) {
                    echo json_encode(['success' => true, 'message' => 'Registro de Diplomado Abierto eliminado con éxito.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar el Registro de Diplomado Abierto.']);
                }
            } catch (\PDOException $e) {
                error_log("Error al eliminar diplomado_abierto: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error de base de datos al eliminar Diplomado Abierto: ' . $e->getMessage()]);
            }
            exit(); // Terminar a execução para a solicitação AJAX
        } else {
            // Comportamento original para solicitações no-AJAX (redireccionamento)
            try {
                if ($this->diplomadoAbiertoModel->delete($id)) {
                    Auth::setFlashMessage('success', 'Registro de Diplomado Abierto eliminado con éxito.');
                } else {
                    Auth::setFlashMessage('error', 'Error al eliminar el Registro de Diplomado Abierto.');
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos al eliminar Diplomado Abierto: ' . $e->getMessage());
            }
            $this->redirect('diplomado_abierto');
        }
    }
}
