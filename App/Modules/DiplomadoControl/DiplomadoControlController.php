<?php
// php_mvc_app/app/Modules/DiplomadoControl/DiplomadoControlController.php
namespace App\Modules\DiplomadoControl;

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\DiplomadoControl\DiplomadoControlModel;

class DiplomadoControlController extends Controller
{
    private $controlModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->controlModel = new DiplomadoControlModel();
    }

    /**
     * Vista principal del módulo. La tabla se carga vía AJAX con DataTables server-side.
     */
    public function index(): void
    {
        $this->view('DiplomadoControl/list');
    }

    /**
     * Formulario de creación de control para un diplomado abierto.
     */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $this->processForm();
        } else {
            $docentes = $this->controlModel->getDocentesActivos();
            
            $this->view('DiplomadoControl/form', [
                'docentes' => $docentes,
                'control_data' => [],
                'is_edit' => false
            ]);
        }
    }

    /**
     * Formulario de edición del control de contenido de un diplomado abierto específico.
     */
    public function edit(int $diplomadoAbiertoId): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $this->processForm($diplomadoAbiertoId);
        } else {
            $diplomadoAbierto = $this->controlModel->findDiplomadoAbierto($diplomadoAbiertoId);
            if (!$diplomadoAbierto) {
                Auth::setFlashMessage('error', 'El diplomado abierto solicitado no existe.');
                $this->redirect('diplomadocontrol');
            }

            $docentes = $this->controlModel->getDocentesActivos();
            $controlesExistentes = $this->controlModel->getControlesPorDiplomadoAbierto($diplomadoAbiertoId);
            $capitulosBase = $this->controlModel->getCapitulosPorDiplomado($diplomadoAbierto['diplomado_id']);

            $controlesIndexados = [];
            foreach ($controlesExistentes as $ctrl) {
                $controlesIndexados[$ctrl['capitulo_id']] = $ctrl;
            }

            $merged = [];
            foreach ($capitulosBase as $cap) {
                if (isset($controlesIndexados[$cap['id']])) {
                    $merged[] = $controlesIndexados[$cap['id']];
                } else {
                    $merged[] = [
                        'capitulo_id' => $cap['id'],
                        'capitulo_numero' => $cap['numero'],
                        'capitulo_nombre' => $cap['nombre'],
                        'docente_id' => null,
                        'fecha' => '',
                        'mensualidad' => 0.0
                    ];
                }
            }

            $this->view('DiplomadoControl/form', [
                'diplomadoAbierto' => $diplomadoAbierto,
                'docentes' => $docentes,
                'controles' => $merged,
                'is_edit' => true
            ]);
        }
    }

    /**
     * Procesa la inserción y actualización del formulario de control.
     */
    private function processForm(?int $id = null): void
    {
        $diplomadoAbiertoId = $id ?? (int)$this->sanitizeInput($_POST['diplomado_abierto_id']);
        
        if (empty($diplomadoAbiertoId)) {
            Auth::setFlashMessage('error', 'Debe seleccionar un Diplomado Abierto válido.');
            $this->redirect('dipladocontrol/create');
            return;
        }

        $capitulosData = $_POST['capitulos'] ?? [];

        try {
            $successCount = 0;
            foreach ($capitulosData as $capituloId => $campos) {
                $fecha = !empty($campos['fecha']) ? $this->sanitizeInput($campos['fecha']) : '';
                $docenteId = !empty($campos['docente_id']) ? (int)$campos['docente_id'] : 0;

                if (empty($fecha) && empty($docenteId)) {
                    continue;
                }

                $data = [
                    'diplomado_abierto_id' => $diplomadoAbiertoId,
                    'capitulo_id' => (int)$capituloId,
                    'docente_id' => $docenteId ?: null,
                    'fecha' => $fecha ?: date('Y-m-d'),
                    'mensualidad' => !empty($campos['mensualidad']) ? (float)$campos['mensualidad'] : 0.0
                ];

                if ($this->controlModel->upsertControl($data)) {
                    $successCount++;
                }
            }

            if ($successCount > 0) {
                Auth::setFlashMessage('success', 'Control de Diplomado guardado con éxito. (' . $successCount . ' Capítulos procesados)');
            } else {
                Auth::setFlashMessage('error', 'No se pudieron registrar controles para los capítulos suministrados.');
            }

            $this->redirect('diplomadocontrol');

        } catch (\PDOException $e) {
            error_log('Error al guardar diplomado control: ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Error de base de datos al guardar control: ' . $e->getMessage());
            $this->redirect('diplomadocontrol');
        }
    }

    /**
     * Endpoint AJAX para obtener los capítulos o controles guardados de un Diplomado Abierto.
     * Si existen registros en diplomado_control los retorna. Si no, retorna los capítulos base con valores por defecto.
     */
    public function getCapitulosAjax(): void
    {
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['error' => 'Acceso denegado.']);
            exit();
        }

        header('Content-Type: application/json');
        $diplomadoAbiertoId = isset($_GET['diplomado_abierto_id']) ? (int)$_GET['diplomado_abierto_id'] : 0;

        if ($diplomadoAbiertoId <= 0) {
            echo json_encode([]);
            exit();
        }

        $diplomadoAbierto = $this->controlModel->findDiplomadoAbierto($diplomadoAbiertoId);
        if (!$diplomadoAbierto) {
            echo json_encode([]);
            exit();
        }

        // Obtener todos los capítulos base y los controles existentes
        $capitulos = $this->controlModel->getCapitulosPorDiplomado($diplomadoAbierto['diplomado_id']);
        $controles = $this->controlModel->getControlesPorDiplomadoAbierto($diplomadoAbiertoId);

        // Indexar controles por capitulo_id para merge rápido
        $controlesIndexados = [];
        foreach ($controles as $ctrl) {
            $controlesIndexados[$ctrl['capitulo_id']] = $ctrl;
        }

        // Merge: si el capítulo tiene control, usar sus datos; si no, defaults vacíos
        $result = [];
        foreach ($capitulos as $cap) {
            if (isset($controlesIndexados[$cap['id']])) {
                $ctrl = $controlesIndexados[$cap['id']];
                $result[] = [
                    'id' => $ctrl['capitulo_id'],
                    'numero' => $ctrl['capitulo_numero'],
                    'nombre' => $ctrl['capitulo_nombre'],
                    'docente_id' => $ctrl['docente_id'],
                    'fecha' => $ctrl['fecha'] ?? '',
                    'mensualidad' => $ctrl['mensualidad'] ?? 0.0
                ];
            } else {
                $result[] = [
                    'id' => $cap['id'],
                    'numero' => $cap['numero'],
                    'nombre' => $cap['nombre'],
                    'docente_id' => null,
                    'fecha' => '',
                    'mensualidad' => 0.0
                ];
            }
        }
        echo json_encode([
            'costo' => (float)$diplomadoAbierto['costo'],
            'inicial' => (float)$diplomadoAbierto['inicial'],
            'diplomado_id' => (int)$diplomadoAbierto['diplomado_id'],
            'diplomado_nombre' => $diplomadoAbierto['diplomado_nombre'],
            'capitulos' => $result
        ]);
        exit();
    }

    /**
     * Endpoint AJAX para DataTables server-side del listado de diplomados control.
     */
    public function getDiplomadosData(): void
    {
        Auth::requireLogin();

        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['error' => 'Acceso denegado.']);
            exit();
        }

        header('Content-Type: application/json');

        $draw = isset($_POST['draw']) ? (int)$_POST['draw'] : 1;
        $start = isset($_POST['start']) ? (int)$_POST['start'] : 0;
        $length = isset($_POST['length']) ? (int)$_POST['length'] : 10;
        $search = $_POST['search']['value'] ?? '';
        $order = $_POST['order'] ?? [];

        $result = $this->controlModel->getDiplomadosDataTable($draw, $start, $length, $search, $order);

        // Mapear a array indexado para DataTables
        $data = [];
        foreach ($result['data'] as $row) {
            $data[] = [
                $row['diplomado_abierto_id'],
                $row['oferta_numero'],
                $row['diplomado_nombre'],
                $row['estatus_oferta'],
                $row['total_controles'],
                $row['controles_configurados']
            ];
        }
        $result['data'] = $data;

        echo json_encode($result);
    }
}