<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use LogicException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (
            app()->environment('testing')
            && (
                config('database.default') !== 'pgsql_testing'
                || config('database.connections.pgsql_testing.database') !== 'era1x_ler_artes_test'
            )
        ) {
            throw new LogicException('A suíte de testes está apontando para o banco principal da aplicação.');
        }
    }
}
