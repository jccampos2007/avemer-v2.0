<?php
namespace App\Modules\Asistencia;

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\Asistencia\AsistenciaModel;

class AsistenciaController extends Controller
{
    private AsistenciaModel $asistenciaModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->asistenciaModel = new AsistenciaModel();
    }

    public function index(): void
    {
        $this->view('Asistencia/list');
    }

    public function getAcademicOffersByType(): void
    {
        Auth::requireLogin();
        $this->requireAjax();

        $tipoOfertaId = $_GET['type_id'] ?? null;
        $data = [];

        try {
            switch ($tipoOfertaId) {
                case '1': $data = $this->asistenciaModel->getCursos(); break;
                case '2': $data = $this->asistenciaModel->getDiplomados(); break;
                case '3': $data = $this->asistenciaModel->getEventos(); break;
                case '4': $data = $this->asistenciaModel->getMaestrias(); break;
            }
            $this->jsonResponse(['success' => true, 'data' => $data]);
        } catch (\PDOException $e) {
            error_log('Error en getAcademicOffersByType (Asistencia): ' . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Error al cargar ofertas académicas.']);
        }
    }

    public function getOfertaInfoAjax(): void
    {
        Auth::requireLogin();
        $this->requireAjax();

        $tipoOfertaId = $_GET['tipo_oferta_id'] ?? null;
        $ofertaId = $_GET['oferta_id'] ?? null;

        if (empty($tipoOfertaId) || empty($ofertaId)) {
            $this->jsonResponse(['success' => false, 'message' => 'Faltan parámetros.']);
        }

        try {
            $info = $this->asistenciaModel->getOfertaInfo((int)$tipoOfertaId, (int)$ofertaId);
            $this->jsonResponse(['success' => true, 'data' => $info]);
        } catch (\PDOException $e) {
            error_log('Error en getOfertaInfoAjax (Asistencia): ' . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Error de base de datos.']);
        }
    }

    public function initAsistencia(): void
    {
        Auth::requireLogin();
        $this->requireAjax();

        $tipoOfertaId = (int)($_GET['tipo_oferta_id'] ?? 0);
        $ofertaId = (int)($_GET['oferta_id'] ?? 0);
        $observacion = $_GET['observacion'] ?? '';

        if ($tipoOfertaId < 1 || $ofertaId < 1) {
            $this->jsonResponse(['success' => false, 'message' => 'Faltan parámetros.']);
        }

        try {
            $masterId = $this->asistenciaModel->getOrCreateMaster($tipoOfertaId, $ofertaId, $observacion);
            $clases = $this->asistenciaModel->getClases($tipoOfertaId, $ofertaId, $masterId);
            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'master_id' => $masterId,
                    'clases' => $clases,
                ]
            ]);
        } catch (\PDOException $e) {
            error_log('Error en initAsistencia (Asistencia): ' . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Error de base de datos.']);
        }
    }

    public function getAlumnosAjax(): void
    {
        Auth::requireLogin();
        $this->requireAjax();

        $masterId = (int)($_GET['master_id'] ?? 0);
        $claseId = (int)($_GET['clase_id'] ?? 0);
        $tipoOfertaId = (int)($_GET['tipo_oferta_id'] ?? 0);
        $ofertaId = (int)($_GET['oferta_id'] ?? 0);

        if ($masterId < 1 || $claseId < 0 || $tipoOfertaId < 1 || $ofertaId < 1) {
            $this->jsonResponse(['success' => false, 'message' => 'Faltan parámetros.']);
        }

        try {
            $alumnos = $this->asistenciaModel->getAlumnos($tipoOfertaId, $ofertaId, $masterId, $claseId);
            $this->jsonResponse(['success' => true, 'data' => $alumnos]);
        } catch (\PDOException $e) {
            error_log('Error en getAlumnosAjax (Asistencia): ' . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Error al cargar estudiantes.']);
        }
    }

    public function saveAsistencia(): void
    {
        Auth::requireLogin();
        $this->requireAjax();
        $this->validateCsrf();

        $masterId = (int)($_POST['master_id'] ?? 0);
        $claseId = (int)($_POST['clase_id'] ?? 0);
        $tipoOfertaId = (int)($_POST['tipo_oferta_academica_id'] ?? 0);
        $ofertaId = (int)($_POST['oferta_id'] ?? 0);
        $alumnoIds = $_POST['alumno_ids'] ?? [];

        if ($masterId < 1 || $claseId < 0 || $tipoOfertaId < 1 || $ofertaId < 1) {
            $this->jsonResponse(['success' => false, 'message' => 'Faltan datos.']);
        }

        try {
            $observacion = $this->sanitizeInput($_POST['observacion'] ?? '');
            $this->asistenciaModel->getOrCreateMaster($tipoOfertaId, $ofertaId, $observacion);
            $success = $this->asistenciaModel->saveAsistencia($masterId, $claseId, $tipoOfertaId, $ofertaId, $alumnoIds);

            if ($success) {
                $clases = $this->asistenciaModel->getClases($tipoOfertaId, $ofertaId, $masterId);
                $this->jsonResponse(['success' => true, 'message' => 'Asistencia guardada con éxito.', 'clases' => $clases]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Error al guardar la asistencia.']);
            }
        } catch (\PDOException $e) {
            error_log('Error en saveAsistencia (Asistencia): ' . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Error de base de datos.']);
        } catch (\Exception $e) {
            error_log('Error en saveAsistencia (Asistencia): ' . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Error al guardar asistencia.']);
        }
    }

    public function getData(): void
    {
        Auth::requireLogin();
        $this->requireAjax();

        try {
            $data = $this->asistenciaModel->getAttendanceData();

            $formattedData = [];
            foreach ($data as $row) {
                $formattedData[] = [
                    $row['id'],
                    htmlspecialchars($row['tipo_oferta_nombre'] ?? ''),
                    htmlspecialchars($row['alumno_nombre'] ?? ''),
                    $row['presente'] ? '<span class="text-green-600 font-semibold">Presente</span>' : '<span class="text-red-600 font-semibold">Ausente</span>',
                    htmlspecialchars($row['observacion'] ?? ''),
                    ''
                ];
            }

            header('Content-Type: application/json');
            echo json_encode([
                'draw' => (int)($_POST['draw'] ?? 1),
                'recordsTotal' => count($data),
                'recordsFiltered' => count($data),
                'data' => $formattedData,
            ]);
            exit();
        } catch (\PDOException $e) {
            error_log('Error en getData (Asistencia): ' . $e->getMessage());
            $this->jsonResponse(['error' => 'Error al cargar datos de asistencia.']);
        }
    }

    private function requireAjax(): void
    {
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['error' => 'Acceso denegado.']);
            exit();
        }
    }

    private function jsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}
