<?php
namespace App\Modules\Cuota;

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\Cuota\CuotaModel;

class CuotaController extends Controller
{
    private $cuotaModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->cuotaModel = new CuotaModel();
    }

    public function index(): void
    {
        $this->redirect('cuota/create');
    }

    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $this->processForm();
        } else {
            $cuota_data = [];
            $this->view('Cuota/form', ['cuota_data' => $cuota_data]);
        }
    }

    public function edit(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
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

    private function processForm(?int $id = null): void
    {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        try {
            $diplomadoControlId = !empty($_POST['diplomado_control_id'])
                ? (int)$this->sanitizeInput($_POST['diplomado_control_id'])
                : null;

            $data = [
                'nombre' => $this->sanitizeInput($_POST['nombre']),
                'monto' => (float)$this->sanitizeInput($_POST['monto']),
                'oferta_academica_id' => (int)$this->sanitizeInput($_POST['oferta_academica_id']),
                'tipo_oferta_academica_id' => (int)$this->sanitizeInput($_POST['tipo_oferta_academica_id']),
                'diplomado_control_id' => $diplomadoControlId,
                'fecha_vencimiento' => $this->sanitizeInput($_POST['fecha_vencimiento']),
            ];

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
                $success = $this->cuotaModel->update($id, $data);
                $message = $success ? 'Cuota actualizada con éxito.' : 'Error al actualizar la Cuota.';
            } else {
                $success = $this->cuotaModel->create($data);
                $message = $success ? 'Cuota creada con éxito.' : 'Error al crear la Cuota.';
            }

            if ($success) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => $message, 'data' => $data]);
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

    public function getOfertaInfoAjax(): void
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
            echo json_encode(['success' => false, 'message' => 'Faltan parámetros.']);
            exit();
        }

        try {
            $info = $this->cuotaModel->getOfertaInfo((int)$tipoOfertaId, (int)$ofertaId);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $info]);
            exit();
        } catch (\PDOException $e) {
            error_log('Error de BD en getOfertaInfoAjax (Cuota): ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error de base de datos.']);
            exit();
        }
    }

    public function getDiplomadoControlesAjax(): void
    {
        Auth::requireLogin();

        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['error' => 'Acceso denegado.']);
            exit();
        }

        $diplomadoAbiertoId = $_GET['diplomado_abierto_id'] ?? null;

        if (empty($diplomadoAbiertoId)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Falta ID de diplomado abierto.']);
            exit();
        }

        try {
            $controles = $this->cuotaModel->getDiplomadoControles((int)$diplomadoAbiertoId);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $controles]);
            exit();
        } catch (\PDOException $e) {
            error_log('Error de BD en getDiplomadoControlesAjax (Cuota): ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error de base de datos.']);
            exit();
        }
    }

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

    public function generateDebt(): void
    {
        Auth::requireLogin();
        $this->validateCsrf();

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
                    'tipo' => 1,
                    'estatus' => 1,
                    'id_transaccion_origen' => null
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

    public function delete(int $id): void
    {
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
            exit();
        } else {
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
