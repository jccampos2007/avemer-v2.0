<?php

use Tests\DatabaseTestCase;
use App\Modules\Envios\EnviosModel;

class EnviosModelTest extends DatabaseTestCase
{
    private EnviosModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new EnviosModel();
    }

    public function test_getPaginatedEnvios_returns_datatables_format(): void
    {
        $result = $this->model->getPaginatedEnvios([
            'draw' => 1, 'start' => 0, 'length' => 10,
            'search' => ['value' => ''], 'order' => [['column' => 0, 'dir' => 'asc']],
        ]);
        $this->assertArrayHasKey('draw', $result);
        $this->assertArrayHasKey('recordsTotal', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertNotEmpty($result['data']);
    }
}
