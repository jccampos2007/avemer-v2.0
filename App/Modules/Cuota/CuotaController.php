<?php
// app/Modules/Cuota/CuotaController.php
namespace App\Modules\Cuota;

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\Cuota\CuotaModel;

class CuotaController extends Controller
{
    private $cuotaModel;

    public function __construct()
    {
        Auth::requireLogin(); // Asegura que el usuario esté logueado
        $this->cuotaModel = new CuotaModel();
    }

    /**
     * Muestra la lista de registros de Cuota (opcional, si tienes una vista de lista).
     * Por ahora, solo redirigiremos a la creación/edición.
     */
    public function index(): void
    {
        // Puedes implementar una vista de lista similar a DiplomadoAbierto/list.php si es necesario.
        // Por ahora, solo redirigimos a la creación para simplificar el ejemplo.
        $this->redirect('cuota/create');
    }

    /**
     * Exibe el formulario para crear un nuevo registro de Cuota
     * o procesa el envío del formulario.
     */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm();
        } else {
            $cuota_data = []; // Datos vacíos para el formulario de creación
            $this->view('Cuota/form', ['cuota_data' => $cuota_data]);
        }
    }

    /**
     * Exibe el formulario para editar un registro de Cuota existente
     * o procesa el envío del formulario.
     * @param int $id El ID del registro a editar.
     */
    public function edit(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm($id);
        } else {
            $cuota_data = $this->cuotaModel->getById($id);
            if (!$cuota_data) {
                Auth::setFlashMessage('error', 'Cuota no encontrada.');
                $this->redirect('cuota');
            }
            $this->view('Cuota/form', ['cuota_data' => $cuota_data]);
        }
    }

    /**
     * Procesa los datos del formulario (crear o actualizar).
     * @param int|null $id El ID del registro si es una actualización, null si es una creación.
     */
    private function processForm(?int $id = null): void
    {
        // Determinar si la solicitud es AJAX
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        try {
            // Validación básica y sanitización
            $data = [
                'nombre' => $this->sanitizeInput($_POST['nombre']),
                'monto' => (float)$this->sanitizeInput($_POST['monto']),
                'oferta_academica_id' => (int)$this->sanitizeInput($_POST['oferta_academica_id']),
                'tipo_oferta_academica_id' => (int)$this->sanitizeInput($_POST['tipo_oferta_academica_id']),
                'generado' => isset($_POST['generado']) ? (int)$this->sanitizeInput($_POST['generado']) : 0,
                'fecha_vencimiento' => $this->sanitizeInput($_POST['fecha_vencimiento']),
            ];

            // Validación de campos obligatorios
            if (
                empty($data['nombre']) || empty($data['monto']) || empty($data['oferta_academica_id']) ||
                empty($data['tipo_oferta_academica_id']) || empty($data['fecha_vencimiento'])
            ) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben ser completados.']);
                    exit();
                } else {
                    Auth::setFlashMessage('error', 'Todos los campos obligatorios deben ser completados.');
                    $redirectPath = $id ? 'cuota/edit/' . $id : 'cuota/create';
                    $this->redirect($redirectPath);
                    return;
                }
            }

            $success = false;
            if ($id) {
                // Actualizar
                $success = $this->cuotaModel->update($id, $data);
                $message = $success ? 'Cuota actualizada con éxito.' : 'Error al actualizar la Cuota.';
            } else {
                // Crear
                $success = $this->cuotaModel->create($data);
                $message = $success ? 'Cuota creada con éxito.' : 'Error al crear la Cuota.';
            }

            if ($success) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => $message, 'data' => $data]); // Devolver los datos para actualizar la UI
                    exit();
                } else {
                    Auth::setFlashMessage('success', $message);
                    $this->redirect('cuota');
                }
            } else {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $message]);
                    exit();
                } else {
                    Auth::setFlashMessage('error', $message);
                    $redirectPath = $id ? 'cuota/edit/' . $id : 'cuota/create';
                    $this->redirect($redirectPath);
                }
            }
        } catch (\PDOException $e) {
            error_log('Error de BD en processForm (Cuota): ' . $e->getMessage());
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
                exit();
            } else {
                Auth::setFlashMessage('error', 'Error de base de datos: ' . $e->getMessage());
                $redirectPath = $id ? 'cuota/edit/' . $id : 'cuota/create';
                $this->redirect($redirectPath);
            }
        } catch (\Exception $e) {
            error_log('Error en processForm (Cuota): ' . $e->getMessage());
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Ocurrió un error: ' . $e->getMessage()]);
                exit();
            } else {
                Auth::setFlashMessage('error', 'Ocurrió un error: ' . $e->getMessage());
                $redirectPath = $id ? 'cuota/edit/' . $id : 'cuota/create';
                $this->redirect($redirectPath);
            }
        }
    }

    /**
     * Endpoint AJAX para obtener ofertas académicas por tipo.
     * tipo_oferta_academica_id: 1=Curso, 2=Diplomado, 3=Evento, 4=Maestria
     */
    public function getAcademicOffersByType(): void
    {
        Auth::requireLogin();

        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['error' => 'Acceso denegado.']);
            exit();
        }

        $tipo_oferta_academica_id = $_GET['type_id'] ?? null;
        $data = [];

        try {
            switch ($tipo_oferta_academica_id) {
                case '1':
                    $data = $this->cuotaModel->getCursos();
                    break;
                case '2':
                    $data = $this->cuotaModel->getDiplomados();
                    break;
                case '3':
                    $data = $this->cuotaModel->getEventos();
                    break;
                case '4':
                    $data = $this->cuotaModel->getMaestrias();
                    break;
                default:
                    // Si no se especifica un tipo válido, devuelve un array vacío
                    break;
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $data]);
            exit();
        } catch (\PDOException $e) {
            error_log('Error de BD en getAcademicOffersByType (Cuota): ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al obtener ofertas académicas.']);
            exit();
        }
    }

    /**
     * Endpoint AJAX para obtener cuotas por tipo de oferta y ID de oferta.
     * @param int $tipoOfertaId
     * @param int $ofertaId
     */
    public function getCuotasByOfferData(): void
    {
        Auth::requireLogin();

        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['error' => 'Acceso denegado.']);
            exit();
        }

        $tipoOfertaId = $_GET['tipo_oferta_id'] ?? null;
        $ofertaId = $_GET['oferta_id'] ?? null;

        if (empty($tipoOfertaId) || empty($ofertaId)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Faltan parámetros de tipo de oferta o ID de oferta.']);
            exit();
        }

        try {
            $cuotas = $this->cuotaModel->getCuotasByOffer((int)$tipoOfertaId, (int)$ofertaId);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $cuotas]);
            exit();
        } catch (\PDOException $e) {
            error_log('Error de BD en getCuotasByOfferData (Cuota): ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al obtener cuotas por oferta: ' . $e->getMessage()]);
            exit();
        }
    }

    /**
     * Endpoint AJAX para obtener los alumnos asociados a una oferta académica.
     */
    public function getStudentsForDebtGeneration(): void
    {
        Auth::requireLogin();

        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['error' => 'Acceso denegado.']);
            exit();
        }

        $tipoOfertaId = $_GET['tipo_oferta_id'] ?? null;
        $ofertaId = $_GET['oferta_id'] ?? null;
        $cuotaId = $_GET['cuota_id'] ?? null;

        if (empty($tipoOfertaId) || empty($ofertaId) || empty($cuotaId)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Faltan parámetros de oferta o cuota.']);
            exit();
        }

        try {
            $students = $this->cuotaModel->getStudentsByOffer((int)$tipoOfertaId, (int)$ofertaId);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $students, 'cuota_id' => (int)$cuotaId]);
            exit();
        } catch (\PDOException $e) {
            error_log('Error de BD en getStudentsForDebtGeneration (Cuota): ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al obtener alumnos: ' . $e->getMessage()]);
            exit();
        }
    }

    /**
     * Endpoint AJAX para generar la deuda para los alumnos seleccionados.
     */
    public function generateDebt(): void
    {
        Auth::requireLogin();

        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['error' => 'Acceso denegado.']);
            exit();
        }

        $cuotaId = $_POST['cuota_id'] ?? null;
        $alumnoIds = $_POST['alumno_ids'] ?? [];
        $montoCuota = $_POST['monto_cuota'] ?? null;

        if (empty($cuotaId) || empty($alumnoIds) || empty($montoCuota)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Faltan datos para generar la deuda.']);
            exit();
        }

        $errors = [];
        $generatedCount = 0;

        try {
            $cuota = $this->cuotaModel->getById((int)$cuotaId);
            if (!$cuota) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Cuota no encontrada.']);
                exit();
            }

            foreach ($alumnoIds as $alumnoId) {
                $transactionData = [
                    'alumno_id' => (int)$alumnoId,
                    'cuota_id' => (int)$cuotaId,
                    'monto' => (float)$montoCuota,
                    'tipo' => 1, // 1: Débito (Deuda)
                    'estatus' => 1, // 1: Deuda generada
                    'id_transaccion_origen' => null // No hay origen para una deuda inicial
                ];
                if ($this->cuotaModel->insertTransaction($transactionData)) {
                    $generatedCount++;
                } else {
                    $errors[] = "Error al generar deuda para alumno ID: {$alumnoId}";
                }
            }

            if ($generatedCount > 0) {
                $this->cuotaModel->updateCuotaGenerado((int)$cuotaId, 1);
            }

            if (empty($errors)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => "Deuda generada para {$generatedCount} alumnos.", 'generated_count' => $generatedCount]);
                exit();
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => "Se generaron {$generatedCount} deudas con algunos errores: " . implode(', ', $errors), 'errors' => $errors]);
                exit();
            }
        } catch (\PDOException $e) {
            error_log('Error de BD en generateDebt (Cuota): ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al generar deuda: ' . $e->getMessage()]);
            exit();
        } catch (\Exception $e) {
            error_log('Error en generateDebt (Cuota): ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error al generar deuda: ' . $e->getMessage()]);
            exit();
        }
    }

    /**
     * Elimina un registro de Cuota.
     * @param int $id El ID del registro a eliminar.
     */
    public function delete(int $id): void
    {
        // Para solicitudes AJAX de eliminación, responder con JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            try {
                if ($this->cuotaModel->delete($id)) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Cuota eliminada con éxito.']);
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar la Cuota.']);
                }
            } catch (\PDOException $e) {
                error_log("Error al eliminar cuota: " . $e->getMessage());
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Error de base de datos al eliminar Cuota: ' . $e->getMessage()]);
            }
            exit(); // Terminar la ejecución para la solicitud AJAX
        } else {
            // Comportamiento original para solicitudes no-AJAX (redireccionamiento)
            try {
                if ($this->cuotaModel->delete($id)) {
                    Auth::setFlashMessage('success', 'Cuota eliminada con éxito.');
                } else {
                    Auth::setFlashMessage('error', 'Error al eliminar la Cuota.');
                }
            } catch (\PDOException $e) {
                Auth::setFlashMessage('error', 'Error de base de datos al eliminar Cuota: ' . $e->getMessage());
            }
            $this->redirect('cuota');
        }
    }
}
