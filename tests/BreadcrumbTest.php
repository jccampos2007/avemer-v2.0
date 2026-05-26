<?php
require_once APP_ROOT . '/App/Layout/breadcrumb.php';

use PHPUnit\Framework\TestCase;

class BreadcrumbTest extends TestCase
{
    private string $savedRequestUri;
    private string $savedBaseUrl;

    protected function setUp(): void
    {
        $this->savedRequestUri = $_SERVER['REQUEST_URI'] ?? '';
        $this->savedBaseUrl = BASE_URL;
    }

    protected function tearDown(): void
    {
        $_SERVER['REQUEST_URI'] = $this->savedRequestUri;
    }

    private function callBreadcrumbs(string $uri): array
    {
        $_SERVER['REQUEST_URI'] = $uri;
        return generateBreadcrumbs();
    }

    public function test_dashboard_returns_inicio_and_dashboard(): void
    {
        $crumbs = $this->callBreadcrumbs('/php_mvc_app/public/dashboard');
        $this->assertCount(2, $crumbs);
        $this->assertSame('Dashboard', $crumbs[1]['label']);
    }

    public function test_list_view_returns_inicio_and_module(): void
    {
        $crumbs = $this->callBreadcrumbs('/php_mvc_app/public/alumnos');
        $this->assertCount(2, $crumbs);
        $this->assertSame('Inicio', $crumbs[0]['label']);
        $this->assertSame(BASE_URL . 'dashboard', $crumbs[0]['url']);
        $this->assertSame('Alumnos', $crumbs[1]['label']);
        $this->assertNull($crumbs[1]['url']);
    }

    public function test_create_view_returns_inicio_module_and_crear(): void
    {
        $crumbs = $this->callBreadcrumbs('/php_mvc_app/public/alumnos/create');
        $this->assertCount(3, $crumbs);
        $this->assertSame('Inicio', $crumbs[0]['label']);
        $this->assertSame('Alumnos', $crumbs[1]['label']);
        $this->assertSame(BASE_URL . 'alumnos', $crumbs[1]['url']);
        $this->assertSame('Crear', $crumbs[2]['label']);
        $this->assertNull($crumbs[2]['url']);
    }

    public function test_edit_view_returns_inicio_module_and_edit_with_id(): void
    {
        $crumbs = $this->callBreadcrumbs('/php_mvc_app/public/alumnos/edit/5');
        $this->assertCount(3, $crumbs);
        $this->assertSame('Inicio', $crumbs[0]['label']);
        $this->assertSame('Alumnos', $crumbs[1]['label']);
        $this->assertSame(BASE_URL . 'alumnos', $crumbs[1]['url']);
        $this->assertSame('Editar #5', $crumbs[2]['label']);
        $this->assertNull($crumbs[2]['url']);
    }

    public function test_unknown_module_uses_ucfirst_fallback(): void
    {
        $crumbs = $this->callBreadcrumbs('/php_mvc_app/public/nuevo_modulo');
        $this->assertSame('Nuevo modulo', $crumbs[1]['label']);
    }

    public function test_unknown_module_with_underscores(): void
    {
        $crumbs = $this->callBreadcrumbs('/php_mvc_app/public/nuevo_modulo_test');
        $this->assertSame('Nuevo modulo test', $crumbs[1]['label']);
    }

    public function test_api_route_returns_empty(): void
    {
        $this->assertSame([], $this->callBreadcrumbs('/php_mvc_app/public/api/data/alumnos'));
    }

    public function test_asset_route_returns_empty(): void
    {
        $this->assertSame([], $this->callBreadcrumbs('/php_mvc_app/public/asset/js/test.js'));
    }

    public function test_edit_without_id_does_not_add_edit_crumb(): void
    {
        $crumbs = $this->callBreadcrumbs('/php_mvc_app/public/alumnos/edit');
        $this->assertCount(2, $crumbs);
    }

    public function test_root_base_url_parses_correctly(): void
    {
        $crumbs = $this->callBreadcrumbs('/alumnos/edit/2');
        $this->assertCount(3, $crumbs);
        $this->assertSame('Alumnos', $crumbs[1]['label']);
        $this->assertSame('Editar #2', $crumbs[2]['label']);
    }
}
