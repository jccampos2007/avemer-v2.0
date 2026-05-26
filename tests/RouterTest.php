<?php
use PHPUnit\Framework\TestCase;
use App\Core\Router;

// Test controller for Router dispatch tests
class RouterTestController
{
    public function index(): void
    {
        echo 'index ok';
    }

    public function show(int $id): void
    {
        echo 'show ' . $id;
    }

    public function edit(...$params): void
    {
        echo 'edit ' . implode(',', $params);
    }
}

class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $this->router = new Router();
    }

    public function test_add_route_registers_route(): void
    {
        $this->router->add('GET', '/test', 'RouterTestController@index');

        $refl = new ReflectionClass($this->router);
        $routes = $refl->getProperty('routes');
        $all = $routes->getValue($this->router);

        $this->assertArrayHasKey('GET', $all);
        $this->assertArrayHasKey('/test', $all['GET']);
        $this->assertSame('RouterTestController@index', $all['GET']['/test']);
    }

    public function test_add_multiple_methods_same_path(): void
    {
        $this->router->add('GET', '/user', 'RouterTestController@index');
        $this->router->add('POST', '/user', 'RouterTestController@index');

        $refl = new ReflectionClass($this->router);
        $routes = $refl->getProperty('routes');
        $all = $routes->getValue($this->router);

        $this->assertArrayHasKey('GET', $all);
        $this->assertArrayHasKey('POST', $all);
        $this->assertArrayHasKey('/user', $all['GET']);
        $this->assertArrayHasKey('/user', $all['POST']);
    }

    public function test_dispatch_404_for_unknown_route(): void
    {
        $this->router->add('GET', '/existing', 'RouterTestController@index');

        ob_start();
        $this->router->dispatch('/unknown', 'GET');
        $output = ob_get_clean();

        $this->assertStringContainsString('404', $output);
    }

    public function test_dispatch_404_for_wrong_method(): void
    {
        $this->router->add('POST', '/test', 'RouterTestController@index');

        ob_start();
        $this->router->dispatch('/test', 'GET');
        $output = ob_get_clean();

        // GET /test won't match POST /test → 404
        $this->assertStringContainsString('404', $output);
    }

    public function test_dispatch_exact_route_calls_controller(): void
    {
        $this->router->add('GET', '/test', 'RouterTestController@index');

        ob_start();
        $this->router->dispatch('/test', 'GET');
        $output = ob_get_clean();

        $this->assertSame('index ok', $output);
    }

    public function test_dispatch_parameterized_route(): void
    {
        $this->router->add('GET', '/user/{id}', 'RouterTestController@show');

        ob_start();
        $this->router->dispatch('/user/42', 'GET');
        $output = ob_get_clean();

        $this->assertSame('show 42', $output);
    }

    public function test_dispatch_parameterized_route_with_string(): void
    {
        $this->router->add('GET', '/post/{slug}', 'RouterTestController@edit');

        ob_start();
        $this->router->dispatch('/post/hello-world', 'GET');
        $output = ob_get_clean();

        $this->assertSame('edit hello-world', $output);
    }

    public function test_dispatch_multiple_params(): void
    {
        $this->router->add('GET', '/category/{cat}/item/{id}', 'RouterTestController@edit');

        ob_start();
        $this->router->dispatch('/category/books/item/5', 'GET');
        $output = ob_get_clean();

        $this->assertSame('edit books,5', $output);
    }

    public function test_dispatch_strips_query_string(): void
    {
        $this->router->add('GET', '/test', 'RouterTestController@index');

        ob_start();
        $this->router->dispatch('/test?page=1&sort=name', 'GET');
        $output = ob_get_clean();

        $this->assertSame('index ok', $output);
    }

    public function test_dispatch_strips_trailing_slash(): void
    {
        $this->router->add('GET', '/test', 'RouterTestController@index');

        ob_start();
        $this->router->dispatch('/test/', 'GET');
        $output = ob_get_clean();

        $this->assertSame('index ok', $output);
    }
}
