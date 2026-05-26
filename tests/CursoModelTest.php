<?php

use Tests\DatabaseTestCase;
use App\Modules\Cursos\CursoModel;

class CursoModelTest extends DatabaseTestCase
{
    private CursoModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new CursoModel();
    }

    protected function tearDown(): void
    {
        $pdo = $this->getConnection();
        $pdo->exec("UPDATE curso SET deleted_at = NULL, nombre = 'TEST Curso', numero = NULL, horas = 999, convenio = NULL WHERE id = 99991");
    }

    public function test_findById_returns_curso(): void
    {
        $curso = $this->model->findById(99991);
        $this->assertIsArray($curso);
        $this->assertSame('TEST Curso', $curso['nombre']);
    }

    public function test_findById_returns_null_for_nonexistent(): void
    {
        $curso = $this->model->findById(999999);
        $this->assertNull($curso);
    }

    public function test_findById_returns_null_for_soft_deleted(): void
    {
        $this->model->delete(99991);
        $curso = $this->model->findById(99991);
        $this->assertNull($curso);
    }

    public function test_create_inserts_and_returns_true(): void
    {
        $result = $this->model->create([
            'nombre' => 'TEST Create Curso',
            'numero' => 'TC-001',
            'horas' => 40,
            'convenio' => 'Test',
        ]);
        $this->assertTrue($result);

        $pdo = $this->getConnection();
        $stmt = $pdo->query("SELECT id FROM curso WHERE nombre = 'TEST Create Curso'");
        $id = $stmt->fetchColumn();
        $this->assertNotFalse($id);

        $pdo->exec("DELETE FROM curso WHERE id = " . (int)$id);
    }

    public function test_update_modifies_record(): void
    {
        $result = $this->model->update(99991, [
            'nombre' => 'TEST Curso Updated',
            'numero' => 'TC-001',
            'horas' => 50,
            'convenio' => 'Updated',
        ]);
        $this->assertTrue($result);

        $curso = $this->model->findById(99991);
        $this->assertSame('TEST Curso Updated', $curso['nombre']);
    }

    public function test_delete_soft_deletes(): void
    {
        $this->model->delete(99991);
        $curso = $this->model->findById(99991);
        $this->assertNull($curso);
    }

    public function test_getAll_contains_seeded_curso(): void
    {
        $cursos = $this->model->getAll();
        $this->assertIsArray($cursos);

        $nombres = array_column($cursos, 'nombre');
        $this->assertContains('TEST Curso', $nombres);
    }
}
