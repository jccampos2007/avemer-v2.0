<?php

use Tests\DatabaseTestCase;
use App\Modules\Docentes\DocenteModel;

class DocenteModelTest extends DatabaseTestCase
{
    private DocenteModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new DocenteModel();
    }

    public function test_getAll_contains_seeded(): void
    {
        $items = $this->model->getAll();
        $this->assertIsArray($items);
        $this->assertNotEmpty($items);
        $cis = array_column($items, 'ci_pasapote');
        $this->assertContains('DOCTEST', $cis);
    }

    public function test_findById_returns_docente(): void
    {
        $d = $this->model->findById(999);
        $this->assertIsArray($d);
        $this->assertSame('DOCTEST', $d['ci_pasapote']);
        $this->assertSame('Doc', $d['primer_nombre']);
    }

    public function test_findById_returns_null_for_nonexistent(): void
    {
        $this->assertNull($this->model->findById(999999));
    }

    public function test_getOfertasAsociadas_returns_offers(): void
    {
        $ofertas = $this->model->getOfertasAsociadas(999);
        $this->assertIsArray($ofertas);
        $this->assertNotEmpty($ofertas);
        $types = array_column($ofertas, 'tipo');
        $this->assertContains('Curso/Taller', $types);
    }
}
