<?php

use Tests\DatabaseTestCase;
use App\Modules\Banco\BancoModel;

class BancoModelTest extends DatabaseTestCase
{
    private BancoModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new BancoModel();
    }

    public function test_findById_returns_banco(): void
    {
        $banco = $this->model->findById(999);
        $this->assertIsArray($banco);
        $this->assertSame('TEST Banco', $banco['nombre']);
    }

    public function test_findById_returns_null_for_nonexistent(): void
    {
        $this->assertNull($this->model->findById(999999));
    }

    public function test_getAll_contains_seeded(): void
    {
        $bancos = $this->model->getAll();
        $this->assertIsArray($bancos);
        $this->assertNotEmpty($bancos);
        $nombres = array_column($bancos, 'nombre');
        $this->assertContains('TEST Banco', $nombres);
    }

    public function test_create_inserts_and_returns_true(): void
    {
        $result = $this->model->create(['nombre' => 'TEST Banco Create']);
        $this->assertTrue($result);

        $pdo = $this->getConnection();
        $stmt = $pdo->query("SELECT id FROM banco WHERE nombre = 'TEST Banco Create'");
        $id = $stmt->fetchColumn();
        $this->assertNotFalse($id);

        $pdo->exec("DELETE FROM banco WHERE id = " . (int)$id);
    }

    public function test_update_modifies_record(): void
    {
        $result = $this->model->update(999, ['nombre' => 'TEST Banco Updated']);
        $this->assertTrue($result);

        $banco = $this->model->findById(999);
        $this->assertSame('TEST Banco Updated', $banco['nombre']);

        $this->model->update(999, ['nombre' => 'TEST Banco']);
    }

    public function test_delete_removes_record(): void
    {
        $this->model->delete(999);
        $this->assertNull($this->model->findById(999));

        $pdo = $this->getConnection();
        $pdo->exec("INSERT IGNORE INTO banco (id, nombre) VALUES (999, 'TEST Banco')");
    }
}
