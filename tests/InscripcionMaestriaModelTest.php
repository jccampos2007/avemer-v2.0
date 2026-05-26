<?php

use Tests\DatabaseTestCase;
use App\Modules\InscripcionMaestria\InscripcionMaestriaModel;

class InscripcionMaestriaModelTest extends DatabaseTestCase
{
    private InscripcionMaestriaModel $model;
    private ?int $testId = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new InscripcionMaestriaModel($this->getConnection());
    }

    protected function tearDown(): void
    {
        if ($this->testId !== null) {
            $this->getConnection()->exec("DELETE FROM inscripcion_maestria WHERE id = " . $this->testId);
        }
        parent::tearDown();
    }

    private function insertTestRecord(int $estatusId = 1): int
    {
        $pdo = $this->getConnection();
        $pdo->exec("INSERT IGNORE INTO inscripcion_maestria (maestria_abierto_id, alumno_id, estatus_inscripcion_id) VALUES (999, 999901, $estatusId)");
        return (int)$pdo->lastInsertId();
    }

    public function test_getPaginatedInscripcionMaestria_returns_datatables_format(): void
    {
        $result = $this->model->getPaginatedInscripcionMaestria([
            'draw' => 1, 'start' => 0, 'length' => 10,
            'search' => ['value' => ''], 'order' => [['column' => 0, 'dir' => 'asc']],
        ]);
        $this->assertArrayHasKey('draw', $result);
        $this->assertArrayHasKey('recordsTotal', $result);
        $this->assertArrayHasKey('data', $result);
    }

    public function test_getById_returns_inscripcion(): void
    {
        $this->testId = $this->insertTestRecord();
        $r = $this->model->getById($this->testId);
        $this->assertIsArray($r);
        $this->assertSame(999, (int)$r['maestria_abierto_id']);
    }

    public function test_getById_returns_false_for_nonexistent(): void
    {
        $this->assertFalse($this->model->getById(999999));
    }

    public function test_create_inserts_and_returns_true(): void
    {
        $this->assertTrue($this->model->create([
            'maestria_abierto_id' => 999,
            'alumno_id' => 999901,
            'estatus_inscripcion_id' => 1,
        ]));
        $pdo = $this->getConnection();
        $this->testId = (int)$pdo->query("SELECT id FROM inscripcion_maestria WHERE alumno_id = 999901 AND maestria_abierto_id = 999 ORDER BY id DESC LIMIT 1")->fetchColumn();
    }

    public function test_exists_returns_true_when_found(): void
    {
        $this->testId = $this->insertTestRecord();
        $this->assertTrue($this->model->exists(999901, 999));
    }

    public function test_exists_returns_false_when_not_found(): void
    {
        $this->assertFalse($this->model->exists(999902, 999999));
    }

    public function test_update_modifies_record(): void
    {
        $this->testId = $this->insertTestRecord(2);
        $this->assertTrue($this->model->update($this->testId, [
            'maestria_abierto_id' => 999,
            'alumno_id' => 999901,
            'estatus_inscripcion_id' => 1,
        ]));
        $r = $this->model->getById($this->testId);
        $this->assertSame(1, (int)$r['estatus_inscripcion_id']);
    }

    public function test_delete_removes_record(): void
    {
        $this->testId = $this->insertTestRecord();
        $this->assertTrue($this->model->delete($this->testId));
        $this->assertFalse($this->model->getById($this->testId));
        $this->testId = null;
    }
}
