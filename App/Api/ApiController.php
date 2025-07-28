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

        $displayColumn = $_GET['displayColumn'] ?? 'nombre';
        $requestedStatusFilterColumn = $_GET['statusFilter'] ?? null;
        $where = $requestedStatusFilterColumn ? "WHERE {$_GET['statusFilter']} = '1'" : '';

        $allowedTables = ['profesion_oficio', 'estado', 'nacionalidad', 'estatus_activo', 'docente', 'curso', 'sede', 'estatus', 'curso_abierto', 'alumno', 'estatus_inscripcion', 'duracion', 'evento'];

        if (!in_array($tableName, $allowedTables)) {
            echo json_encode(['success' => false, 'message' => "Tabla no permitida {$tableName} {$displayColumn} {$where}."]);
            exit;
        }

        try {
            $stmt = $this->pdo->prepare("SELECT id, {$displayColumn} AS text FROM {$tableName} {$where} ORDER BY {$displayColumn} ASC");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $data]);
        } catch (PDOException $e) {
            error_log('Database Error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error de base de datos. Por favor, intente más tarde.']);
        }
    }

    /**
     * Método genérico para obtener datos para autocompletado desde cualquier tabla.
     * Permite especificar la columna a mostrar, las columnas a buscar y el filtro de estatus activo.
     *
     * @param string $tableName El nombre de la tabla de la que se desean obtener los datos.
     * @return void Envía una respuesta JSON.
     */
    public function getAutocompleteData(string $tableName): void
    {
        header('Content-Type: application/json');

        $searchTerm = $_GET['term'] ?? '';
        $displayColumn = $_GET['displayColumn'] ?? 'nombre'; // Columna para mostrar en 'label' y 'value'

        // Configuración de tablas permitidas para autocompletado.
        // Incluye:
        // - 'display_columns': Columnas válidas para mostrar en el autocomplete.
        // - 'search_columns': Columnas en las que se realizará la búsqueda LIKE.
        // - 'status_column': (Opcional) Columna para filtrar por estado activo (ej. 'estatus_activo_id').
        // Las tablas que son "tablas de estatus" (ej. estatus_activo) no se filtran por sí mismas.
        $allowedTablesConfig = [
            'docente' => [
                'display_columns' => ['primer_nombre', 'primer_apellido', 'CONCAT(primer_apellido, ", ", primer_nombre)'],
                'search_columns' => ['primer_nombre', 'primer_apellido'],
                'status_column' => 'estatus_activo_id'
            ],
            'alumno' => [
                'display_columns' => ['primer_nombre', 'primer_apellido', 'CONCAT(primer_apellido, ", ", primer_nombre)'],
                'search_columns' => ['primer_nombre', 'primer_apellido', 'ci_pasapote'], // Puedes añadir más columnas de búsqueda
                'status_column' => 'estatus_activo_id'
            ],
        ];

        // 1. Validar que la tabla solicitada esté permitida y configurada para autocompletado
        if (!isset($allowedTablesConfig[$tableName])) {
            echo json_encode(['success' => false, 'message' => 'Tabla no configurada para autocompletado.']);
            exit;
        }

        $tableConfig = $allowedTablesConfig[$tableName];
        $searchColumns = $tableConfig['search_columns'];
        $statusColumn = $tableConfig['status_column'] ?? null;

        // 2. Validar que la columna de visualización solicitada sea permitida para esta tabla.
        //    Si no es válida, se intenta usar la primera columna de visualización definida.
        if (!in_array($displayColumn, $tableConfig['display_columns'])) {
            $displayColumn = $tableConfig['display_columns'][0] ?? 'id'; // Fallback
            error_log("Advertencia: Columna de visualización inválida solicitada para {$tableName}. Usando por defecto: {$displayColumn}");
        }

        // 3. Requerir un término de búsqueda mínimo
        if (strlen($searchTerm) < 3) {
            echo json_encode([]);
            exit;
        }

        try {
            $sql = "SELECT id, {$displayColumn} AS text FROM {$tableName}";
            $whereClauses = [];
            $queryParams = [];
            $likeTerm = '%' . $searchTerm . '%';
            $paramIndex = 0; // Contador para nombres de parámetros únicos

            // Construir cláusulas de búsqueda dinámica
            $searchConditions = [];
            foreach ($searchColumns as $col) {
                $paramName = ":search_param{$paramIndex}"; // Generar un nombre de parámetro único
                $searchConditions[] = "{$col} LIKE {$paramName}";
                $queryParams[$paramName] = $likeTerm;
                $paramIndex++;
            }
            if (!empty($searchConditions)) {
                $whereClauses[] = "(" . implode(' OR ', $searchConditions) . ")";
            }

            // Aplicar filtro por estatus activo si la tabla tiene una columna de estado definida
            // y si la tabla no es una de las tablas que *definen* estados
            if ($statusColumn && !in_array($tableName, ['estatus_activo', 'estatus', 'estatus_inscripcion'])) {
                $whereClauses[] = "{$statusColumn} = :status_id";
                $queryParams[':status_id'] = 1; // Asumimos que '1' significa activo
            }

            // Construir la cláusula WHERE final
            if (!empty($whereClauses)) {
                $sql .= " WHERE " . implode(' AND ', $whereClauses);
            }

            $sql .= " LIMIT 10"; // Limitar resultados para autocompletado

            // Preparar y ejecutar la consulta
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($queryParams);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $results = [];
            foreach ($data as $item) {
                $results[] = [
                    'label' => htmlspecialchars($item['text']), // Texto a mostrar en la lista
                    'value' => htmlspecialchars($item['text']), // Valor que se pone en el input text
                    'id'    => $item['id'] // El ID real del item
                ];
            }

            echo json_encode($results);
        } catch (PDOException $e) {
            error_log("Error de base de datos en getAutocompleteData para {$tableName}: " . $e->getMessage());
            echo json_encode([]); // Devolver vacío en caso de error
        }
    }
}
