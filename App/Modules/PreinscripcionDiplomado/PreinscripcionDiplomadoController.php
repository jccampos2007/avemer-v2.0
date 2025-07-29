<?php
// app/Modules/PreinscripcionDiplomado/PreinscripcionDiplomadoController.php
namespace App\Modules\PreinscripcionDiplomado;

use App\Core\Controller; // Asume que Controller provee los helpers
use App\Core\Auth;
use App\Modules\PreinscripcionDiplomado\PreinscripcionDiplomadoModel;
use App\Modules\DiplomadoAbierto\DiplomadoAbiertoModel;
use App\Modules\Alumnos\AlumnoModel;

class PreinscripcionDiplomadoController extends Controller
{
    private $preinscripcionDiplomadoModel;
    private $diplomadoAbiertoModel;
    private $alumnoModel;
    private $preinscritoStatusId = 2; // ID del estatus "Preinscrito"

    public function __construct()
    {
        Auth::requireLogin();
        $pdo = \App\Core\Database::getInstance()->getConnection();
        $this->preinscripcionDiplomadoModel = new PreinscripcionDiplomadoModel($pdo);
        $this->diplomadoAbiertoModel = new DiplomadoAbiertoModel($pdo);
        $this->alumnoModel = new AlumnoModel();
    }

    /**
     * Muestra el formulario principal para la pre-inscripción.
     * Este módulo no tiene una vista de lista tradicional, redirige al formulario.
     */
    public function index(): void
    {
        $this->redirect('preinscripcion_diplomado/create');
    }

