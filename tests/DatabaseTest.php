<?php

use PHPUnit\Framework\TestCase;
use App\Core\Database;

class DatabaseTest extends TestCase
{
    protected function setUp(): void
    {
        $refl = new \ReflectionClass(Database::class);
        $prop = $refl->getProperty('instance');
        $prop->setValue(null);
    }

    public function test_getInstance_returns_instance(): void
    {
        $db = Database::getInstance();
        $this->assertInstanceOf(Database::class, $db);
    }

    public function test_getInstance_returns_same_instance(): void
    {
        $db1 = Database::getInstance();
        $db2 = Database::getInstance();
        $this->assertSame($db1, $db2);
    }

    public function test_getConnection_returns_pdo(): void
    {
        $pdo = Database::getInstance()->getConnection();
        $this->assertInstanceOf(PDO::class, $pdo);
    }

    public function test_connection_can_query(): void
    {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->query('SELECT 1 AS val');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(1, $row['val']);
    }

    public function test_connection_uses_test_database(): void
    {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->query('SELECT DATABASE() AS db');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertStringContainsString('php_mvc_app_test', $row['db']);
    }

    public function test_singleton_isolation_after_reset(): void
    {
        $db1 = Database::getInstance();

        $refl = new \ReflectionClass(Database::class);
        $prop = $refl->getProperty('instance');
        $prop->setValue(null);

        $db2 = Database::getInstance();
        $this->assertNotSame($db1, $db2);
    }
}
