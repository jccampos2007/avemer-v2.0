<?php

use Tests\ControllerIntegrationTestCase;

class DataTableEndpointsIntegrationTest extends ControllerIntegrationTestCase
{
    private const AJAX_HEADER = [
        'HTTP_X_REQUESTED_WITH' => 'xmlhttprequest',
        'REQUEST_METHOD' => 'POST',
    ];

    private static function basePost(array $extra = []): array
    {
        return array_merge([
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'search' => ['value' => ''],
            'order' => [['column' => 0, 'dir' => 'asc']],
        ], $extra);
    }

    /** @dataProvider dataTableEndpointProvider */
    public function test_dataTable_endpoint_returns_valid_response(string $controllerClass, string $method, array $post, string $desc): void
    {
        $output = $this->callController($controllerClass, $method, $post, self::AJAX_HEADER);
        $response = json_decode($output, true);

        $this->assertIsArray($response, "$desc: Response is valid JSON");
        $this->assertArrayHasKey('draw', $response, "$desc: has draw");
        $this->assertArrayHasKey('recordsTotal', $response, "$desc: has recordsTotal");
        $this->assertArrayHasKey('recordsFiltered', $response, "$desc: has recordsFiltered");
        $this->assertArrayHasKey('data', $response, "$desc: has data");
        $this->assertIsArray($response['data'], "$desc: data is array");
        $this->assertSame(1, (int)$response['draw'], "$desc: draw=1");
    }

    public static function dataTableEndpointProvider(): array
    {
        return [
            'Users' => [
                \App\Modules\Users\UserController::class,
                'getUsersData',
                self::basePost(),
                'Users',
            ],
            'Alumnos' => [
                \App\Modules\Alumnos\AlumnoController::class,
                'getAlumnosData',
                self::basePost(),
                'Alumnos',
            ],
            'Docentes' => [
                \App\Modules\Docentes\DocenteController::class,
                'getDocentesData',
                self::basePost(),
                'Docentes',
            ],
            'Coordinadores' => [
                \App\Modules\Coordinadores\CoordinadorController::class,
                'getCoordinadoresData',
                self::basePost(),
                'Coordinadores',
            ],
            'Cursos' => [
                \App\Modules\Cursos\CursoController::class,
                'getCursosData',
                self::basePost(),
                'Cursos',
            ],
            'CursoAbierto' => [
                \App\Modules\CursoAbierto\CursoAbiertoController::class,
                'getCursoAbiertoData',
                self::basePost(),
                'CursoAbierto',
            ],
            'InscripcionCurso' => [
                \App\Modules\InscripcionCurso\InscripcionCursoController::class,
                'getInscripcionCursoData',
                self::basePost(),
                'InscripcionCurso',
            ],
            'Diplomado' => [
                \App\Modules\Diplomado\DiplomadoController::class,
                'getDiplomadoData',
                self::basePost(),
                'Diplomado',
            ],
            'DiplomadoAbierto' => [
                \App\Modules\DiplomadoAbierto\DiplomadoAbiertoController::class,
                'getDiplomadoAbiertoData',
                self::basePost(),
                'DiplomadoAbierto',
            ],
            'Capitulo' => [
                \App\Modules\Capitulo\CapituloController::class,
                'getCapituloData',
                self::basePost(['diplomado_id' => 999]),
                'Capitulo',
            ],
            'DiplomadoControl' => [
                \App\Modules\DiplomadoControl\DiplomadoControlController::class,
                'getDiplomadosData',
                self::basePost(),
                'DiplomadoControl',
            ],
            'InscripcionDiplomado' => [
                \App\Modules\InscripcionDiplomado\InscripcionDiplomadoController::class,
                'getInscripcionDiplomadoData',
                self::basePost(),
                'InscripcionDiplomado',
            ],
            'Evento' => [
                \App\Modules\Evento\EventoController::class,
                'getEventoData',
                self::basePost(),
                'Evento',
            ],
            'EventoAbierto' => [
                \App\Modules\EventoAbierto\EventoAbiertoController::class,
                'getEventoAbiertoData',
                self::basePost(),
                'EventoAbierto',
            ],
            'InscripcionEvento' => [
                \App\Modules\InscripcionEvento\InscripcionEventoController::class,
                'getInscripcionEventoData',
                self::basePost(),
                'InscripcionEvento',
            ],
            'Maestria' => [
                \App\Modules\Maestria\MaestriaController::class,
                'getMaestriaData',
                self::basePost(),
                'Maestria',
            ],
            'MaestriaAbierto' => [
                \App\Modules\MaestriaAbierto\MaestriaAbiertoController::class,
                'getMaestriaAbiertoData',
                self::basePost(),
                'MaestriaAbierto',
            ],
            'InscripcionMaestria' => [
                \App\Modules\InscripcionMaestria\InscripcionMaestriaController::class,
                'getInscripcionMaestriaData',
                self::basePost(),
                'InscripcionMaestria',
            ],
            'Grupo' => [
                \App\Modules\Grupo\GrupoController::class,
                'getGroupsData',
                self::basePost(),
                'Grupo',
            ],
            'Sede' => [
                \App\Modules\Sede\SedeController::class,
                'getSedesData',
                self::basePost(),
                'Sede',
            ],
            'Ciudad' => [
                \App\Modules\Ciudad\CiudadController::class,
                'getData',
                self::basePost(),
                'Ciudad',
            ],
            'Banco' => [
                \App\Modules\Banco\BancoController::class,
                'getBancosData',
                self::basePost(),
                'Banco',
            ],
            'Duracion' => [
                \App\Modules\Duracion\DuracionController::class,
                'getDuracionesData',
                self::basePost(),
                'Duracion',
            ],
            'ProfesionOficio' => [
                \App\Modules\ProfesionOficio\ProfesionOficioController::class,
                'getProfesionesData',
                self::basePost(),
                'ProfesionOficio',
            ],
            'Mensajes' => [
                \App\Modules\Mensajes\MensajesController::class,
                'getMensajesData',
                self::basePost(),
                'Mensajes',
            ],
            'Envios' => [
                \App\Modules\Envios\EnviosController::class,
                'getEnviosData',
                self::basePost(),
                'Envios',
            ],
        ];
    }
}
