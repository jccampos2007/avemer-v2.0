<?php

use Tests\ControllerIntegrationTestCase;

class AjaxEndpointsIntegrationTest extends ControllerIntegrationTestCase
{
    private const AJAX_HEADER = [
        'HTTP_X_REQUESTED_WITH' => 'xmlhttprequest',
        'REQUEST_METHOD' => 'GET',
    ];

    private const POST_AJAX_HEADER = [
        'HTTP_X_REQUESTED_WITH' => 'xmlhttprequest',
        'REQUEST_METHOD' => 'POST',
    ];

    /** @dataProvider correoEndpointProvider */
    public function test_correo_endpoint(string $method, array $get, array $server, string $desc): void
    {
        $output = $this->callController(
            \App\Modules\Correo\CorreoController::class,
            $method,
            $get,
            $server
        );
        $response = json_decode($output, true);
        $this->assertIsArray($response, "$desc: valid JSON");
        $this->assertArrayHasKey('success', $response, "$desc: has success");
    }

    /** @dataProvider cuotaEndpointProvider */
    public function test_cuota_endpoint(string $method, array $post, array $server, string $desc): void
    {
        $output = $this->callController(
            \App\Modules\Cuota\CuotaController::class,
            $method,
            $post,
            $server
        );
        $response = json_decode($output, true);
        $this->assertIsArray($response, "$desc: valid JSON");
        $this->assertArrayHasKey('success', $response, "$desc: has success");
    }

    /** @dataProvider cuotaDeleteProvider */
    public function test_cuota_delete_endpoint(string $method, array $post, array $server, array $args, string $desc): void
    {
        $output = $this->callController(
            \App\Modules\Cuota\CuotaController::class,
            $method,
            $post,
            $server,
            $args
        );
        $response = json_decode($output, true);
        $this->assertIsArray($response, "$desc: valid JSON");
        $this->assertArrayHasKey('success', $response, "$desc: has success");
    }

    /** @dataProvider diplomadoControlEndpointProvider */
    public function test_diplomado_control_endpoint(string $method, array $get, array $server, string $desc): void
    {
        $output = $this->callController(
            \App\Modules\DiplomadoControl\DiplomadoControlController::class,
            $method,
            $get,
            $server
        );
        $response = json_decode($output, true);
        $this->assertIsArray($response, "$desc: valid JSON");
    }

    /** @dataProvider grupoEndpointProvider */
    public function test_grupo_endpoint(string $method, array $post, array $server, array $args, string $desc): void
    {
        $output = $this->callController(
            \App\Modules\Grupo\GrupoController::class,
            $method,
            $post,
            $server,
            $args
        );
        $response = json_decode($output, true);
        $this->assertIsArray($response, "$desc: valid JSON");
        $this->assertArrayHasKey('success', $response, "$desc: has success");
    }

    /** @dataProvider preinscripcionDiplomadoEndpointProvider */
    public function test_preinscripcion_diplomado_endpoint(string $method, array $post, string $desc): void
    {
        $output = $this->callController(
            \App\Modules\PreinscripcionDiplomado\PreinscripcionDiplomadoController::class,
            $method,
            $post
        );
        $response = json_decode($output, true);
        $this->assertIsArray($response, "$desc: valid JSON");
        $this->assertArrayHasKey('success', $response, "$desc: has success");
    }

    public static function correoEndpointProvider(): array
    {
        $h = self::AJAX_HEADER;
        return [
            'getAcademicOffersByType_1' => ['getAcademicOffersByType', ['type_id' => 1], $h, 'Correo getAcademicOffersByType Cursos'],
            'getAcademicOffersByType_2' => ['getAcademicOffersByType', ['type_id' => 2], $h, 'Correo getAcademicOffersByType Diplo'],
            'getAcademicOffersByType_3' => ['getAcademicOffersByType', ['type_id' => 3], $h, 'Correo getAcademicOffersByType Evento'],
            'getAcademicOffersByType_4' => ['getAcademicOffersByType', ['type_id' => 4], $h, 'Correo getAcademicOffersByType Maes'],
            'getMensajes' => ['getMensajes', [], $h, 'Correo getMensajes'],
            'getCorreosByOfferData' => ['getCorreosByOfferData', ['tipo_oferta_id' => 2, 'oferta_id' => 999], $h, 'Correo getCorreosByOfferData'],
            'getStudentsForDebtGeneration' => ['getStudentsForDebtGeneration', ['tipo_oferta_id' => 2, 'oferta_id' => 999, 'correo_id' => 0], $h, 'Correo getStudentsForDebtGeneration'],
        ];
    }

