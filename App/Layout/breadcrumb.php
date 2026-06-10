<?php
// App/Layout/breadcrumb.php
// Config y generador de breadcrumbs

function generateBreadcrumbs(): array
{
    $moduleLabels = [
        'dashboard'              => 'Dashboard',
        'alumnos'                => 'Alumnos',
        'docentes'               => 'Instructores',
        'coordinadores'          => 'Coordinadores',
        'cursos'                 => 'Talleres / Cursos',
        'cursos_abiertos'        => 'Apertura',
        'inscripcion_curso'      => 'Inscripción',
        'evento'                 => 'Eventos',
        'evento_abierto'         => 'Apertura',
        'inscripcion_evento'     => 'Inscripción',
        'diplomado'              => 'Diplomados',
        'capitulo'               => 'Capítulos',
        'diplomado_abierto'      => 'Apertura',
        'diplomadocontrol'       => 'Control de Capítulos',
        'inscripcion_diplomado'  => 'Inscripción',
        'preinscripcion_diplomado' => 'Pre-Inscripción',
        'maestria'               => 'Maestrías',
        'maestria_abierto'       => 'Apertura',
        'inscripcion_maestria'   => 'Inscripción',
        'cuota'                  => 'Cuotas',
        'pago'                   => 'Pagos',
        'cronograma'             => 'Cronograma',
        'asistencia'             => 'Asistencia',
        'sede'                   => 'Sedes',
        'banco'                  => 'Bancos',
        'duracion'               => 'Duraciones',
        'profesion_oficio'       => 'Profesiones',
        'ciudad'                 => 'Ciudades / Estados',
        'mensajes'               => 'Mensajes',
        'listaenvio'             => 'Listas Envío',
        'listacorreo'            => 'Listas Correo',
        'correo'                 => 'Correo',
        'users'                  => 'Usuarios',
        'grupo'                  => 'Grupo y Permisos',
        'preinscripcionlanding'  => 'Pre-Inscripción Landing',
    ];

    $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $baseUrlPath = rtrim(parse_url(BASE_URL, PHP_URL_PATH), '/');
    $relativeUri = $currentUri;
    if ($baseUrlPath !== '' && strpos($currentUri, $baseUrlPath) === 0) {
        $relativeUri = substr($currentUri, strlen($baseUrlPath));
    }
    $relativeUri = trim($relativeUri, '/');
    $segments = explode('/', $relativeUri);
    $module = $segments[0] ?? '';
    $action = $segments[1] ?? 'index';
    $id = $segments[2] ?? null;

    if (in_array($module, ['api', 'asset', 'image', 'uploads', ''])) {
        return [];
    }

    $crumbs = [];

    // 1. Inicio → link a dashboard
    $crumbs[] = ['label' => 'Inicio', 'url' => BASE_URL . 'dashboard'];

    // 2. Módulo (solo con link si no estamos en su listado y existe list.php)
    $moduleLabel = $moduleLabels[$module] ?? ucfirst(str_replace('_', ' ', $module));
    $moduleDirMap = [
        'cursos_abiertos' => 'CursoAbierto',
        'pago' => 'Pagos',
        'diplomadocontrol' => 'DiplomadoControl',
        'listaenvio' => 'Envios',
        'listacorreo' => 'Correo',
        'preinscripcionlanding' => 'PreinscripcionLanding',
    ];
    $moduleDirName = $moduleDirMap[$module] ?? implode('', array_map('ucfirst', explode('_', $module)));
    $moduleViewDir = MODULES_PATH . $moduleDirName . '/Views';
    $hasListView = file_exists($moduleViewDir . '/list.php');
    $crumbs[] = [
        'label' => $moduleLabel,
        'url'   => ($action === 'index' || !$hasListView) ? null : BASE_URL . $module,
    ];

    // 3. Acción (create / edit)
    if ($action === 'create') {
        $crumbs[] = ['label' => 'Crear', 'url' => null];
    } elseif ($action === 'edit' && $id) {
        $crumbs[] = ['label' => "Editar #{$id}", 'url' => null];
    }

    return $crumbs;
}
