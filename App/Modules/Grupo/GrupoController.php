<?php
// app/Modules/Grupo/GrupoController.php
namespace App\Modules\Grupo;

use App\Core\Controller;
use App\Core\Auth;
use App\Modules\Grupo\GrupoModel;

class GrupoController extends Controller
{
    private $grupoModel;

    public function __construct()
    {
        Auth::requireLogin();
        $this->grupoModel = new GrupoModel();
    }

    public function index(): void
    {
        $this->view('Grupo/list');
    }

    public function create(): void
    {
        $this->view('Grupo/form', ['grupo_data' => []]);
    }

    public function edit(int $id): void
    {
        $grupo = $this->grupoModel->getGroupById($id);
        if (!$grupo) {
            $this->redirect('grupo');
        }
        $this->view('Grupo/form', ['grupo_data' => $grupo]);
    }

    public function getGroupsData(): void
    {
        $draw = $_POST['draw'] ?? 1;
        $groups = $this->grupoModel->getAllGroups();
        
        $data = [];
        foreach ($groups as $g) {
            $data[] = [
                $g['grupo_id'],
                $g['nombre_grupo'],
                $g['descripcion_grupo'],
                (!empty($g['grupo_fechareg']) && $g['grupo_fechareg'] != '0000-00-00') ? date('d/m/Y', strtotime($g['grupo_fechareg'])) : 'N/A',
                '' // For actions
            ];
        }

        echo json_encode([
            "draw" => intval($draw),
            "recordsTotal" => count($data),
            "recordsFiltered" => count($data),
            "data" => $data
        ]);
        exit;
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $data = [
                'nombre_grupo' => $this->sanitizeInput($_POST['nombre_grupo']),
                'descripcion_grupo' => $this->sanitizeInput($_POST['descripcion_grupo']),
                'usuario_idreg' => Auth::user('user_id')
            ];

            $newId = $this->grupoModel->createGroup($data);
            if ($newId) {
                echo json_encode(['success' => true, 'message' => 'Grupo creado correctamente.', 'id' => $newId]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear el grupo.']);
            }
            exit;
        }
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $data = [
                'nombre_grupo' => $this->sanitizeInput($_POST['nombre_grupo']),
                'descripcion_grupo' => $this->sanitizeInput($_POST['descripcion_grupo'])
            ];

            if ($this->grupoModel->updateGroup($id, $data)) {
                echo json_encode(['success' => true, 'message' => 'Grupo actualizado correctamente.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el grupo.']);
            }
            exit;
        }
    }

    public function delete(int $id): void
    {
        $this->validateCsrf();
        if ($this->grupoModel->deleteGroup($id)) {
            echo json_encode(['success' => true, 'message' => 'Grupo eliminado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar el grupo.']);
        }
        exit;
    }

    public function getPermissions(int $id): void
    {
        $allWindows = $this->grupoModel->getAllWindows();
        $groupPermissions = $this->grupoModel->getPermissionsByGroup($id);
        
        // Map existing permissions for easy lookup
        $mappedPermissions = [];
        foreach ($groupPermissions as $p) {
            $mappedPermissions[$p['ventana_id']] = $p;
        }

        $result = [];
        foreach ($allWindows as $w) {
            $p = $mappedPermissions[$w['ventana_id']] ?? null;
            $result[] = [
                'ventana_id' => $w['ventana_id'],
                'aplicacion_id' => $w['aplicacion_id'] ?? 1,
                'nombre_ventana' => $w['ventana_titulo'],
                'crear' => $p ? (bool)$p['permisos_crear'] : false,
                'modificar' => $p ? (bool)$p['permisos_modificar'] : false,
                'eliminar' => $p ? (bool)$p['permisos_eliminar'] : false,
                'listar' => $p ? (bool)$p['permisos_listar'] : false
            ];
        }

        echo json_encode(['success' => true, 'data' => $result]);
        exit;
    }

    public function savePermissions(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            $grupo_id = (int)$_POST['grupo_id'];
            $permissions = $_POST['permissions'] ?? [];
            
            if ($this->grupoModel->syncPermissions($grupo_id, $permissions, Auth::user('user_id'))) {
                echo json_encode(['success' => true, 'message' => 'Permisos guardados correctamente.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar los permisos.']);
            }
            exit;
        }
    }
}