    public static function cuotaEndpointProvider(): array
    {
        $h = self::AJAX_HEADER;
        return [
            'getAcademicOffersByType_1' => ['getAcademicOffersByType', ['type_id' => 1], $h, 'Cuota getAcademicOffersByType Cursos'],
            'getAcademicOffersByType_2' => ['getAcademicOffersByType', ['type_id' => 2], $h, 'Cuota getAcademicOffersByType Diplo'],
            'getAcademicOffersByType_3' => ['getAcademicOffersByType', ['type_id' => 3], $h, 'Cuota getAcademicOffersByType Evento'],
            'getAcademicOffersByType_4' => ['getAcademicOffersByType', ['type_id' => 4], $h, 'Cuota getAcademicOffersByType Maes'],
            'getCuotasByOfferData' => ['getCuotasByOfferData', ['tipo_oferta_id' => 2, 'oferta_id' => 999], $h, 'Cuota getCuotasByOfferData'],
            'getStudentsForDebtGeneration' => ['getStudentsForDebtGeneration', ['tipo_oferta_id' => 2, 'oferta_id' => 999, 'cuota_id' => 1], $h, 'Cuota getStudentsForDebtGeneration'],
        ];
    }

    public static function cuotaEndpointPostProvider(): array
    {
        return [
            'generateDebt_missing_data' => ['generateDebt', [], self::POST_AJAX_HEADER, 'Cuota generateDebt missing data'],
        ];
    }

    /** @dataProvider cuotaEndpointPostProvider */
    public function test_cuota_post_endpoint(string $method, array $post, array $server, string $desc): void
    {
        $output = $this->callController(
            \App\Modules\Cuota\CuotaController::class,
            $method,
            $post,
            $server
        );
        $response = json_decode($output, true);
        $this->assertIsArray($response, "$desc: valid JSON");
    }

    public static function correoPostEndpointProvider(): array
    {
        return [
            'sendChecked_missing_data' => ['sendChecked', [], self::POST_AJAX_HEADER, 'Correo sendChecked missing data'],
        ];
    }

    /** @dataProvider correoPostEndpointProvider */
    public function test_correo_post_endpoint(string $method, array $post, array $server, string $desc): void
    {
        $output = $this->callController(
            \App\Modules\Correo\CorreoController::class,
            $method,
            $post,
            $server
        );
        $response = json_decode($output, true);
        $this->assertIsArray($response, "$desc: valid JSON");
    }

    public static function cuotaDeleteProvider(): array
    {
        return [
            'delete_not_found' => ['delete', [], self::AJAX_HEADER, [999999], 'Cuota delete not found'],
        ];
    }

    public static function diplomadoControlEndpointProvider(): array
    {
        return [
            'getCapitulosAjax' => ['getCapitulosAjax', ['diplomado_abierto_id' => 999], self::AJAX_HEADER, 'DiplControl getCapitulosAjax'],
        ];
    }

    public static function grupoEndpointProvider(): array
    {
        $hGet = ['REQUEST_METHOD' => 'GET'];
        return [
            'getPermissions' => ['getPermissions', [], $hGet, [999], 'Grupo getPermissions'],
        ];
    }

    public static function preinscripcionDiplomadoEndpointProvider(): array
    {
        return [
            'searchAlumno_found' => ['searchAlumno', ['ci_pasaporte' => '99999901'], 'Preinsc searchAlumno found'],
            'searchAlumno_not_found' => ['searchAlumno', ['ci_pasaporte' => 'NONEXISTENT'], 'Preinsc searchAlumno not found'],
            'getDiplomadosAbiertosForPreinscripcion' => ['getDiplomadosAbiertosForPreinscripcion', [], 'Preinsc getDiplomadosAbiertos'],
        ];
    }
}
