<?php

use Tests\DatabaseTestCase;
use App\Modules\Diplomado\DiplomadoModel;

class DiplomadoModelTest extends DatabaseTestCase
{
    private DiplomadoModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new DiplomadoModel();
    }

    public function test_getById_returns_diplomado(): void
    {
        $d = $this->model->getById(999);
        $this->assertIsArray($d);
        $this->assertSame('TEST Diplomado', $d['nombre']);
    }

    public function test_getById_returns_false_for_nonexistent(): void
    {
        $this->assertFalse($this->model->getById(999999));
    }

    public function test_getPaginatedDiplomados_returns_datatables_format(): void
    {
        $result = $this->model->getPaginatedDiplomados([
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

    public function test_getDiplomadosAbiertos_returns_open_diplomas(): void
    {
        $abiertos = $this->model->getDiplomadosAbiertos(999);
        $this->assertIsArray($abiertos);
        $this->assertNotEmpty($abiertos);
        $this->assertSame('DA-001', $abiertos[0]['oferta_numero']);
    }

    public function test_getCapitulosByDiplomadoId_returns_chapters(): void
    {
        $caps = $this->model->getCapitulosByDiplomadoId(999);
        $this->assertIsArray($caps);
        $this->assertNotEmpty($caps);
        $this->assertSame('TEST Capítulo', $caps[0]['nombre']);
    }

    public function test_create_inserts_and_returns_true(): void
    {
        $result = $this->model->create([
            'duracion_id' => 999,
            'nombre' => 'TEST Diplomado Create',
            'descripcion' => 'Created',
            'siglas' => 'TDC',
            'costo' => 200,
            'inicial' => 100,
        ]);
        $this->assertTrue($result);

        $pdo = $this->getConnection();
        $id = $pdo->query("SELECT id FROM diplomado WHERE nombre = 'TEST Diplomado Create'")->fetchColumn();
        $this->assertNotFalse($id);
        $pdo->exec("DELETE FROM diplomado WHERE id = " . (int)$id);
    }

    public function test_update_modifies_record(): void
    {
        $this->assertTrue($this->model->update(999, [
            'duracion_id' => 999,
            'nombre' => 'TEST Diplomado Updated',
            'descripcion' => 'Updated',
            'siglas' => 'TDU',
            'costo' => 150,
            'inicial' => 75,
        ]));
        $this->assertSame('TEST Diplomado Updated', $this->model->getById(999)['nombre']);
        $this->model->update(999, [
            'duracion_id' => 999,
            'nombre' => 'TEST Diplomado',
            'descripcion' => 'Test description',
            'siglas' => 'TD',
            'costo' => 100,
            'inicial' => 50,
        ]);
    }

    public function test_delete_soft_deletes_record(): void
    {
        $this->assertTrue($this->model->delete(999));
        $this->assertFalse($this->model->getById(999));
        $this->getConnection()->exec("UPDATE diplomado SET deleted_at = NULL WHERE id = 999");
    }
}
