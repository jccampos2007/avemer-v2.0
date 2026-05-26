<?php

use Tests\DatabaseTestCase;
use App\Modules\Dashboard\DashboardModel;

class DashboardModelTest extends DatabaseTestCase
{
    private DashboardModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new DashboardModel();
    }

    public function test_getInscripcionesStats_returns_stats(): void
    {
        $stats = $this->model->getInscripcionesStats();
        $this->assertIsArray($stats);
        $this->assertCount(4, $stats);
        foreach ($stats as $key => $stat) {
            $this->assertArrayHasKey('total', $stat);
            $this->assertArrayHasKey('activos', $stat);
        }
    }

    public function test_getInscripcionesLastMonthStats_returns_chart_data(): void
    {
        $data = $this->model->getInscripcionesLastMonthStats();
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $item = $data[0];
        $this->assertArrayHasKey('label', $item);
        $this->assertArrayHasKey('count', $item);
        $this->assertArrayHasKey('bg_color', $item);
        $this->assertArrayHasKey('category_index', $item);
    }
}
