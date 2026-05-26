<?php

use Tests\DatabaseTestCase;
use App\Modules\CursoAbierto\CursoAbiertoModel;

class CursoAbiertoModelTest extends DatabaseTestCase
{
    private CursoAbiertoModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new CursoAbiertoModel();
    }

    protected function tearDown(): void
    {
        $this->getConnection()->exec("DELETE FROM inscripcion_curso WHERE curso_abierto_id = 999 AND alumno_id = 999901");
        parent::tearDown();
    }

    public function test_getAll_contains_seeded(): void
    {
        $items = $this->model->getAll();
        $this->assertIsArray($items);
        $this->assertNotEmpty($items);
        $numeros = array_column($items, 'numero');
        $this->assertContains('CA-001', $numeros);
    }

    public function test_findById_returns_curso_abierto(): void
    {
        $ca = $this->model->findById(999);
        $this->assertIsArray($ca);
        $this->assertSame('CA-001', $ca['numero']);
    }

    public function test_findById_returns_null_for_nonexistent(): void
    {
        $this->assertNull($this->model->findById(999999));
    }

    public function test_getInscritos_returns_enrolled_students(): void
    {
        $pdo = $this->getConnection();
        $pdo->exec("INSERT IGNORE INTO inscripcion_curso (curso_abierto_id, alumno_id, estatus_inscripcion_id) VALUES (999, 999901, 1)");

        $inscritos = $this->model->getInscritos(999);
        $this->assertIsArray($inscritos);
        $this->assertNotEmpty($inscritos);
        $this->assertSame('99999901', $inscritos[0]['ci_pasapote']);
    }

    public function test_countInscritos_returns_count(): void
    {
        $pdo = $this->getConnection();
        $pdo->exec("INSERT IGNORE INTO inscripcion_curso (curso_abierto_id, alumno_id, estatus_inscripcion_id) VALUES (999, 999901, 1)");

        $count = $this->model->countInscritos(999);
        $this->assertSame(1, $count);
    }

    public function test_getPaginatedCursoAbierto_returns_datatables_format(): void
    {
        $result = $this->model->getPaginatedCursoAbierto([
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
            'numero' => 'CA-TEST',
            'curso_id' => 99991,
            'sede_id' => 999,
            'estatus_id' => 1,
            'docente_id' => 999,
            'fecha' => '2026-06-01',
            'nombre_carta' => 'Test Create',
            'convenio' => null,
        ]);
        $this->assertTrue($result);

        $pdo = $this->getConnection();
        $id = $pdo->query("SELECT id FROM curso_abierto WHERE numero = 'CA-TEST'")->fetchColumn();
        $this->assertNotFalse($id);
        $pdo->exec("DELETE FROM curso_abierto WHERE id = " . (int)$id);
    }

    public function test_update_modifies_record(): void
    {
        $this->assertTrue($this->model->update(999, [
            'numero' => 'CA-UPD',
            'curso_id' => 99991,
            'sede_id' => 999,
            'estatus_id' => 1,
            'docente_id' => 999,
            'fecha' => '2026-01-01',
            'nombre_carta' => 'Updated',
            'convenio' => null,
        ]));
        $this->assertSame('CA-UPD', $this->model->findById(999)['numero']);
        $this->model->update(999, [
            'numero' => 'CA-001',
            'curso_id' => 99991,
            'sede_id' => 999,
            'estatus_id' => 1,
            'docente_id' => 999,
            'fecha' => '2026-01-01',
            'nombre_carta' => 'Test Carta',
            'convenio' => 'CONV-001',
        ]);
    }

    public function test_delete_soft_deletes_record(): void
    {
        $this->assertTrue($this->model->delete(999));
        $this->assertNull($this->model->findById(999));
        $this->getConnection()->exec("UPDATE curso_abierto SET deleted_at = NULL WHERE id = 999");
    }
}
