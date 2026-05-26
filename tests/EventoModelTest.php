<?php

use Tests\DatabaseTestCase;
use App\Modules\Evento\EventoModel;

class EventoModelTest extends DatabaseTestCase
{
    private EventoModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new EventoModel($this->getConnection());
    }

    public function test_getById_returns_evento(): void
    {
        $e = $this->model->getById(999);
        $this->assertIsArray($e);
        $this->assertSame('TEST Evento', $e['nombre']);
    }

    public function test_getById_returns_false_for_nonexistent(): void
    {
        $this->assertFalse($this->model->getById(999999));
    }

    public function test_getPaginatedEvento_returns_datatables_format(): void
    {
        $result = $this->model->getPaginatedEvento([
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

    public function test_getEventosAbiertos_returns_open_events(): void
    {
        $abiertos = $this->model->getEventosAbiertos(999);
        $this->assertIsArray($abiertos);
        $this->assertNotEmpty($abiertos);
        $this->assertSame('EA-001', $abiertos[0]['oferta_numero']);
    }

    public function test_create_inserts_and_returns_true(): void
    {
        $result = $this->model->create([
            'duracion_id' => 999,
            'nombre' => 'TEST Evento Create',
            'descripcion' => 'Created',
            'siglas' => 'TEC',
            'costo' => 60,
            'inicial' => 30,
        ]);
        $this->assertTrue($result);

        $pdo = $this->getConnection();
        $id = $pdo->query("SELECT id FROM evento WHERE nombre = 'TEST Evento Create'")->fetchColumn();
        $this->assertNotFalse($id);
        $pdo->exec("DELETE FROM evento WHERE id = " . (int)$id);
    }

    public function test_update_modifies_record(): void
    {
        $this->assertTrue($this->model->update(999, [
            'duracion_id' => 999,
            'nombre' => 'TEST Evento Updated',
            'descripcion' => 'Updated',
            'siglas' => 'TEU',
            'costo' => 90,
            'inicial' => 45,
        ]));
        $this->assertSame('TEST Evento Updated', $this->model->getById(999)['nombre']);
        $this->model->update(999, [
            'duracion_id' => 999,
            'nombre' => 'TEST Evento',
            'descripcion' => 'Test description',
            'siglas' => 'TE',
            'costo' => 80,
            'inicial' => 40,
        ]);
    }

    public function test_delete_soft_deletes_record(): void
    {
        $this->assertTrue($this->model->delete(999));
        $this->assertFalse($this->model->getById(999));
        $this->getConnection()->exec("UPDATE evento SET deleted_at = NULL WHERE id = 999");
    }
}
