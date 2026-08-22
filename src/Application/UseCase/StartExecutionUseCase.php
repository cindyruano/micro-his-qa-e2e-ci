<?php

declare(strict_types=1);

namespace MicroHis\Application\UseCase;

use InvalidArgumentException;
use MicroHis\Domain\Entity\TestExecution;
use MicroHis\Domain\Repository\TestExecutionRepositoryInterface;
use MicroHis\Domain\Repository\TestPlanRepositoryInterface;
use MicroHis\Domain\ValueObject\Environment;
use MicroHis\Domain\ValueObject\ExecutionStatus;

/**
 * Planifica y arranca una ejecución E2E dentro de un plan de pruebas existente.
 */
final class StartExecutionUseCase
{
    public function __construct(
        private TestPlanRepositoryInterface $testPlans,
        private TestExecutionRepositoryInterface $executions
    ) {
    }

    public function execute(string $executionId, string $testPlanId, string $scenarioName, string $environment): TestExecution
    {
        $plan = $this->testPlans->findById($testPlanId);
        if ($plan === null) {
            throw new InvalidArgumentException("El plan de pruebas '{$testPlanId}' no existe.");
        }

        $execution = new TestExecution(
            $executionId,
            $plan->id(),
            $scenarioName,
            new Environment($environment),
            ExecutionStatus::PLANNED
        );

        $execution->start();
        $this->executions->save($execution);

        return $execution;
    }
}
