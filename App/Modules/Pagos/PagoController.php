<?php
namespace App\Modules\Pagos;

use App\Core\Controller;
use App\Core\Auth;

class PagoController extends Controller
{
    private PagoModel $pagoModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->pagoModel = new PagoModel();
    }

    public function index(): void
    {
        $this->view('Pagos/list');
    }

    public function getPagosData(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $data = $this->pagoModel->getPaginated($_POST);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $this->processForm();
        } else {
            $formaPagos = $this->pagoModel->getFormaPagos();
            $bancos = $this->pagoModel->getBancos();
            $estatusPagos = $this->pagoModel->getEstatusPagos();
            $this->view('Pagos/form', [
                'pago_data' => [],
                'formaPagos' => $formaPagos,
                'bancos' => $bancos,
                'estatusPagos' => $estatusPagos,
                'alumno_nombre_current' => '',
            ]);
        }
    }

    public function edit(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $this->processForm($id);
        } else {
            $pago_data = $this->pagoModel->findById($id);
            if (!$pago_data) {
                Auth::setFlashMessage('error', 'Pago no encontrado.');
                $this->redirect('pago');
            }
            $formaPagos = $this->pagoModel->getFormaPagos();
            $bancos = $this->pagoModel->getBancos();
            $estatusPagos = $this->pagoModel->getEstatusPagos();
            $cuotas = $this->pagoModel->getCuotasByAlumno((int)$pago_data['alumno_id']);
            $alumno = $this->pagoModel->findAlumnoById((int)$pago_data['alumno_id']);
            $alumno_nombre_current = $alumno['nombre_completo'] ?? '';
            $this->view('Pagos/form', [
                'pago_data' => $pago_data,
                'formaPagos' => $formaPagos,
                'bancos' => $bancos,
                'estatusPagos' => $estatusPagos,
                'cuotas' => $cuotas,
                'alumno_nombre_current' => $alumno_nombre_current,
            ]);
        }
    }

    private function processForm(?int $id = null): void
    {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        try {
            $data = [
                'cuota_id' => (int)$this->sanitizeInput($_POST['cuota_id'] ?? '0'),
                'alumno_id' => (int)$this->sanitizeInput($_POST['alumno_id'] ?? '0'),
                'forma_pago_id' => (int)$this->sanitizeInput($_POST['forma_pago_id'] ?? '0'),
                'banco_id' => !empty($_POST['banco_id']) ? (int)$this->sanitizeInput($_POST['banco_id']) : null,
                'numero_control' => $this->sanitizeInput($_POST['numero_control'] ?? ''),
                'monto' => (float)$this->sanitizeInput($_POST['monto'] ?? '0'),
                'fecha' => $this->sanitizeInput($_POST['fecha'] ?? date('Y-m-d')),
                'estatus_pago_id' => (int)$this->sanitizeInput($_POST['estatus_pago_id'] ?? '1'),
            ];

            $requiereBancoReferencia = !in_array($data['forma_pago_id'], [4, 6]);

            if (empty($data['cuota_id']) || empty($data['alumno_id']) || empty($data['forma_pago_id'])
                || $data['monto'] <= 0) {
                $msg = 'Todos los campos obligatorios deben ser completados.';
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $msg]);
                    exit();
                }
                Auth::setFlashMessage('error', $msg);
                $this->redirect($id ? 'pago/edit/' . $id : 'pago/create');
                return;
            }

            if ($requiereBancoReferencia && (empty($data['banco_id']) || empty($data['numero_control']))) {
                $msg = 'Banco y Nro. Referencia son obligatorios para esta forma de pago.';
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $msg]);
                    exit();
                }
                Auth::setFlashMessage('error', $msg);
                $this->redirect($id ? 'pago/edit/' . $id : 'pago/create');
                return;
            }

            $saldoPendiente = $this->pagoModel->getSaldoPendiente($data['alumno_id'], $data['cuota_id']);
            if ($data['monto'] > $saldoPendiente) {
                $msg = 'El monto del pago ($' . number_format($data['monto'], 2) . ') excede el saldo pendiente ($' . number_format($saldoPendiente, 2) . ').';
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $msg]);
                    exit();
                }
                Auth::setFlashMessage('error', $msg);
                $this->redirect($id ? 'pago/edit/' . $id : 'pago/create');
                return;
            }

            if ($id) {
                $success = $this->pagoModel->update($id, $data);
                $message = $success ? 'Pago actualizado con éxito.' : 'Error al actualizar el pago.';
            } else {
                $pagoId = $this->pagoModel->create($data);
                $success = $pagoId !== null;
                $message = $success ? 'Pago registrado con éxito.' : 'Error al registrar el pago.';

                if ($success) {
                    $transaccionData = [
                        'alumno_id' => $data['alumno_id'],
                        'cuota_id' => $data['cuota_id'],
                        'tipo' => 2,
                        'monto' => $data['monto'],
                        'estatus' => 1,
                        'id_transaccion_origen' => null,
                    ];
                    $this->pagoModel->insertTransaccion($transaccionData);
                }
            }

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => $success, 'message' => $message]);
                exit();
            }

            if ($success) {
                Auth::setFlashMessage('success', $message);
                $this->redirect('pago');
            } else {
                Auth::setFlashMessage('error', $message);
                $this->redirect($id ? 'pago/edit/' . $id : 'pago/create');
            }
        } catch (\PDOException $e) {
            error_log('Error de BD en processForm (Pago): ' . $e->getMessage());
            $msg = 'Error de base de datos: ' . $e->getMessage();
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                exit();
            }
            Auth::setFlashMessage('error', $msg);
            $this->redirect($id ? 'pago/edit/' . $id : 'pago/create');
        }
    }

    public function delete(int $id): void
    {
        $this->validateCsrf();
        header('Content-Type: application/json');

        try {
            if ($this->pagoModel->delete($id)) {
                echo json_encode(['success' => true, 'message' => 'Pago eliminado con éxito.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el pago.']);
            }
        } catch (\PDOException $e) {
            error_log("Error al eliminar pago: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al eliminar pago.']);
        }
        exit;
    }

    public function getAlumnosAjax(): void
    {
        $alumnos = $this->pagoModel->getAlumnos();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $alumnos]);
        exit;
    }

    public function getCuotasByAlumnoAjax(): void
    {
        $alumnoId = (int)($_GET['alumno_id'] ?? 0);
        if (!$alumnoId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'data' => []]);
            exit;
        }
        $cuotas = $this->pagoModel->getCuotasByAlumnoPendientes($alumnoId);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $cuotas]);
        exit;
    }

    public function confirm(int $id): void
    {
        $this->validateCsrf();
        header('Content-Type: application/json');

        try {
            if ($this->pagoModel->updateStatus($id, 2)) {
                echo json_encode(['success' => true, 'message' => 'Pago confirmado con éxito.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al confirmar el pago.']);
            }
        } catch (\PDOException $e) {
            error_log("Error al confirmar pago: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al confirmar pago.']);
        }
        exit;
    }

    public function softDelete(int $id): void
    {
        $this->validateCsrf();
        header('Content-Type: application/json');

        try {
            if ($this->pagoModel->updateStatus($id, 3)) {
                echo json_encode(['success' => true, 'message' => 'Pago eliminado con éxito.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el pago.']);
            }
        } catch (\PDOException $e) {
            error_log("Error al eliminar pago: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al eliminar pago.']);
        }
        exit;
    }
}