    /**
     * Muestra el formulario para crear una pre-inscripción.
     * También maneja la lógica de búsqueda/creación de alumnos y la pre-inscripción.
     */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processPreinscripcion(); // Cambiado a processPreinscripcion para mayor claridad
        } else {
            $data = [
                'alumno_data' => [], // Vacío al inicio
                'diplomados_abiertos' => [] // Se cargarán vía AJAX
            ];
            $this->view('PreinscripcionDiplomado/form', $data);
        }
    }

    /**
     * Endpoint AJAX para buscar un alumno por CI/Pasaporte.
     */
    public function searchAlumno(): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $ciPasaporte = $this->sanitizeInput($_POST['ci_pasapote'] ?? '');

        if (empty($ciPasaporte)) {
            echo json_encode(['success' => false, 'message' => 'CI/Pasaporte no puede estar vacío.']);
            exit();
        }

        try {
            $alumno = $this->alumnoModel->findByCiPasaporte($ciPasaporte);
            if ($alumno) {
                echo json_encode(['success' => true, 'found' => true, 'alumno' => $alumno]);
            } else {
                echo json_encode(['success' => true, 'found' => false, 'message' => 'Alumno no encontrado.']);
            }
        } catch (\PDOException $e) {
            error_log('Error de BD en searchAlumno (PreinscripcionDiplomado): ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al buscar alumno.']);
        } catch (\Exception $e) {
            error_log('Error en searchAlumno (PreinscripcionDiplomado): ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error al buscar alumno.']);
        }
        exit();
    }

    /**
     * Endpoint AJAX para crear un nuevo alumno.
     */
    public function createAlumno(): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $data = [
            'ci_pasapote' => $this->sanitizeInput($_POST['ci_pasapote'] ?? ''),
            'primer_nombre' => $this->sanitizeInput($_POST['primer_nombre'] ?? ''),
            'segundo_nombre' => $this->sanitizeInput($_POST['segundo_nombre'] ?? ''),
            'primer_apellido' => $this->sanitizeInput($_POST['primer_apellido'] ?? ''),
            'segundo_apellido' => $this->sanitizeInput($_POST['segundo_apellido'] ?? ''),
            'correo' => $this->sanitizeInput($_POST['correo'] ?? ''),
            'tlf_habitacion' => $this->sanitizeInput($_POST['tlf_habitacion'] ?? ''),
            'tlf_trabajo' => $this->sanitizeInput($_POST['tlf_trabajo'] ?? ''),
            'tlf_celular' => $this->sanitizeInput($_POST['tlf_celular'] ?? ''),
        ];

        // Validación básica
        if (empty($data['ci_pasapote']) || empty($data['primer_nombre']) || empty($data['primer_apellido'])) {
            echo json_encode(['success' => false, 'message' => 'CI/Pasaporte, Primer Nombre y Primer Apellido son obligatorios para crear un alumno.']);
            exit();
        }

        try {
            // Verificar si ya existe un alumno con ese CI/Pasaporte
            if ($this->alumnoModel->findByCiPasaporte($data['ci_pasapote'])) {
                echo json_encode(['success' => false, 'message' => 'Ya existe un alumno con este CI/Pasaporte.']);
                exit();
            }

            $alumnoId = $this->alumnoModel->create($data);
            if ($alumnoId) {
                $alumno = $this->alumnoModel->findById($alumnoId); // Obtener el alumno completo
                echo json_encode(['success' => true, 'alumno' => $alumno, 'message' => 'Alumno creado con éxito.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear el alumno.']);
            }
        } catch (\PDOException $e) {
            error_log('Error de BD en createAlumno (PreinscripcionDiplomado): ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al crear alumno: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            error_log('Error en createAlumno (PreinscripcionDiplomado): ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error al crear alumno: ' . $e->getMessage()]);
        }
        exit();
    }

    /**
     * Endpoint AJAX para obtener la lista de diplomados abiertos para pre-inscripción.
     */
    public function getDiplomadosAbiertosForPreinscripcion(): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        try {
            // Obtener todos los diplomados abiertos (o paginados si la lista es muy larga)
            // Asume que DiplomadoAbiertoModel tiene un getAll() que incluye los nombres de diplomado, sede, estatus
            // Si tu DiplomadoAbiertoModel no tiene getAllWithRelatedNames(), usa getAll() y haz los joins aquí
            $diplomadosAbiertos = $this->diplomadoAbiertoModel->getAllWithRelatedNames();

            // Formatear para una visualización simple o DataTables si se desea
            $formattedData = [];
            if (!is_array($diplomadosAbiertos)) {
                $diplomadosAbiertos = [];
            }
            foreach ($diplomadosAbiertos as $da) {
                $formattedData[] = [
                    'id' => $da['id'],
                    'numero' => htmlspecialchars($da['numero']),
                    'diplomado_nombre' => htmlspecialchars($da['diplomado_nombre'] ?? 'N/A'),
                    'sede_nombre' => htmlspecialchars($da['sede_nombre'] ?? 'N/A'),
                    'fecha_inicio' => htmlspecialchars($da['fecha_inicio']),
                    'fecha_fin' => htmlspecialchars($da['fecha_fin'])
                ];
            }
            echo json_encode(['success' => true, 'data' => $formattedData]);
        } catch (\PDOException $e) {
            error_log('Error de BD en getDiplomadosAbiertosForPreinscripcion (PreinscripcionDiplomado): ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al cargar diplomados abiertos.']);
        } catch (\Exception $e) {
            error_log('Error en getDiplomadosAbiertosForPreinscripcion (PreinscripcionDiplomado): ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error al cargar diplomados abiertos.']);
        }
        exit();
    }

    /**
     * Procesa la solicitud de pre-inscripción.
     */
    private function processPreinscripcion(): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $alumnoId = $_POST['alumno_id'] ?? null;
        $diplomadoAbiertoId = $_POST['diplomado_abierto_id'] ?? null;

        if (empty($alumnoId) || empty($diplomadoAbiertoId)) {
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar un alumno y un diplomado abierto.']);
            exit();
        }

        if ($this->preinscritoStatusId === null) {
            echo json_encode(['success' => false, 'message' => 'Error de configuración: Estatus "Preinscrito" no encontrado.']);
            exit();
        }

        $data = [
            'diplomado_abierto_id' => (int)$this->sanitizeInput($diplomadoAbiertoId),
            'alumno_id' => (int)$this->sanitizeInput($alumnoId),
            'estatus_inscripcion_id' => $this->preinscritoStatusId, // Estatus "Preinscrito"
        ];

        try {
            // Verificar si ya existe una pre-inscripción para este alumno y diplomado
            if ($this->preinscripcionDiplomadoModel->exists($data['alumno_id'], $data['diplomado_abierto_id'])) {
                echo json_encode(['success' => false, 'message' => 'Este alumno ya está pre-inscrito en este diplomado.']);
                exit();
            }

            $success = $this->preinscripcionDiplomadoModel->create($data);

            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Pre-inscripción realizada con éxito.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al realizar la pre-inscripción.']);
            }
        } catch (\PDOException $e) {
            error_log('Error de BD en processPreinscripcion (PreinscripcionDiplomado): ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al pre-inscribir: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            error_log('Error en processPreinscripcion (PreinscripcionDiplomado): ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error al pre-inscribir: ' . $e->getMessage()]);
        }
        exit();
    }
}
