<?php

use Tests\DatabaseTestCase;
use App\Modules\Users\UserModel;

class UserModelTest extends DatabaseTestCase
{
    private UserModel $model;
    private ?int $testId = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new UserModel();
    }

    protected function tearDown(): void
    {
        if ($this->testId !== null) {
            $this->getConnection()->exec("DELETE FROM usuario WHERE usuario_id = " . $this->testId);
        }
        $this->getConnection()->exec("DELETE FROM usuario WHERE usuario_user = 'testcreated'");
        parent::tearDown();
    }

    public function test_getAll_returns_array(): void
    {
        $users = $this->model->getAll();
        $this->assertIsArray($users);
        $this->assertNotEmpty($users);
    }

    public function test_getPaginatedUsers_returns_datatables_format(): void
    {
        $result = $this->model->getPaginatedUsers([
            'draw' => 1, 'start' => 0, 'length' => 10,
            'search' => ['value' => ''], 'order' => [['column' => 0, 'dir' => 'asc']],
        ]);
        $this->assertArrayHasKey('draw', $result);
        $this->assertArrayHasKey('recordsTotal', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertNotEmpty($result['data']);
    }

    public function test_findById_returns_user(): void
    {
        $user = $this->model->findById(999);
        $this->assertIsArray($user);
        $this->assertSame('testuser', $user['usuario_user']);
    }

    public function test_findById_returns_null_for_nonexistent(): void
    {
        $this->assertNull($this->model->findById(999999));
    }

    public function test_findByUsername_returns_user(): void
    {
        $user = $this->model->findByUsername('testuser');
        $this->assertIsArray($user);
        $this->assertSame(999, (int)$user['usuario_id']);
    }

    public function test_findByUsername_returns_null_for_nonexistent(): void
    {
        $this->assertNull($this->model->findByUsername('nonexistent_user'));
    }

    public function test_create_inserts_and_returns_true(): void
    {
        $this->assertTrue($this->model->create([
            'usuario_cedula' => 'V-TEST-001',
            'usuario_nombre' => 'TestCreated',
            'usuario_apellido' => 'User',
            'usuario_user' => 'testcreated',
            'usuario_pws' => password_hash('password', PASSWORD_DEFAULT),
            'estatus_activo_id' => 1,
            'grupo_id' => 999,
            'usuario_idreg' => 1,
            'usuario_fechareg' => date('Y-m-d'),
        ]));
        $pdo = $this->getConnection();
        $this->testId = (int)$pdo->query("SELECT usuario_id FROM usuario WHERE usuario_user = 'testcreated'")->fetchColumn();
    }

    public function test_update_modifies_record(): void
    {
        $this->assertTrue($this->model->update(999, [
            'usuario_cedula' => 'V-999',
            'usuario_nombre' => 'Modified',
            'usuario_apellido' => 'User',
            'usuario_user' => 'testuser',
            'estatus_activo_id' => 1,
            'grupo_id' => 999,
            'id_persona' => null,
        ]));
        $user = $this->model->findById(999);
        $this->assertSame('Modified', $user['usuario_nombre']);
        $this->getConnection()->exec("UPDATE usuario SET usuario_nombre = 'Test' WHERE usuario_id = 999");
    }

    public function test_delete_removes_record(): void
    {
        $pdo = $this->getConnection();
        $pdo->exec("INSERT INTO usuario (usuario_cedula, usuario_nombre, usuario_apellido, usuario_user, usuario_pws, estatus_activo_id, grupo_id, usuario_idreg, usuario_fechareg) VALUES ('V-DEL', 'Delete', 'Test', 'testdel', '" . password_hash('pwd', PASSWORD_DEFAULT) . "', 1, 999, 1, CURDATE())");
        $id = (int)$pdo->query("SELECT usuario_id FROM usuario WHERE usuario_user = 'testdel'")->fetchColumn();
        $this->assertTrue($this->model->delete($id));
        $this->assertNull($this->model->findById($id));
    }
}
