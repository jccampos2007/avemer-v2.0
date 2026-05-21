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
     * Vista principal del módulo. Muestra los diplomados abiertos y el estado de su control.
     */
    public function index(): void
    {
        $diplomados = $this->controlModel->getDiplomadosAbiertosConControl();
        $this->view('DiplomadoControl/list', ['diplomados' => $diplomados]);
    }

    /**
     * Formulario de creación de control para un diplomado abierto.
     */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm();
        } else {
            $diplomadosAbiertos = $this->controlModel->getDiplomadosAbiertosDisponibles();
            $docentes = $this->controlModel->getDocentesActivos();
            
            $this->view('DiplomadoControl/form', [
                'diplomadosAbiertos' => $diplomadosAbiertos,
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
            $this->processForm($diplomadoAbiertoId);
        } else {
            $diplomadoAbierto = $this->controlModel->findDiplomadoAbierto($diplomadoAbiertoId);
            if (!$diplomadoAbierto) {
                Auth::setFlashMessage('error', 'El diplomado abierto solicitado no existe.');
                $this->redirect('diplomadocontrol');
            }

            $docentes = $this->controlModel->getDocentesActivos();
            $controlesExistentes = $this->controlModel->    ($diplomadoAbiertoId);

            // Si por alguna razón no tiene controles aún, lo tratamos con su diplomado base para cargar vacíos
            if (empty($controlesExistentes)) {
                $capitulos = $this->controlModel->getCapitulosPorDiplomado($diplomadoAbierto['diplomado_id']);
                $controlesExistentes = [];
                foreach ($capitulos as $cap) {
                    $controlesExistentes[] = [
                        'capitulo_id' => $cap['id'],
                        'capitulo_numero' => $cap['numero'],
                        'capitulo_nombre' => $cap['nombre'],
                        'docente_id' => null,
                        'fecha' => '',
                        'mensualidad' => 0.0,
                        'generado' => 1
                    ];
                }
            }

            $this->view('DiplomadoControl/form', [
                'diplomadoAbierto' => $diplomadoAbierto,
                'docentes' => $docentes,
                'controles' => $controlesExistentes,
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

        // Estructura de recepción de capítulos
        $capitulosData = $_POST['capitulos'] ?? [];

        try {
            // Iniciamos limpiando los registros existentes del diplomado abierto en cuestión si es actualización
            $this->controlModel->deleteControlesPorDiplomadoAbierto($diplomadoAbiertoId);

            $successCount = 0;
            foreach ($capitulosData as $capituloId => $campos) {
                $data = [
                    'diplomado_abierto_id' => $diplomadoAbiertoId,
                    'capitulo_id' => (int)$capituloId,
                    'docente_id' => !empty($campos['docente_id']) ? (int)$campos['docente_id'] : null,
                    'fecha' => !empty($campos['fecha']) ? $this->sanitizeInput($campos['fecha']) : date('Y-m-d'),
                    'mensualidad' => !empty($campos['mensualidad']) ? (float)$campos['mensualidad'] : 0.0,
                    'generado' => !empty($campos['generado']) ? (int)$campos['generado'] : 1
                ];

                if ($this->controlModel->createControl($data)) {
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
     * Endpoint AJAX para obtener los capítulos asociados a un Diplomado Abierto.
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

        $capitulos = $this->controlModel->getCapitulosPorDiplomado($diplomadoAbierto['diplomado_id']);
        echo json_encode($capitulos);
        exit();
    }
}