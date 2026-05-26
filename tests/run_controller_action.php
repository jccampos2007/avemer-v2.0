<?php

require_once __DIR__ . '/bootstrap.php';

$controllerClass = $argv[1] ?? '';
$methodName = $argv[2] ?? '';

if (!$controllerClass || !$methodName) {
    echo json_encode(['error' => 'Missing arguments: <ControllerClass> <methodName>']);
    exit(1);
}

$postData = $argv[3] ?? '{}';
$_POST = json_decode($postData, true) ?? [];

$serverData = $argv[4] ?? '{}';
$serverOverrides = json_decode($serverData, true) ?? [];
foreach ($serverOverrides as $key => $value) {
    $_SERVER[$key] = $value;
}

$methodArgs = $argv[5] ?? '[]';
$methodArgs = json_decode($methodArgs, true) ?? [];

$controller = new $controllerClass();
$controller->$methodName(...$methodArgs);
