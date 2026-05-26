<?php

use Tests\DatabaseTestCase;
use App\Modules\DiplomadoControl\DiplomadoControlModel;

class DiplomadoControlModelTest extends DatabaseTestCase
{
    private DiplomadoControlModel $model;
    private ?int $testId = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new DiplomadoControlModel();
    }

    protected function tearDown(): void
    {
        if ($this->testId !== null) {
            $this->getConnection()->exec("DELETE FROM diplomado_control WHERE id = " . $this->testId);
        }
        $this->getConnection()->exec("DELETE FROM diplomado_control WHERE diplomado_abierto_id = 999 AND capitulo_id = 999");
        parent::tearDown();
    }

    private function insertTestRecord(): int
    {
        $pdo = $this->getConnection();
        $pdo->exec("INSERT IGNORE INTO diplomado_control (diplomado_abierto_id, capitulo_id, docente_id, fecha, mensualidad, generado) VALUES (999, 999, 999, CURDATE(), 100, 1)");
        return (int)$pdo->query("SELECT id FROM diplomado_control WHERE diplomado_abierto_id = 999 AND capitulo_id = 999")->fetchColumn();
    }

    public function test_getDiplomadosAbiertosConControl_returns_array(): void
    {
        $result = $this->model->getDiplomadosAbiertosConControl();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('diplomado_abierto_id', $result[0]);
        $this->assertArrayHasKey('total_controles', $result[0]);
    }

    public function test_findDiplomadoAbierto_returns_data(): void
    {
        $result = $this->model->findDiplomadoAbierto(999);
        $this->assertIsArray($result);
        $this->assertSame('TEST Diplomado', $result['diplomado_nombre']);
    }

    public function test_findDiplomadoAbierto_returns_null_for_nonexistent(): void
    {
        $this->assertNull($this->model->findDiplomadoAbierto(999999));
    }

    public function test_getDiplomadosAbiertosDisponibles_returns_list(): void
    {
        $result = $this->model->getDiplomadosAbiertosDisponibles();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('diplomado_nombre', $result[0]);
    }

    public function test_getCapitulosPorDiplomado_returns_chapters(): void
    {
        $result = $this->model->getCapitulosPorDiplomado(999);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertSame('TEST Capítulo', $result[0]['nombre']);
    }

    public function test_getDocentesActivos_returns_teachers(): void
    {
        $result = $this->model->getDocentesActivos();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertContains('DOCTEST', array_column($result, 'ci_pasapote'));
    }

    public function test_upsertControl_inserts_and_updates(): void
    {
        $this->assertTrue($this->model->upsertControl([
            'diplomado_abierto_id' => 999,
            'capitulo_id' => 999,
            'docente_id' => 999,
            'fecha' => date('Y-m-d'),
            'mensualidad' => 200,
            'generado' => 1,
        ]));
        $this->testId = $this->insertTestRecord();
        $controls = $this->model->getControlesPorDiplomadoAbierto(999);
        $this->assertNotEmpty($controls);
        $this->assertSame(200.0, (float)$controls[0]['mensualidad']);
    }

    public function test_getControlesPorDiplomadoAbierto_returns_controls(): void
    {
        $this->testId = $this->insertTestRecord();
        $controls = $this->model->getControlesPorDiplomadoAbierto(999);
        $this->assertIsArray($controls);
        $this->assertNotEmpty($controls);
        $this->assertArrayHasKey('capitulo_nombre', $controls[0]);
    }

    public function test_deleteControlesPorDiplomadoAbierto_removes_records(): void
    {
        $this->testId = $this->insertTestRecord();
        $this->assertTrue($this->model->deleteControlesPorDiplomadoAbierto(999));
        $controls = $this->model->getControlesPorDiplomadoAbierto(999);
        $this->assertEmpty($controls);
        $this->testId = null;
    }

    public function test_getDiplomadosDataTable_returns_datatables_format(): void
    {
        $result = $this->model->getDiplomadosDataTable(1, 0, 10, '', [['column' => 0, 'dir' => 'asc']]);
        $this->assertArrayHasKey('draw', $result);
        $this->assertArrayHasKey('recordsTotal', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertNotEmpty($result['data']);
    }
}
