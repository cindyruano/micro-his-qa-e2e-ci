<?php

declare(strict_types=1);

namespace MicroHis\Application\UseCase;

use InvalidArgumentException;
use MicroHis\Domain\Repository\TestExecutionRepositoryInterface;
use MicroHis\Domain\Repository\TestPlanRepositoryInterface;
use MicroHis\Domain\Service\QualityGateEvaluator;

/**
 * Aplica el quality gate de un plan de pruebas: determina si la versión
 * puede aprobarse para despliegue según la tasa de ejecuciones aprobadas.
 */
final class EvaluateQualityGateUseCase
{
    public function __construct(
        private TestPlanRepositoryInterface $testPlans,
        private TestExecutionRepositoryInterface $executions,
        private QualityGateEvaluator $evaluator
    ) {
    }

    /** @return float Tasa de aprobación (0.0 - 1.0). Lanza excepción si no se cumple el gate. */
    public function execute(string $testPlanId): float
    {
        $plan = $this->testPlans->findById($testPlanId);
        if ($plan === null) {
            throw new InvalidArgumentException("El plan de pruebas '{$testPlanId}' no existe.");
        }

        $executions = $this->executions->findByTestPlanId($testPlanId);

        return $this->evaluator->evaluate($plan, $executions);
    }
}
