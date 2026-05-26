<?php

use Tests\DatabaseTestCase;
use App\Modules\Duracion\DuracionModel;

class DuracionModelTest extends DatabaseTestCase
{
    private DuracionModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new DuracionModel();
    }

    public function test_findById_returns_duracion(): void
    {
        $d = $this->model->findById(999);
        $this->assertIsArray($d);
        $this->assertSame('TEST Duración', $d['nombre']);
    }

    public function test_findById_returns_null_for_nonexistent(): void
    {
        $this->assertNull($this->model->findById(999999));
    }

    public function test_getAll_contains_seeded(): void
    {
        $items = $this->model->getAll();
        $this->assertIsArray($items);
        $this->assertNotEmpty($items);
        $nombres = array_column($items, 'nombre');
        $this->assertContains('TEST Duración', $nombres);
    }

    public function test_create_inserts_and_returns_true(): void
    {
        $result = $this->model->create(['nombre' => 'TEST Cr']);
        $this->assertTrue($result);

        $pdo = $this->getConnection();
        $id = $pdo->query("SELECT id FROM duracion WHERE nombre = 'TEST Cr'")->fetchColumn();
        $this->assertNotFalse($id);
        $pdo->exec("DELETE FROM duracion WHERE id = " . (int)$id);
    }

    public function test_update_modifies_record(): void
    {
        $this->assertTrue($this->model->update(999, ['nombre' => 'TEST Upd']));
        $this->assertSame('TEST Upd', $this->model->findById(999)['nombre']);
        $this->model->update(999, ['nombre' => 'TEST Duración']);
    }

    public function test_delete_removes_record(): void
    {
        $this->assertTrue($this->model->delete(999));
        $this->assertNull($this->model->findById(999));
        $this->getConnection()->exec("INSERT IGNORE INTO duracion (id, nombre) VALUES (999, 'TEST Duración')");
    }
}
