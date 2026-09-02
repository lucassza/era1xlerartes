<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use LogicException;

abstract class TestCase extends BaseTestCase
{
    private const TEST_DATABASE = 'era1x_ler_artes_test';

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertRunningAgainstTestDatabase();
    }

    private function assertRunningAgainstTestDatabase(): void
    {
        $default = config('database.default');
        $configured = config('database.connections.'.$default.'.database');

        if ($default !== 'pgsql_testing' || $configured !== self::TEST_DATABASE) {
            throw new LogicException(sprintf(
                'A suíte de testes está apontando para o banco principal da aplicação (conexão "%s", banco "%s"). '
                .'Verifique se as variáveis de ambiente do container não estão sobrescrevendo o phpunit.xml.',
                (string) $default,
                (string) $configured,
            ));
        }

        $connected = DB::connection()->getDatabaseName();

        if ($connected !== self::TEST_DATABASE) {
            throw new LogicException(sprintf(
                'A conexão de testes abriu o banco "%s" em vez de "%s".',
                (string) $connected,
                self::TEST_DATABASE,
            ));
        }
    }
}
