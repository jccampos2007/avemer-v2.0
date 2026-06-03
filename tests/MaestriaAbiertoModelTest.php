<?php

use Tests\DatabaseTestCase;
use App\Modules\MaestriaAbierto\MaestriaAbiertoModel;

class MaestriaAbiertoModelTest extends DatabaseTestCase
{
    private MaestriaAbiertoModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new MaestriaAbiertoModel();
    }

    public function test_getPaginatedMaestriaAbierto_returns_datatables_format(): void
    {
        $result = $this->model->getPaginatedMaestriaAbierto([
            'draw' => 1, 'start' => 0, 'length' => 10,
            'search' => ['value' => ''], 'order' => [['column' => 0, 'dir' => 'asc']],
        ]);
        $this->assertArrayHasKey('draw', $result);
        $this->assertArrayHasKey('recordsTotal', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertNotEmpty($result['data']);
    }

    public function test_getById_returns_maestria_abierto(): void
    {
        $ma = $this->model->getById(999);
        $this->assertIsArray($ma);
        $this->assertSame('MA-001', $ma['numero']);
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
        $this->assertSame('99999901', $inscritos[0]['ci_pasaporte']);
    }

    public function test_countInscritos_returns_count(): void
    {
        $count = $this->model->countInscritos(999);
        $this->assertSame(1, $count);
    }

    public function test_create_inserts_and_returns_true(): void
    {
        $result = $this->model->create([
            'numero' => 'MA-TEST',
            'maestria_id' => 999,
            'sede_id' => 999,
            'estatus_id' => 1,
            'docente_id' => 999,
            'fecha' => '2026-01-01',
            'nombre_carta' => 'Test Master',
            'convenio' => null,
            'costo' => 5000,
            'inicial' => 1000,
        ]);
        $this->assertTrue($result);

        $pdo = $this->getConnection();
        $id = $pdo->query("SELECT id FROM maestria_abierto WHERE numero = 'MA-TEST'")->fetchColumn();
        $this->assertNotFalse($id);
        $pdo->exec("DELETE FROM maestria_abierto WHERE id = " . (int)$id);
    }

    public function test_update_modifies_record(): void
    {
        $this->assertTrue($this->model->update(999, [
            'numero' => 'MA-UPD',
            'maestria_id' => 999,
            'sede_id' => 999,
            'estatus_id' => 2,
            'docente_id' => 999,
            'fecha' => '2026-01-01',
            'nombre_carta' => 'Updated',
            'convenio' => null,
            'costo' => 6000,
            'inicial' => 1200,
        ]));
        $this->assertSame('MA-UPD', $this->model->getById(999)['numero']);
        $this->model->update(999, [
            'numero' => 'MA-001',
            'maestria_id' => 999,
            'sede_id' => 999,
            'estatus_id' => 1,
            'docente_id' => 999,
            'fecha' => '2026-01-01',
            'nombre_carta' => 'Test Master Carta',
            'convenio' => null,
            'costo' => 5000,
            'inicial' => 1000,
        ]);
    }

    public function test_delete_soft_deletes_record(): void
    {
        $this->assertTrue($this->model->delete(999));
        $this->assertFalse($this->model->getById(999));
        $this->getConnection()->exec("UPDATE maestria_abierto SET deleted_at = NULL WHERE id = 999");
    }
}
