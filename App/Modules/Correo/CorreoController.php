<?php
// app/Modules/Correo/CorreoController.php
namespace App\Modules\Correo;

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\Correo\CorreoModel;

class CorreoController extends Controller
{
    private $correoModel;

    public function __construct()
    {
        Auth::requireLogin(); // Asegura que el usuario esté logueado
        $this->correoModel = new CorreoModel();
    }

    /**
     * Muestra la lista de registros de Correo (opcional, si tienes una vista de lista).
     * Por ahora, solo redirigiremos a la creación/edición.
     */
    public function index(): void
    {
        // Puedes implementar una vista de lista similar a DiplomadoAbierto/list.php si es necesario.
        // Por ahora, solo redirigimos a la creación para simplificar el ejemplo.
        $this->redirect('correo/create');
    }

    /**
     * Exibe el formulario para crear un nuevo registro de Correo
     * o procesa el envío del formulario.
     */
    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processForm();
        } else {
            $correo_data = []; // Datos vacíos para el formulario de creación
            $this->view('Correo/form', ['correo_data' => $correo_data]);
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
                'oferta_academica_id' => (int)$this->sanitizeInput($_POST['oferta_academica_id']),
                'tipo_oferta_academica_id' => (int)$this->sanitizeInput($_POST['tipo_oferta_academica_id']),
                'buscar_mensajes' => (int)$this->sanitizeInput($_POST['buscar_mensajes'])
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
                    $redirectPath = 'correo/create';
                    $this->redirect($redirectPath);
                    return;
                }
            }
            

        } catch (\PDOException $e) {
            error_log('Error de BD en processForm (Correo): ' . $e->getMessage());
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
                exit();
            } else {
                Auth::setFlashMessage('error', 'Error de base de datos: ' . $e->getMessage());
                $redirectPath = $id ? 'correo/edit/' . $id : 'correo/create';
                $this->redirect($redirectPath);
            }
        } catch (\Exception $e) {
            error_log('Error en processForm (Correo): ' . $e->getMessage());
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Ocurrió un error: ' . $e->getMessage()]);
                exit();
            } else {
                Auth::setFlashMessage('error', 'Ocurrió un error: ' . $e->getMessage());
                $redirectPath = $id ? 'correo/edit/' . $id : 'correo/create';
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
                    $data = $this->correoModel->getCursos();
                    break;
                case '2':
                    $data = $this->correoModel->getDiplomados();
                    break;
                case '3':
                    $data = $this->correoModel->getEventos();
                    break;
                case '4':
                    $data = $this->correoModel->getMaestrias();
                    break;
                default:
                    // Si no se especifica un tipo válido, devuelve un array vacío
                    break;
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $data]);
            exit();
        } catch (\PDOException $e) {
            error_log('Error de BD en getAcademicOffersByType (Correo): ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al obtener ofertas académicas.']);
            exit();
        }
    }

    public function getMensajes(): void
    {
        Auth::requireLogin();

        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['error' => 'Acceso denegado.']);
            exit();
        }
        $data = [];

        try {
            
            $data = $this->correoModel->getMensajes();

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $data]);
            exit();
        } catch (\PDOException $e) {
            error_log('Error de BD en getMensajes (Correo): ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al obtener mensajes.']);
            exit();
        }
    }

    /**
     * Endpoint AJAX para obtener correos por tipo de oferta y ID de oferta.
     * @param int $tipoOfertaId
     * @param int $ofertaId
     */
    public function getCorreosByOfferData(): void
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
            $correos = $this->correoModel->getCorreosByOffer((int)$tipoOfertaId, (int)$ofertaId);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $correos]);
            exit();
        } catch (\PDOException $e) {
            error_log('Error de BD en getCorreosByOfferData (Correo): ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al obtener correos por oferta: ' . $e->getMessage()]);
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
        $correoId = $_GET['correo_id'] ?? null;

        if (empty($tipoOfertaId) || empty($ofertaId) || empty($correoId)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Faltan parámetros de oferta o correo.']);
            exit();
        }

        try {
            $students = $this->correoModel->getStudentsByOffer((int)$tipoOfertaId, (int)$ofertaId);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $students, 'correo_id' => (int)$correoId]);
            exit();
        } catch (\PDOException $e) {
            error_log('Error de BD en getStudentsForDebtGeneration (Correo): ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al obtener alumnos: ' . $e->getMessage()]);
            exit();
        }
    }

    public function sendChecked(): void
    {
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
            exit();
        }

        $correos = $_POST['correos'] ?? [];
        $mensajeId = (int)($_POST['mensaje_id'] ?? 0);

        if (empty($correos) || $mensajeId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Faltan correos o mensaje.']);
            exit();
        }

        $mensaje = $this->correoModel->getMensajeById($mensajeId);
        if (empty($mensaje)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Mensaje no encontrado.']);
            exit();
        }

        require_once __DIR__ . '/enviar.php';

        foreach ($correos as $correo) {
            $result = correo($mensaje['titulo'], $mensaje['mensaje'], $correo);
            
            $data = [
                'correo'    => $correo,
                'id_mensaje' => $mensajeId,
                'respose'    => $result
            ];
            $this->correoModel-> insertLog($data);
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Correos enviados correctamente.']);
        exit();
    }
}
