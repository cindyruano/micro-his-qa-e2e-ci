<?php

declare(strict_types=1);

namespace MicroHis\Presentation;

use MicroHis\Application\RunQualityGate;
use MicroHis\Domain\TestPlan;
use RuntimeException;

final class CliQualityGate
{
    public function __construct(private RunQualityGate $useCase)
    {
    }

    /** @param list<string> $arguments */
    public function run(array $arguments): int
    {
        $commit = $arguments[1] ?? 'local';
        $environment = $arguments[2] ?? 'local';
        $induceFailure = ($arguments[3] ?? '') === '--induce-failure';
        $plan = new TestPlan('micro-his-qa', ['e2e_login', 'evidence_saved', 'quality_gate']);

        try {
            $run = $this->useCase->execute($commit, $environment, $plan);
            echo "QUALITY GATE PASSED\nRun: {$run->id}\n";
            return 0;
        } catch (RuntimeException $exception) {
            echo "QUALITY GATE FAILED\n";
            return 1;
        }
    }
}
