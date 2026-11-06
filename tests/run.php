<?php

declare(strict_types=1);

namespace Interface\Tests;

require __DIR__ . '/../autoload.php';
require __DIR__ . '/Assert.php';
require __DIR__ . '/Runner.php';

$runner = new Runner();
$files = glob(__DIR__ . '/*.test.php') ?: [];
sort($files);

foreach ($files as $file) {
    /** @var list<array{0: string, 1: \Closure}> $tests */
    $tests = require $file;
    foreach ($tests as $test) {
        $runner->check($test[0], $test[1]);
    }
}

exit($runner->summary());
