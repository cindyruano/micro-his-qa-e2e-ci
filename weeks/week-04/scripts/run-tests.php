<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../tests/TestSupport.php';

$tests = [];
foreach (glob(__DIR__ . '/../tests/*Test.php') as $file) {
    $tests = array_merge($tests, require $file);
}

$passed = 0;
$failed = 0;
foreach ($tests as $name => $test) {
    try {
        $test();
        $passed++;
        echo "PASS: {$name}\n";
    } catch (Throwable $throwable) {
        $failed++;
        echo "FAIL: {$name} - {$throwable->getMessage()}\n";
    }
}

echo "Total: " . count($tests) . " | Passed: {$passed} | Failed: {$failed}\n";
exit($failed > 0 ? 1 : 0);
