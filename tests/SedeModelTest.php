<?php

use Tests\DatabaseTestCase;
use App\Modules\Sede\SedeModel;

class SedeModelTest extends DatabaseTestCase
{
    private SedeModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new SedeModel();
    }

    public function test_getAll_contains_seeded(): void
    {
        $items = $this->model->getAll();
        $this->assertIsArray($items);
        $this->assertNotEmpty($items);
        $nombres = array_column($items, 'nombre');
        $this->assertContains('TEST Sede', $nombres);
    }

    public function test_findById_returns_sede(): void
    {
        $s = $this->model->findById(999);
        $this->assertIsArray($s);
        $this->assertSame('TEST Sede', $s['nombre']);
    }

    public function test_findById_returns_null_for_nonexistent(): void
    {
        $this->assertNull($this->model->findById(999999));
    }

    public function test_getPaginatedSedes_returns_data_in_datatables_format(): void
    {
        $result = $this->model->getPaginatedSedes([
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
        $result = $this->model->create([
            'nombre' => 'TEST Sede Creada',
            'estado_id' => 999,
            'tlf_sede' => '1111',
            'correo' => 'test@sedecreate.com',
        ]);
        $this->assertTrue($result);

        $pdo = $this->getConnection();
        $id = $pdo->query("SELECT id FROM sede WHERE nombre = 'TEST Sede Creada'")->fetchColumn();
        $this->assertNotFalse($id);
        $pdo->exec("DELETE FROM sede WHERE id = " . (int)$id);
    }

    public function test_update_modifies_record(): void
    {
        $this->assertTrue($this->model->update(999, [
            'nombre' => 'TEST Sede Updated',
            'estado_id' => 999,
            'tlf_sede' => '2222',
            'correo' => 'updated@sede.com',
        ]));
        $this->assertSame('TEST Sede Updated', $this->model->findById(999)['nombre']);
        $this->model->update(999, [
            'nombre' => 'TEST Sede',
            'estado_id' => 999,
            'tlf_sede' => '0000',
            'correo' => 'test@sede.com',
        ]);
    }

    public function test_delete_removes_record(): void
    {
        $this->assertTrue($this->model->delete(999));
        $this->assertNull($this->model->findById(999));
        $this->getConnection()->exec("INSERT IGNORE INTO sede (id, estado_id, nombre, tlf_sede, correo) VALUES (999, 999, 'TEST Sede', '0000', 'test@sede.com')");
    }
}
