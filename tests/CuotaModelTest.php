<?php

use Tests\DatabaseTestCase;
use App\Modules\Cuota\CuotaModel;

class CuotaModelTest extends DatabaseTestCase
{
    private CuotaModel $model;
    private ?int $testId = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new CuotaModel();
    }

    protected function tearDown(): void
    {
        if ($this->testId !== null) {
            $this->getConnection()->exec("DELETE FROM cuota WHERE id = " . $this->testId);
            $this->getConnection()->exec("DELETE FROM transaccion WHERE cuota_id = " . $this->testId);
        }
        parent::tearDown();
    }

    private function insertTestRecord(): int
    {
        $pdo = $this->getConnection();
        $pdo->exec("INSERT INTO cuota (nombre, monto, oferta_academica_id, tipo_oferta_academica_id, generado, fecha_vencimiento, fecha) VALUES ('TEST Cuota', 500, 999, 2, 0, '2026-12-31', CURDATE())");
        return (int)$pdo->lastInsertId();
    }

    public function test_getById_returns_cuota(): void
    {
        $this->testId = $this->insertTestRecord();
        $r = $this->model->getById($this->testId);
        $this->assertIsArray($r);
        $this->assertSame('TEST Cuota', $r['nombre']);
    }

    public function test_getById_returns_false_for_nonexistent(): void
    {
        $this->assertFalse($this->model->getById(999999));
    }

    public function test_create_inserts_record(): void
    {
        $result = $this->model->create([
            'nombre' => 'Test',
            'monto' => 100,
            'oferta_academica_id' => 999,
            'tipo_oferta_academica_id' => 2,
            'fecha_vencimiento' => '2026-12-31',
        ]);
        $this->assertTrue($result);
        $pdo = $this->getConnection();
        $id = $pdo->query("SELECT id FROM cuota WHERE nombre = 'Test'")->fetchColumn();
        $this->assertNotFalse($id);
        $pdo->exec("DELETE FROM cuota WHERE id = " . (int)$id);
    }

    public function test_create_with_diplomado_control(): void
    {
        $result = $this->model->create([
            'nombre' => 'Test DC',
            'monto' => 200,
            'oferta_academica_id' => 999,
            'tipo_oferta_academica_id' => 2,
            'diplomado_control_id' => 1,
            'fecha_vencimiento' => '2026-12-31',
        ]);
        $this->assertTrue($result);
        $pdo = $this->getConnection();
        $id = $pdo->query("SELECT id FROM cuota WHERE nombre = 'Test DC'")->fetchColumn();
        $this->assertNotFalse($id);
        $pdo->exec("DELETE FROM cuota WHERE id = " . (int)$id);
    }

    public function test_update_modifies_record(): void
    {
        $this->testId = $this->insertTestRecord();
        $pdo = $this->getConnection();
        $pdo->exec("UPDATE cuota SET nombre = 'TEST Cuota', monto = 500, oferta_academica_id = 999, tipo_oferta_academica_id = 2, generado = 0, fecha_vencimiento = '2026-12-31' WHERE id = " . $this->testId);
        $this->assertTrue($this->model->update($this->testId, [
            'nombre' => 'Updated',
            'monto' => 600,
            'oferta_academica_id' => 999,
            'tipo_oferta_academica_id' => 2,
            'fecha_vencimiento' => '2026-12-31',
        ]));
        $r = $this->model->getById($this->testId);
        $this->assertSame('Updated', $r['nombre']);
        $this->assertSame(600.0, (float)$r['monto']);
    }

    public function test_delete_removes_record(): void
    {
        $this->testId = $this->insertTestRecord();
        $this->assertTrue($this->model->delete($this->testId));
        $this->assertFalse($this->model->getById($this->testId));
        $this->testId = null;
    }

    public function test_getCursos_returns_array(): void
    {
        $result = $this->model->getCursos();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('nombre', $result[0]);
    }

    public function test_getDiplomados_returns_array(): void
    {
        $result = $this->model->getDiplomados();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertContains('DA-001 TEST Diplomado', array_column($result, 'nombre'));
    }

    public function test_getEventos_returns_array(): void
    {
        $result = $this->model->getEventos();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function test_getMaestrias_returns_array(): void
    {
        $result = $this->model->getMaestrias();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function test_getOfertaInfo_returns_costo_inicial(): void
    {
        $info = $this->model->getOfertaInfo(2, 999);
        $this->assertNotNull($info);
        $this->assertArrayHasKey('costo', $info);
        $this->assertArrayHasKey('inicial', $info);
        $this->assertArrayHasKey('oferta_nombre', $info);
    }

    public function test_getOfertaInfo_returns_null_for_invalid_type(): void
    {
        $this->assertNull($this->model->getOfertaInfo(99, 999));
    }

    public function test_getDiplomadoControles_returns_array(): void
    {
        $controles = $this->model->getDiplomadoControles(999);
        $this->assertIsArray($controles);
    }

    public function test_getCuotasByOffer_returns_cuotas(): void
    {
        $this->testId = $this->insertTestRecord();
        $result = $this->model->getCuotasByOffer(2, 999);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertSame('TEST Cuota', $result[0]['nombre']);
    }

    public function test_getCuotasByOffer_returns_empty_for_invalid_type(): void
    {
        $this->assertSame([], $this->model->getCuotasByOffer(99, 999));
    }

    public function test_getStudentsByOffer_returns_students(): void
    {
        $students = $this->model->getStudentsByOffer(2, 999);
        $this->assertIsArray($students);
        $this->assertNotEmpty($students);
        $this->assertArrayHasKey('alumno_id', $students[0]);
    }

    public function test_getStudentsByOffer_returns_empty_for_invalid_type(): void
    {
        $this->assertSame([], $this->model->getStudentsByOffer(99, 999));
    }

    public function test_insertTransaction_inserts_and_returns_true(): void
    {
        $this->testId = $this->insertTestRecord();
        $result = $this->model->insertTransaction([
            'alumno_id' => 999901,
            'cuota_id' => $this->testId,
            'monto' => 500.00,
        ]);
        $this->assertTrue($result);
    }

    public function test_updateCuotaGenerado_updates_status(): void
    {
        $this->testId = $this->insertTestRecord();
        $this->assertTrue($this->model->updateCuotaGenerado($this->testId, 1));
        $r = $this->model->getById($this->testId);
        $this->assertSame(1, (int)$r['generado']);
    }
}
