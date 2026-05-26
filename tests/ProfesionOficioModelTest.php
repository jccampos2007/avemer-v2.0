<?php

use Tests\DatabaseTestCase;
use App\Modules\ProfesionOficio\ProfesionOficioModel;

class ProfesionOficioModelTest extends DatabaseTestCase
{
    private ProfesionOficioModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new ProfesionOficioModel();
    }

    public function test_findById_returns_profesion(): void
    {
        $p = $this->model->findById(999);
        $this->assertIsArray($p);
        $this->assertStringContainsString('Profesión', $p['nombre']);
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
    }

    public function test_create_inserts_and_returns_true(): void
    {
        $result = $this->model->create(['nombre' => 'TEST Prof Create']);
        $this->assertTrue($result);

        $pdo = $this->getConnection();
        $id = $pdo->query("SELECT id FROM profesion_oficio WHERE nombre = 'TEST Prof Create'")->fetchColumn();
        $this->assertNotFalse($id);
        $pdo->exec("DELETE FROM profesion_oficio WHERE id = " . (int)$id);
    }

    public function test_update_modifies_record(): void
    {
        $this->assertTrue($this->model->update(999, ['nombre' => 'TEST Prof Updated']));
        $this->assertSame('TEST Prof Updated', $this->model->findById(999)['nombre']);
        $this->model->update(999, ['nombre' => 'TEST Profesión']);
    }

    public function test_delete_removes_record(): void
    {
        $this->assertTrue($this->model->delete(999));
        $this->assertNull($this->model->findById(999));
        $this->getConnection()->exec("INSERT IGNORE INTO profesion_oficio (id, nombre) VALUES (999, 'TEST Profesión')");
    }
}
