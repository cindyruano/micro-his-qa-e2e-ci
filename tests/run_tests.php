<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/Support/TestCase.php';
require __DIR__ . '/Support/InMemoryRepositories.php';
require __DIR__ . '/Domain/TestExecutionDomainRulesTest.php';
require __DIR__ . '/Domain/QualityGateEvaluatorTest.php';
require __DIR__ . '/Application/HappyPathExecutionFlowTest.php';
require __DIR__ . '/Persistence/PdoRepositoryPersistenceErrorTest.php';

use MicroHis\Tests\Application\HappyPathExecutionFlowTest;
use MicroHis\Tests\Domain\QualityGateEvaluatorTest;
use MicroHis\Tests\Domain\TestExecutionDomainRulesTest;
use MicroHis\Tests\Persistence\PdoRepositoryPersistenceErrorTest;

$suites = [
    new TestExecutionDomainRulesTest(),
    new QualityGateEvaluatorTest(),
    new HappyPathExecutionFlowTest(),
    new PdoRepositoryPersistenceErrorTest(),
];

$total = 0;
$passed = 0;
$failed = 0;

echo "==============================================\n";
echo " Micro-HIS QA / E2E / CI - Suite de pruebas\n";
echo "==============================================\n\n";

foreach ($suites as $suite) {
    $results = $suite->run();
    foreach ($results as $result) {
        $total++;
        $icon = $result['status'] === 'PASS' ? '[OK]  ' : '[FAIL]';
        echo "{$icon} {$result['test']} ({$result['assertions']} asserts)\n";
        if ($result['status'] === 'PASS') {
            $passed++;
        } else {
            $failed++;
            echo "        -> {$result['error']}\n";
        }
    }
}

echo "\n----------------------------------------------\n";
echo "Total: {$total} | Aprobadas: {$passed} | Fallidas: {$failed}\n";
echo "----------------------------------------------\n";

exit($failed > 0 ? 1 : 0);
