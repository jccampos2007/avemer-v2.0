<?php

use Tests\DatabaseTestCase;
use App\Modules\Ciudad\CiudadModel;

class CiudadModelTest extends DatabaseTestCase
{
    private CiudadModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new CiudadModel();
    }

    public function test_getAll_contains_seeded(): void
    {
        $items = $this->model->getAll();
        $this->assertIsArray($items);
        $this->assertNotEmpty($items);
        $nombres = array_column($items, 'nombre');
        $this->assertContains('TEST Estado', $nombres);
    }

    public function test_getAll_excludes_soft_deleted(): void
    {
        $items = $this->model->getAll();
        $nombres = array_column($items, 'nombre');
        $this->assertNotContains('TEST Estado Deleted', $nombres);
    }

    public function test_findById_returns_estado(): void
    {
        $e = $this->model->findById(999);
        $this->assertIsArray($e);
        $this->assertSame('TEST Estado', $e['nombre']);
    }

    public function test_findById_returns_null_for_nonexistent(): void
    {
        $this->assertNull($this->model->findById(999999));
    }

    public function test_findById_returns_null_for_soft_deleted(): void
    {
        $this->assertNull($this->model->findById(997));
    }

    public function test_getAllPaises_contains_seeded(): void
    {
        $paises = $this->model->getAllPaises();
        $this->assertIsArray($paises);
        $this->assertNotEmpty($paises);
        $nombres = array_column($paises, 'nombre');
        $this->assertContains('TEST País', $nombres);
    }

    public function test_getPaginated_returns_data_in_datatables_format(): void
    {
        $result = $this->model->getPaginated([
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'search' => ['value' => ''],
            'order' => [['column' => 0, 'dir' => 'asc']],
        ]);
        $this->assertArrayHasKey('draw', $result);
        $this->assertArrayHasKey('recordsTotal', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertNotEmpty($result['data']);
    }

    public function test_create_inserts_and_returns_true(): void
    {
        $result = $this->model->create(['nombre' => 'TEST Ciudad Creada', 'pais_id' => 999]);
        $this->assertTrue($result);

        $pdo = $this->getConnection();
        $id = $pdo->query("SELECT id FROM estado WHERE nombre = 'TEST Ciudad Creada'")->fetchColumn();
        $this->assertNotFalse($id);
        $this->model->delete((int)$id);
    }

    public function test_update_modifies_record(): void
    {
        $this->assertTrue($this->model->update(999, ['nombre' => 'TEST Estado Updated', 'pais_id' => 999]));
        $this->assertSame('TEST Estado Updated', $this->model->findById(999)['nombre']);
        $this->model->update(999, ['nombre' => 'TEST Estado', 'pais_id' => 999]);
    }

    public function test_delete_soft_deletes_record(): void
    {
        $this->assertTrue($this->model->delete(999));
        $this->assertNull($this->model->findById(999));
        $this->getConnection()->exec("UPDATE estado SET deleted_at = NULL WHERE id = 999");
    }
}
