<?php
use PHPUnit\Framework\TestCase;
use App\Core\Controller;

class ControllerTest extends TestCase
{
    private Controller $controller;
    private ReflectionMethod $sanitizeInput;

    protected function setUp(): void
    {
        $this->controller = new Controller();
        $this->sanitizeInput = new ReflectionMethod($this->controller, 'sanitizeInput');
    }

    private function sanitize(string $data): string
    {
        return $this->sanitizeInput->invoke($this->controller, $data);
    }

    public function test_trims_whitespace(): void
    {
        $this->assertSame('hello', $this->sanitize('  hello  '));
    }

    public function test_strips_html_tags(): void
    {
        $this->assertSame('hello', $this->sanitize('<b>hello</b>'));
    }

    public function test_strips_nested_tags(): void
    {
        $this->assertSame('hello', $this->sanitize('<div><p>hello</p></div>'));
    }

    public function test_strips_and_encodes_special_chars(): void
    {
        $this->assertSame('alert(1)', $this->sanitize('<script>alert(1)</script>'));
    }

    public function test_encodes_double_quotes(): void
    {
        $this->assertSame('&quot;hello&quot;', $this->sanitize('"hello"'));
    }

    public function test_encodes_single_quotes(): void
    {
        $this->assertSame('&#039;hello&#039;', $this->sanitize("'hello'"));
    }

    public function test_encodes_ampersand(): void
    {
        $this->assertSame('AT&amp;T', $this->sanitize('AT&T'));
    }

    public function test_handles_empty_string(): void
    {
        $this->assertSame('', $this->sanitize(''));
    }

    public function test_handles_string_with_only_whitespace(): void
    {
        $this->assertSame('', $this->sanitize('   '));
    }

    public function test_combined_trim_strip_encode(): void
    {
        $input = '  <a href="xss">Click &amp; Win</a>  ';
        $expected = 'Click &amp;amp; Win';
        $this->assertSame($expected, $this->sanitize($input));
    }

    public function test_preserves_normal_text(): void
    {
        $this->assertSame('Hello World', $this->sanitize('Hello World'));
    }

    public function test_handles_numbers(): void
    {
        $this->assertSame('12345', $this->sanitize('  12345  '));
    }
}
