<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Core\Database;

abstract class DatabaseTestCase extends TestCase
{
    protected function setUp(): void
    {
        $refl = new \ReflectionClass(Database::class);
        $prop = $refl->getProperty('instance');
        $prop->setValue(null);
    }

    protected function getConnection(): \PDO
    {
        return Database::getInstance()->getConnection();
    }
}
