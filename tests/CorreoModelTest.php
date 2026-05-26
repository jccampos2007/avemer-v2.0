<?php

use Tests\DatabaseTestCase;
use App\Modules\Correo\CorreoModel;

class CorreoModelTest extends DatabaseTestCase
{
    private CorreoModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new CorreoModel();
    }

    public function test_getById_throws_exception_when_table_missing(): void
    {
        $this->expectException(\PDOException::class);
        $this->model->getById(999);
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

    public function test_getMensajes_returns_array(): void
    {
        $result = $this->model->getMensajes();
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function test_getCorreosByOffer_returns_enrolled_students(): void
    {
        $result = $this->model->getCorreosByOffer(2, 999);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertSame('test1@test.com', $result[0]['correo']);
    }

    public function test_getCorreosByOffer_returns_empty_for_invalid_type(): void
    {
        $this->assertSame([], $this->model->getCorreosByOffer(99, 999));
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

    public function test_insertTransaction_throws_exception_due_to_schema_mismatch(): void
    {
        $this->expectException(\PDOException::class);
        $this->expectExceptionMessage('correo_id');
        $this->model->insertTransaction([
            'alumno_id' => 999901,
            'correo_id' => 0,
            'monto' => 100.00,
        ]);
    }

    public function test_updateCorreoGenerado_throws_exception_when_table_missing(): void
    {
        $this->expectException(\PDOException::class);
        $this->model->updateCorreoGenerado(999, 1);
    }

    public function test_getMensajeById_returns_mensaje(): void
    {
        $result = $this->model->getMensajeById(999);
        $this->assertIsArray($result);
        $this->assertSame('TEST Mensaje', $result['titulo']);
        $this->assertSame('Test message body', $result['mensaje']);
    }

    public function test_getMensajeById_returns_empty_for_nonexistent(): void
    {
        $this->assertSame([], $this->model->getMensajeById(999999));
    }
}
