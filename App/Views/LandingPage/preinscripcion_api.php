<?php
/**
 * preinscripcion_api.php
 * Backend para manejar las solicitudes de preinscripción.
 */

// 1. CARGAR VARIABLES DE ENTORNO DESDE .ENV
function loadEnv($path) {
    if (!file_exists($path)) return false;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
    return true;
}

// Ajusta la ruta al archivo .env según tu estructura de carpetas
loadEnv(__DIR__ . '/../../../.env'); // Asumiendo que el .env está en la raíz del proyecto (php_mvc_app/.env)

// 2. CONFIGURACIÓN DE CABECERAS (CORS y JSON)
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *"); // En producción, limita esto a tu dominio
header("Access-Control-Allow-Methods: POST");

// 3. CONEXIÓN A LA BASE DE DATOS
try {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $db   = $_ENV['DB_NAME'] ?? 'tu_base_de_datos';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASS'] ?? '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()]);
    exit;
}

// 4. PROCESAMIENTO DE SOLICITUDES
$action = $_POST['action'] ?? '';
$typeId = $_POST['typeId'] ?? '1'; // Recibir el tipo de oferta académica (opcional, por defecto '1')

switch ($action) {
    case 'search_alumno':
        $ci = $_POST['ci_pasapote'] ?? '';
        $stmt = $pdo->prepare("SELECT * FROM alumno WHERE ci_pasapote = ? LIMIT 1");
        $stmt->execute([$ci]);
        $alumno = $stmt->fetch();

        if ($alumno) {
            echo json_encode(['success' => true, 'found' => true, 'alumno' => $alumno]);
        } else {
            echo json_encode(['success' => true, 'found' => false, 'message' => 'Alumno no registrado. Por favor, complete el formulario.']);
        }
        break;

    case 'create_alumno':
        try {
            $sql = "INSERT INTO alumno (ci_pasapote, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, correo, tlf_habitacion, tlf_celular) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['new_ci_pasapote'],
                $_POST['new_primer_nombre'],
                $_POST['new_segundo_nombre'],
                $_POST['new_primer_apellido'],
                $_POST['new_segundo_apellido'],
                $_POST['new_correo'],
                $_POST['new_tlf_habitacion'],
                $_POST['new_tlf_celular']
            ]);
            
            $newId = $pdo->lastInsertId();
            $stmt = $pdo->prepare("SELECT * FROM alumno WHERE id = ?");
            $stmt->execute([$newId]);
            $alumno = $stmt->fetch();

            echo json_encode(['success' => true, 'message' => 'Alumno registrado con exito.', 'alumno' => $alumno]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al crear alumno: ' . $e->getMessage()]);
        }
        break;

    case 'get_ofertas_abiertas':
        try {

        switch ($typeId) {
            case 1:
                $sql = "SELECT ca.id, ca.numero, ca.fecha, c.nombre, e.nombre AS estado_nombre, s.nombre AS sede_nombre 
                    FROM curso_abierto ca
                        INNER JOIN curso c ON ca.curso_id = c.id 
                        INNER JOIN sede s ON ca.sede_id = s.id
                        INNER JOIN estado e ON s.estado_id = e.id
                    WHERE ca.estatus_id = '1' ORDER BY c.nombre";
                break;
            case 2:
                $sql = "SELECT da.id, da.numero, da.diplomado_id, d.nombre, da.sede_id, s.nombre AS sede_nombre, da.estatus_id, st.nombre AS estatus_nombre, da.fecha_inicio, da.fecha_fin
                    FROM diplomado_abierto da
                        LEFT JOIN diplomado d ON da.diplomado_id = d.id
                        LEFT JOIN sede s ON da.sede_id = s.id
                        LEFT JOIN estatus st ON da.estatus_id = st.id
                    WHERE da.estatus_id = 1";
                break;
            case 3:
                $sql = "SELECT ea.id, ea.numero, e.nombre, sede.nombre AS sede_nombre, estado.nombre AS estado_nombre, ea.fecha_inicio AS fecha 
                    FROM evento e 
                        INNER JOIN evento_abierto ea ON e.id = ea.evento_id 
                        INNER JOIN sede ON ea.sede_id = sede.id 
                        INNER JOIN estado ON sede.estado_id = estado.id 
                    WHERE ea.estatus_id = 1";
                break;
            case 4:
                $sql = "SELECT ma.id, ma.numero, m.nombre, sede.nombre AS sede_nombre, estado.nombre AS estado_nombre, ma.fecha 
                    FROM maestria m 
                        INNER JOIN maestria_abierto ma ON m.id = ma.maestria_id 
                        INNER JOIN sede ON ma.sede_id = sede.id 
                        INNER JOIN estado ON sede.estado_id = estado.id 
                    WHERE ma.estatus_id = 1";
                break;
        }
            
            $stmt = $pdo->query($sql);
            $data = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al cargar diplomados.']);
        }
        break;

    case 'process_preinscripcion':
        $alumno_id = $_POST['alumno_id'] ?? null;
        $oferta_id = $_POST['oferta_abierta_id'] ?? null;

        if (!$alumno_id || !$oferta_id) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
            break;
        }

        try {
            $table = '';
            switch ($typeId) {
                case 1:
                    $table = 'curso';
                    break;
                case 2:
                    $table = 'diplomado';
                    break;
                case 3:
                    $table = 'evento';
                    break;
                case 4:
                    $table = 'maestria';
                    break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Tipo de oferta inválido.']);
                    exit;
            }

            $check = $pdo->prepare("SELECT id FROM inscripcion_{$table} WHERE alumno_id = ? AND {$table}_abierto_id = ?");
            $check->execute([$alumno_id, $oferta_id]);
            if ($check->fetch()) {
                echo json_encode(['success' => false, 'message' => 'El alumno ya se encuentra pre-inscrito en esta oferta.']);
            } else {
                $stmt = $pdo->prepare("INSERT INTO inscripcion_{$table} (alumno_id, {$table}_abierto_id, estatus_inscripcion_id) VALUES (?, ?, 1)");
                $stmt->execute([$alumno_id, $oferta_id]);
                echo json_encode(['success' => true, 'message' => '¡Pre-inscripción realizada con éxito!']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al procesar: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
        break;
}