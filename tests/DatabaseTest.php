<?php

namespace Tests;

use App\Database;
use PHPUnit\Framework\TestCase;

/**
 * A tiny smoke test so `composer test` has something to run out of the box.
 * Candidates are expected to add real coverage as part of task 9.
 */
class DatabaseTest extends TestCase
{
    public function test_it_connects_to_sqlite(): void
    {
        $pdo = Database::connection();

        $this->assertSame(
            'sqlite',
            $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME)
        );
    }
}
