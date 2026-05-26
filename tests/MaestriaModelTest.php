<?php

use Tests\DatabaseTestCase;
use App\Modules\Maestria\MaestriaModel;

class MaestriaModelTest extends DatabaseTestCase
{
    private MaestriaModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new MaestriaModel();
    }

    public function test_getById_returns_maestria(): void
    {
        $m = $this->model->getById(999);
        $this->assertIsArray($m);
        $this->assertSame('TEST Maestría', $m['nombre']);
    }

    public function test_getById_returns_false_for_nonexistent(): void
    {
        $this->assertFalse($this->model->getById(999999));
    }

    public function test_getPaginatedMaestria_returns_datatables_format(): void
    {
        $result = $this->model->getPaginatedMaestria([
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

    public function test_getMaestriasAbiertas_returns_open_maestrias(): void
    {
        $abiertas = $this->model->getMaestriasAbiertas(999);
        $this->assertIsArray($abiertas);
        $this->assertNotEmpty($abiertas);
        $this->assertSame('MA-001', $abiertas[0]['oferta_numero']);
    }

    public function test_create_inserts_and_returns_true(): void
    {
        $result = $this->model->create([
            'nombre' => 'TEST Maestria Create',
            'numero' => 'MC-001',
            'duracion_id' => 999,
            'convenio' => null,
        ]);
        $this->assertTrue($result);

        $pdo = $this->getConnection();
        $id = $pdo->query("SELECT id FROM maestria WHERE nombre = 'TEST Maestria Create'")->fetchColumn();
        $this->assertNotFalse($id);
        $pdo->exec("DELETE FROM maestria WHERE id = " . (int)$id);
    }

    public function test_update_modifies_record(): void
    {
        $this->assertTrue($this->model->update(999, [
            'nombre' => 'TEST Maestria Updated',
            'numero' => 'MU-001',
            'duracion_id' => 999,
            'convenio' => null,
        ]));
        $this->assertSame('TEST Maestria Updated', $this->model->getById(999)['nombre']);
        $this->model->update(999, [
            'nombre' => 'TEST Maestría',
            'numero' => 'M-001',
            'duracion_id' => 999,
            'convenio' => null,
        ]);
    }

    public function test_delete_soft_deletes_record(): void
    {
        $this->assertTrue($this->model->delete(999));
        $this->assertFalse($this->model->getById(999));
        $this->getConnection()->exec("UPDATE maestria SET deleted_at = NULL WHERE id = 999");
    }
}
