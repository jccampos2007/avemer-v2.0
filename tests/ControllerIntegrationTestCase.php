<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

abstract class ControllerIntegrationTestCase extends TestCase
{
    protected function callController(string $controllerClass, string $method, array $post = [], array $server = [], array $args = []): string
    {
        $phpBinary = defined('PHP_BINARY') ? PHP_BINARY : 'php';

        $cmd = sprintf(
            '%s %s %s %s %s %s %s 2>&1',
            escapeshellcmd($phpBinary),
            escapeshellarg(__DIR__ . '/run_controller_action.php'),
            escapeshellarg($controllerClass),
            escapeshellarg($method),
            escapeshellarg(json_encode($post)),
            escapeshellarg(json_encode($server)),
            escapeshellarg(json_encode($args))
        );

        $output = shell_exec($cmd);

        if ($output === null) {
            $this->fail("Controller call returned no output (command may have failed): $cmd");
        }

        return $output;
    }
}
