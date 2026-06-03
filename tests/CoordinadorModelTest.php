<?php

use Tests\DatabaseTestCase;
use App\Modules\Coordinadores\CoordinadorModel;

class CoordinadorModelTest extends DatabaseTestCase
{
    private CoordinadorModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new CoordinadorModel();
    }

    public function test_findById_returns_coordinador(): void
    {
        $coord = $this->model->findById(999);
        $this->assertIsArray($coord);
        $this->assertSame('Coord', $coord['primer_nombre']);
    }

    public function test_findById_returns_null_for_nonexistent(): void
    {
        $this->assertNull($this->model->findById(999999));
    }

    public function test_getAll_contains_seeded(): void
    {
        $coords = $this->model->getAll();
        $this->assertIsArray($coords);
        $this->assertNotEmpty($coords);
        $cis = array_column($coords, 'ci_pasaporte');
        $this->assertContains('COOR-TEST', $cis);
    }

    public function test_create_inserts_and_returns_true(): void
    {
        $result = $this->model->create([
            'profesion_oficio_id' => 999,
            'estado_id' => 999,
            'nacionalidad_id' => 999,
            'ci_pasaporte' => 'COOR-CREATE',
            'primer_nombre' => 'Create',
            'segundo_nombre' => '',
            'primer_apellido' => 'Coord',
            'segundo_apellido' => '',
            'correo' => 'coord-test@test.com',
            'tlf_habitacion' => '',
            'tlf_trabajo' => '',
            'tlf_celular' => '04120000999',
            'fecha_nacimiento' => null,
            'estatus_activo_id' => 1,
            'direccion' => '',
            'foto' => null,
            'imagen' => null,
        ]);
        $this->assertTrue($result);

        $pdo = $this->getConnection();
        $stmt = $pdo->query("SELECT id FROM coordinador WHERE ci_pasaporte = 'COOR-CREATE'");
        $id = $stmt->fetchColumn();
        $this->assertNotFalse($id);

        $pdo->exec("DELETE FROM coordinador WHERE id = " . (int)$id);
    }

    public function test_update_modifies_record(): void
    {
        $result = $this->model->update(999, [
            'profesion_oficio_id' => 999,
            'estado_id' => 999,
            'nacionalidad_id' => 999,
            'ci_pasaporte' => 'COOR-TEST',
            'primer_nombre' => 'Updated',
            'segundo_nombre' => '',
            'primer_apellido' => 'Coord',
            'segundo_apellido' => '',
            'correo' => 'coord-updated@test.com',
            'tlf_habitacion' => '',
            'tlf_trabajo' => '',
            'tlf_celular' => '',
            'fecha_nacimiento' => null,
            'estatus_activo_id' => 1,
            'direccion' => '',
            'foto' => null,
            'imagen' => null,
        ]);
        $this->assertTrue($result);

        $coord = $this->model->findById(999);
        $this->assertSame('Updated', $coord['primer_nombre']);

        // Restore
        $this->model->update(999, [
            'profesion_oficio_id' => 999,
            'estado_id' => 999,
            'nacionalidad_id' => 999,
            'ci_pasaporte' => 'COOR-TEST',
            'primer_nombre' => 'Coord',
            'segundo_nombre' => '',
            'primer_apellido' => 'Test',
            'segundo_apellido' => '',
            'correo' => null,
            'tlf_habitacion' => '',
            'tlf_trabajo' => '',
            'tlf_celular' => '',
            'fecha_nacimiento' => null,
            'estatus_activo_id' => 1,
            'direccion' => '',
            'foto' => null,
            'imagen' => null,
        ]);
    }

    public function test_delete_removes_record(): void
    {
        $this->assertTrue($this->model->delete(999));
        $this->assertNull($this->model->findById(999));

        $pdo = $this->getConnection();
        $pdo->exec("INSERT IGNORE INTO coordinador (id, ci_pasaporte, primer_nombre, primer_apellido) VALUES (999, 'COOR-TEST', 'Coord', 'Test')");
    }
}
