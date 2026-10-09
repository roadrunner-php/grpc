<?php

declare(strict_types=1);

use Testo\Application\Config\ApplicationConfig;
use Testo\Application\Config\FinderConfig;
use Testo\Application\Config\SuiteConfig;
use Testo\Bridge\Mockery\MockeryPlugin;

ini_set('error_reporting', (string) (E_ALL ^ E_DEPRECATED));
ini_set('memory_limit', '-1');

return new ApplicationConfig(
    // For Codecov.
    src: ['src'],
    suites: [
        new SuiteConfig(
            name: 'Unit',
            location: new FinderConfig(
                include: ['tests'],
                exclude: ['tests/generated'],
            ),
        ),

        // For inline tests and benchmarks right in the project source code, in the src folder.
        new SuiteConfig(
            name: 'Sources',
            location: ['src'],
        ),
    ],
    plugins: [
        new MockeryPlugin(),
    ],
);
