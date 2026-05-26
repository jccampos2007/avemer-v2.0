<?php

use Tests\DatabaseTestCase;
use App\Modules\Capitulo\CapituloModel;

class CapituloModelTest extends DatabaseTestCase
{
    private CapituloModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new CapituloModel();
    }

    protected function tearDown(): void
    {
        $pdo = $this->getConnection();
        $pdo->exec("UPDATE capitulo SET deleted_at = NULL WHERE id = 999");
    }

    public function test_getById_returns_capitulo(): void
    {
        $capitulo = $this->model->getById(999);
        $this->assertIsArray($capitulo);
        $this->assertSame('TEST Capítulo', $capitulo['nombre']);
    }

    public function test_getById_returns_false_for_nonexistent(): void
    {
        $result = $this->model->getById(999999);
        $this->assertFalse($result);
    }

    public function test_getById_returns_false_for_soft_deleted(): void
    {
        $this->model->delete(999);
        $this->assertFalse($this->model->getById(999));
    }

    public function test_create_inserts_and_returns_true(): void
    {
        $result = $this->model->create([
            'diplomado_id' => 999,
            'numero' => '2',
            'nombre' => 'TEST Capítulo Create',
            'descripcion' => 'Created chapter',
            'activo' => 1,
            'orden' => 2,
        ]);
        $this->assertTrue($result);

        $pdo = $this->getConnection();
        $stmt = $pdo->query("SELECT id FROM capitulo WHERE nombre = 'TEST Capítulo Create'");
        $id = $stmt->fetchColumn();
        $this->assertNotFalse($id);

        $pdo->exec("DELETE FROM capitulo WHERE id = " . (int)$id);
    }

    public function test_update_modifies_record(): void
    {
        $result = $this->model->update(999, [
            'diplomado_id' => 999,
            'numero' => '1',
            'nombre' => 'TEST Capítulo Updated',
            'descripcion' => 'Updated desc',
            'activo' => 1,
            'orden' => 1,
        ]);
        $this->assertTrue($result);

        $capitulo = $this->model->getById(999);
        $this->assertSame('TEST Capítulo Updated', $capitulo['nombre']);

        $this->model->update(999, [
            'diplomado_id' => 999,
            'numero' => '1',
            'nombre' => 'TEST Capítulo',
            'descripcion' => 'Test chapter',
            'activo' => 1,
            'orden' => 1,
        ]);
    }

    public function test_delete_soft_deletes(): void
    {
        $this->assertTrue($this->model->delete(999));
        $this->assertFalse($this->model->getById(999));
    }
}
