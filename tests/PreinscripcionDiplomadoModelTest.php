<?php

use Tests\DatabaseTestCase;
use App\Modules\PreinscripcionDiplomado\PreinscripcionDiplomadoModel;

class PreinscripcionDiplomadoModelTest extends DatabaseTestCase
{
    private PreinscripcionDiplomadoModel $model;
    private ?int $testId = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new PreinscripcionDiplomadoModel($this->getConnection());
    }

    protected function tearDown(): void
    {
        if ($this->testId !== null) {
            $this->getConnection()->exec("DELETE FROM inscripcion_diplomado WHERE id = " . $this->testId);
        }
        parent::tearDown();
    }

    public function test_create_inserts_and_returns_true(): void
    {
        $this->assertTrue($this->model->create([
            'diplomado_abierto_id' => 999,
            'alumno_id' => 999902,
            'estatus_inscripcion_id' => 1,
        ]));
        $pdo = $this->getConnection();
        $this->testId = (int)$pdo->query("SELECT id FROM inscripcion_diplomado WHERE alumno_id = 999902 AND diplomado_abierto_id = 999 ORDER BY id DESC LIMIT 1")->fetchColumn();
    }

    public function test_exists_returns_true_when_found(): void
    {
        $this->assertTrue($this->model->exists(999901, 999));
    }

    public function test_exists_returns_false_when_not_found(): void
    {
        $this->assertFalse($this->model->exists(999902, 999999));
    }
}
