<?php
// php_mvc_app/App/Api/ApiController.php
namespace App\Api;

use App\Core\Database;
use PDO;
use PDOException;

class ApiController
{

    private $pdo; // Asume que tienes una conexión a la base de datos

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // Método para obtener datos de una tabla genérica
    public function getTableData($tableName)
    {
        header('Content-Type: application/json');

        // Lista blanca de tablas permitidas para evitar inyecciones SQL
        $allowedTables = ['profesion_oficio', 'estado', 'nacionalidad', 'estatus_activo'];

        if (!in_array($tableName, $allowedTables)) {
            echo json_encode(['success' => false, 'message' => 'Tabla no permitida.']);
            exit;
        }

        try {
            // Ajusta el nombre de la columna de visualización si es necesario
            // Algunas tablas pueden usar 'nombre', otras 'descripcion', etc.
            $displayColumn = 'nombre'; // Asume 'nombre' por defecto

            $stmt = $this->pdo->prepare("SELECT id, {$displayColumn} AS text FROM {$tableName} ORDER BY {$displayColumn} ASC");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $data]);
        } catch (PDOException $e) {
            // En un entorno de producción, loguea el error, no lo muestres directamente
            echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
        }
    }

    public function searchUsers()
    {
        header('Content-Type: application/json');

        // El término de búsqueda viene en el parámetro 'term' por defecto con jQuery UI Autocomplete
        $searchTerm = $_GET['term'] ?? '';

        // Asegúrate de que el término de búsqueda tenga al menos 3 caracteres
        if (strlen($searchTerm) < 3) {
            echo json_encode([]); // Devolver un array vacío si el término es muy corto
            exit;
        }

        try {
            // Ajusta esta consulta SQL según la estructura de tu tabla 'usuarios'
            // Asumo que tienes 'id', 'primer_nombre', 'primer_apellido'
            $stmt = $this->pdo->prepare("
                SELECT id, primer_nombre, primer_apellido
                FROM usuarios
                WHERE primer_nombre LIKE :searchTerm1 OR primer_apellido LIKE :searchTerm2
                LIMIT 10
            ");
            $likeTerm = '%' . $searchTerm . '%';
            $stmt->bindParam(':searchTerm1', $likeTerm);
            $stmt->bindParam(':searchTerm2', $likeTerm);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $results = [];
            foreach ($users as $user) {
                $results[] = [
                    'label' => htmlspecialchars($user['primer_nombre'] . ' ' . $user['primer_apellido']), // Texto a mostrar en la lista
                    'value' => htmlspecialchars($user['primer_nombre'] . ' ' . $user['primer_apellido']), // Valor que se pone en el input text
                    'id'    => $user['id'] // El ID real del usuario
                ];
            }

            echo json_encode($results);
        } catch (PDOException $e) {
            error_log("Error de base de datos al buscar usuarios: " . $e->getMessage());
            echo json_encode([]); // Devolver vacío en caso de error
        }
    }
}
