<?php

use Tests\DatabaseTestCase;
use App\Modules\Grupo\GrupoModel;

class GrupoModelTest extends DatabaseTestCase
{
    private GrupoModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new GrupoModel();
    }

    public function test_getGroupById_returns_group(): void
    {
        $g = $this->model->getGroupById(999);
        $this->assertIsArray($g);
        $this->assertSame('TEST Grupo', $g['nombre_grupo']);
    }

    public function test_getGroupById_returns_false_for_nonexistent(): void
    {
        $this->assertFalse($this->model->getGroupById(999999));
    }

    public function test_getAllGroups_contains_seeded(): void
    {
        $groups = $this->model->getAllGroups();
        $this->assertIsArray($groups);
        $this->assertNotEmpty($groups);
        $names = array_column($groups, 'nombre_grupo');
        $this->assertContains('TEST Grupo', $names);
    }

    public function test_createGroup_inserts_and_returns_id(): void
    {
        $id = $this->model->createGroup([
            'nombre_grupo' => 'TEST Grupo Create',
            'descripcion_grupo' => 'Created group',
            'usuario_idreg' => 1,
        ]);
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        $g = $this->model->getGroupById($id);
        $this->assertIsArray($g);
        $this->assertSame('TEST Grupo Create', $g['nombre_grupo']);

        $this->model->deleteGroup($id);
    }

    public function test_updateGroup_modifies_record(): void
    {
        $result = $this->model->updateGroup(999, [
            'nombre_grupo' => 'TEST Grupo Updated',
            'descripcion_grupo' => 'Updated group',
        ]);
        $this->assertTrue($result);

        $g = $this->model->getGroupById(999);
        $this->assertSame('TEST Grupo Updated', $g['nombre_grupo']);

        $this->model->updateGroup(999, [
            'nombre_grupo' => 'TEST Grupo',
            'descripcion_grupo' => 'Test group',
        ]);
    }

    public function test_deleteGroup_cascades_to_permissions(): void
    {
        $this->assertTrue($this->model->deleteGroup(999));
        $this->assertFalse($this->model->getGroupById(999));

        $pdo = $this->getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM permisos WHERE grupo_id = 999");
        $this->assertEquals(0, $stmt->fetchColumn());

        // Restore
        $pdo->exec("INSERT IGNORE INTO grupo (grupo_id, nombre_grupo, descripcion_grupo, usuario_idreg, grupo_fechareg) VALUES (999, 'TEST Grupo', 'Test group', 1, CURDATE())");
        $pdo->exec("INSERT IGNORE INTO permisos (grupo_id, ventana_id, aplicacion_id, permisos_crear, permisos_modificar, permisos_eliminar, permisos_listar, usuario_idreg, permisos_fechareg) VALUES (999, 999, 999, 1, 1, 1, 1, 1, CURDATE())");
    }

    public function test_getPermissionsByGroup_returns_permissions(): void
    {
        $perms = $this->model->getPermissionsByGroup(999);
        $this->assertIsArray($perms);
        $this->assertNotEmpty($perms);
        $this->assertSame('TEST Ventana', $perms[0]['ventana_titulo']);
    }

    public function test_getAllWindows_returns_windows(): void
    {
        $windows = $this->model->getAllWindows();
        $this->assertIsArray($windows);
        $this->assertNotEmpty($windows);
        $titles = array_column($windows, 'ventana_titulo');
        $this->assertContains('TEST Ventana', $titles);
    }
}
