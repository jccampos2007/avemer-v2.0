<?php

use Tests\DatabaseTestCase;
use App\Modules\DiplomadoAbierto\DiplomadoAbiertoModel;

class DiplomadoAbiertoModelTest extends DatabaseTestCase
{
    private DiplomadoAbiertoModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new DiplomadoAbiertoModel();
    }

    public function test_getPaginatedDiplomadoAbierto_returns_datatables_format(): void
    {
        $result = $this->model->getPaginatedDiplomadoAbierto([
            'draw' => 1, 'start' => 0, 'length' => 10,
            'search' => ['value' => ''], 'order' => [['column' => 0, 'dir' => 'asc']],
        ]);
        $this->assertArrayHasKey('draw', $result);
        $this->assertArrayHasKey('recordsTotal', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertNotEmpty($result['data']);
    }

    public function test_getById_returns_diplomado_abierto(): void
    {
        $da = $this->model->getById(999);
        $this->assertIsArray($da);
        $this->assertSame('DA-001', $da['numero']);
    }

    public function test_getById_returns_false_for_nonexistent(): void
    {
        $this->assertFalse($this->model->getById(999999));
    }

    public function test_getAllWithRelatedNames_returns_only_active(): void
    {
        $items = $this->model->getAllWithRelatedNames();
        $this->assertIsArray($items);
        $this->assertNotEmpty($items);
        $numeros = array_column($items, 'numero');
        $this->assertContains('DA-001', $numeros);
    }

    public function test_getInscritos_returns_enrolled_students(): void
    {
        $inscritos = $this->model->getInscritos(999);
        $this->assertIsArray($inscritos);
        $this->assertNotEmpty($inscritos);
        $this->assertSame('99999901', $inscritos[0]['ci_pasapote']);
    }

    public function test_create_inserts_and_returns_true(): void
    {
        $result = $this->model->create([
            'numero' => 'DA-TEST',
            'diplomado_id' => 999,
            'sede_id' => 999,
            'estatus_id' => 1,
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-06-30',
            'nombre_carta' => 'Test Diploma',
        ]);
        $this->assertTrue($result);

        $pdo = $this->getConnection();
        $id = $pdo->query("SELECT id FROM diplomado_abierto WHERE numero = 'DA-TEST'")->fetchColumn();
        $this->assertNotFalse($id);
        $pdo->exec("DELETE FROM diplomado_abierto WHERE id = " . (int)$id);
    }

    public function test_update_modifies_record(): void
    {
        $this->assertTrue($this->model->update(999, [
            'numero' => 'DA-UPD',
            'diplomado_id' => 999,
            'sede_id' => 999,
            'estatus_id' => 2,
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-06-30',
            'nombre_carta' => 'Updated',
        ]));
        $this->assertSame('DA-UPD', $this->model->getById(999)['numero']);
        $this->model->update(999, [
            'numero' => 'DA-001',
            'diplomado_id' => 999,
            'sede_id' => 999,
            'estatus_id' => 1,
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-06-30',
            'nombre_carta' => 'Test Diploma Carta',
        ]);
    }

    public function test_delete_soft_deletes_record(): void
    {
        $this->assertTrue($this->model->delete(999));
        $this->assertFalse($this->model->getById(999));
        $this->getConnection()->exec("UPDATE diplomado_abierto SET deleted_at = NULL WHERE id = 999");
    }
}
