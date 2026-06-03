<?php

use Tests\DatabaseTestCase;
use App\Modules\Alumnos\AlumnoModel;

class AlumnoModelTest extends DatabaseTestCase
{
    private AlumnoModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new AlumnoModel();
    }

    public function test_findByCiPasaporte_returns_alumno(): void
    {
        $alumno = $this->model->findByCiPasaporte('99999901');
        $this->assertIsArray($alumno);
        $this->assertSame('Test', $alumno['primer_nombre']);
    }

    public function test_findByCiPasaporte_ignores_dots(): void
    {
        $alumno = $this->model->findByCiPasaporte('99.999.901');
        $this->assertIsArray($alumno);
        $this->assertSame('Test', $alumno['primer_nombre']);
    }

    public function test_findByCiPasaporte_returns_false_for_nonexistent(): void
    {
        $alumno = $this->model->findByCiPasaporte('00000000');
        $this->assertFalse($alumno);
    }

    public function test_findByCiPasaporte_handles_binary_photo(): void
    {
        $alumno = $this->model->findByCiPasaporte('99999902');
        $this->assertIsArray($alumno);
        $this->assertIsString($alumno['foto']);
        $this->assertNotEmpty($alumno['foto']);
    }

    public function test_findById_returns_alumno(): void
    {
        $alumno = $this->model->findById(999901);
        $this->assertIsArray($alumno);
        $this->assertSame('Test', $alumno['primer_nombre']);
    }

    public function test_findById_returns_null_for_nonexistent(): void
    {
        $alumno = $this->model->findById(999999);
        $this->assertNull($alumno);
    }

    public function test_getAll_returns_array(): void
    {
        $alumnos = $this->model->getAll();
        $this->assertIsArray($alumnos);
        $this->assertNotEmpty($alumnos);

        $cis = array_column($alumnos, 'ci_pasaporte');
        $this->assertContains('99999901', $cis);
        $this->assertContains('99999902', $cis);
    }

    public function test_create_inserts_and_returns_id(): void
    {
        $id = $this->model->create([
            'ci_pasaporte' => 'TEST-CREATE-01',
            'primer_nombre' => 'Created',
            'segundo_nombre' => '',
            'primer_apellido' => 'Test',
            'segundo_apellido' => '',
            'correo' => 'create-test@test.com',
            'tlf_celular' => '04120000003',
            'profesion_oficio_id' => 999,
            'estado_id' => 999,
            'nacionalidad_id' => 999,
            'estatus_activo_id' => 1,
        ]);
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        $this->model->delete($id);
    }
}
