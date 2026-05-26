<?php

use Tests\DatabaseTestCase;
use App\Modules\EventoAbierto\EventoAbiertoModel;

class EventoAbiertoModelTest extends DatabaseTestCase
{
    private EventoAbiertoModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new EventoAbiertoModel();
    }

    public function test_getPaginatedEventoAbierto_returns_datatables_format(): void
    {
        $result = $this->model->getPaginatedEventoAbierto([
            'draw' => 1, 'start' => 0, 'length' => 10,
            'search' => ['value' => ''], 'order' => [['column' => 0, 'dir' => 'asc']],
        ]);
        $this->assertArrayHasKey('draw', $result);
        $this->assertArrayHasKey('recordsTotal', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertNotEmpty($result['data']);
    }

    public function test_getById_returns_evento_abierto(): void
    {
        $ea = $this->model->getById(999);
        $this->assertIsArray($ea);
        $this->assertSame('EA-001', $ea['numero']);
    }

    public function test_getById_returns_false_for_nonexistent(): void
    {
        $this->assertFalse($this->model->getById(999999));
    }

    public function test_getInscritos_returns_enrolled_students(): void
    {
        $inscritos = $this->model->getInscritos(999);
        $this->assertIsArray($inscritos);
        $this->assertNotEmpty($inscritos);
        $this->assertSame('99999901', $inscritos[0]['ci_pasapote']);
    }

    public function test_countInscritos_returns_count(): void
    {
        $count = $this->model->countInscritos(999);
        $this->assertSame(1, $count);
    }

    public function test_create_inserts_and_returns_true(): void
    {
        $result = $this->model->create([
            'numero' => 'EA-TEST',
            'evento_id' => 999,
            'sede_id' => 999,
            'estatus_id' => 1,
            'docente_id' => 999,
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-01-05',
            'nombre_carta' => 'Test Event',
        ]);
        $this->assertTrue($result);

        $pdo = $this->getConnection();
        $id = $pdo->query("SELECT id FROM evento_abierto WHERE numero = 'EA-TEST'")->fetchColumn();
        $this->assertNotFalse($id);
        $pdo->exec("DELETE FROM evento_abierto WHERE id = " . (int)$id);
    }

    public function test_update_modifies_record(): void
    {
        $this->assertTrue($this->model->update(999, [
            'numero' => 'EA-UPD',
            'evento_id' => 999,
            'sede_id' => 999,
            'estatus_id' => 2,
            'docente_id' => 999,
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-01-05',
            'nombre_carta' => 'Updated',
        ]));
        $this->assertSame('EA-UPD', $this->model->getById(999)['numero']);
        $this->model->update(999, [
            'numero' => 'EA-001',
            'evento_id' => 999,
            'sede_id' => 999,
            'estatus_id' => 1,
            'docente_id' => 999,
            'fecha_inicio' => '2026-03-01',
            'fecha_fin' => '2026-03-05',
            'nombre_carta' => 'Test Event Carta',
        ]);
    }

    public function test_delete_soft_deletes_record(): void
    {
        $this->assertTrue($this->model->delete(999));
        $this->assertFalse($this->model->getById(999));
        $this->getConnection()->exec("UPDATE evento_abierto SET deleted_at = NULL WHERE id = 999");
    }
}
