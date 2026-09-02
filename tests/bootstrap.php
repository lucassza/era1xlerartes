<?php

declare(strict_types=1);

$forceEnv = static function (string $name, string $value): void {
    putenv($name.'='.$value);
    $_ENV[$name] = $value;
    $_SERVER[$name] = $value;
};

$configPath = __DIR__.'/../phpunit.xml';

if (is_file($configPath) && ($config = @simplexml_load_file($configPath)) !== false) {
    foreach ($config->php->env ?? [] as $entry) {
        if ((string) $entry['force'] !== 'true') {
            continue;
        }

        $forceEnv((string) $entry['name'], (string) $entry['value']);
    }
}

foreach (['APP_CONFIG_CACHE', 'APP_ROUTES_CACHE', 'APP_EVENTS_CACHE'] as $cacheKey) {
    $forceEnv($cacheKey, __DIR__.'/../storage/framework/cache/phpunit/does-not-exist.php');
}

require __DIR__.'/../vendor/autoload.php';
