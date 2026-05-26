<?php

use Tests\ControllerIntegrationTestCase;
use App\Modules\PreinscripcionLanding\PreinscripcionLandingController;

class PreinscripcionLandingControllerIntegrationTest extends ControllerIntegrationTestCase
{
    private const CONTROLLER = PreinscripcionLandingController::class;

    protected function setUp(): void
    {
        parent::setUp();
        // MyISAM no soporta transacciones — limpia datos residuales entre runs
        $pdo = \App\Core\Database::getInstance()->getConnection();
        $pdo->exec("DELETE FROM inscripcion_curso WHERE alumno_id = 999901 AND curso_abierto_id = 999");
    }

    public function test_searchAlumno_found_without_photo(): void
    {
        $output = $this->callController(self::CONTROLLER, 'searchAlumno', [
            'ci_pasapote' => '99999901',
        ]);

        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertTrue($response['success']);
        $this->assertTrue($response['found']);
        $this->assertSame('Test', $response['alumno']['primer_nombre']);
    }

    public function test_searchAlumno_found_with_photo_base64_encoded(): void
    {
        $output = $this->callController(self::CONTROLLER, 'searchAlumno', [
            'ci_pasapote' => '99999902',
        ]);

        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertTrue($response['success']);
        $this->assertTrue($response['found']);
        $this->assertIsString($response['alumno']['foto']);
        $this->assertNotEmpty($response['alumno']['foto']);
        // Verify it's valid base64
        $this->assertNotFalse(base64_decode($response['alumno']['foto'], true));
    }

    public function test_searchAlumno_not_found(): void
    {
        $output = $this->callController(self::CONTROLLER, 'searchAlumno', [
            'ci_pasapote' => '00000000',
        ]);

        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertTrue($response['success']);
        $this->assertFalse($response['found']);
    }

    public function test_searchAlumno_empty_ci(): void
    {
        $output = $this->callController(self::CONTROLLER, 'searchAlumno', [
            'ci_pasapote' => '',
        ]);

        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('vacío', $response['message']);
    }

    public function test_searchAlumno_with_dots_in_ci(): void
    {
        $output = $this->callController(self::CONTROLLER, 'searchAlumno', [
            'ci_pasapote' => '99.999.901',
        ]);

        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertTrue($response['success']);
        $this->assertTrue($response['found']);
        $this->assertSame('Test', $response['alumno']['primer_nombre']);
    }

    public function test_createAlumno_creates_and_returns_alumno(): void
    {
        $suffix = substr(uniqid(), -6);
        $ci = "INT-$suffix";

        $output = $this->callController(self::CONTROLLER, 'createAlumno', [
            'new_ci_pasapote' => $ci,
            'new_primer_nombre' => 'Integration',
            'new_segundo_nombre' => '',
            'new_primer_apellido' => 'Test',
            'new_segundo_apellido' => 'Created',
            'new_correo' => "int-test-$suffix@test.com",
            'new_tlf_habitacion' => '',
            'new_tlf_celular' => '04120000999',
        ]);

        $response = json_decode($output, true);
        $this->assertIsArray($response, "Failed to parse JSON. Raw output: $output");
        $this->assertTrue($response['success'], 'message: ' . ($response['message'] ?? '') . " | raw: $output");
        $this->assertIsArray($response['alumno']);
        $this->assertSame('Integration', $response['alumno']['primer_nombre']);

        // Cleanup
        $pdo = \App\Core\Database::getInstance()->getConnection();
        $pdo->exec("DELETE FROM alumno WHERE ci_pasapote = " . $pdo->quote($ci));
    }

    public function test_getOfertasAbiertas_returns_cursos(): void
    {
        $output = $this->callController(self::CONTROLLER, 'getOfertasAbiertas', [
            'typeId' => '1',
        ]);

        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertTrue($response['success']);
        $this->assertIsArray($response['data']);
        $this->assertNotEmpty($response['data']);

        $found = false;
        foreach ($response['data'] as $item) {
            if ($item['id'] == 999) {
                $found = true;
                $this->assertSame('CA-001', $item['numero']);
                break;
            }
        }
        $this->assertTrue($found, 'Expected curso_abierto 999 in results');
    }

    public function test_getOfertasAbiertas_invalid_type(): void
    {
        $output = $this->callController(self::CONTROLLER, 'getOfertasAbiertas', [
            'typeId' => '999',
        ]);

        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('inválido', $response['message']);
    }

    public function test_processPreinscripcion_creates_inscripcion(): void
    {
        $output = $this->callController(self::CONTROLLER, 'processPreinscripcion', [
            'alumno_id' => '999901',
            'oferta_abierta_id' => '999',
            'typeId' => '1',
        ]);

        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertTrue($response['success'], 'message: ' . ($response['message'] ?? ''));

        // Cleanup
        $pdo = \App\Core\Database::getInstance()->getConnection();
        $pdo->exec("DELETE FROM inscripcion_curso WHERE alumno_id = 999901 AND curso_abierto_id = 999");
    }

    public function test_processPreinscripcion_duplicate_rejected(): void
    {
        // First insert
        $pdo = \App\Core\Database::getInstance()->getConnection();
        $pdo->exec("INSERT IGNORE INTO inscripcion_curso (alumno_id, curso_abierto_id, estatus_inscripcion_id) VALUES (999901, 999, 1)");

        $output = $this->callController(self::CONTROLLER, 'processPreinscripcion', [
            'alumno_id' => '999901',
            'oferta_abierta_id' => '999',
            'typeId' => '1',
        ]);

        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('ya se encuentra pre-inscrito', $response['message']);

        // Cleanup
        $pdo->exec("DELETE FROM inscripcion_curso WHERE alumno_id = 999901 AND curso_abierto_id = 999");
    }

    public function test_processPreinscripcion_missing_data(): void
    {
        $output = $this->callController(self::CONTROLLER, 'processPreinscripcion', [
            'alumno_id' => '',
            'oferta_abierta_id' => '999',
            'typeId' => '1',
        ]);

        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('Datos incompletos', $response['message']);
    }

    public function test_processPreinscripcion_invalid_type(): void
    {
        $output = $this->callController(self::CONTROLLER, 'processPreinscripcion', [
            'alumno_id' => '999901',
            'oferta_abierta_id' => '999',
            'typeId' => '999',
        ]);

        $response = json_decode($output, true);
        $this->assertIsArray($response);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('inválido', $response['message']);
    }
}
