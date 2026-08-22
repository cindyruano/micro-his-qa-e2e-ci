<?php

declare(strict_types=1);

namespace MicroHis\Domain\Service;

use MicroHis\Domain\Entity\TestExecution;
use MicroHis\Domain\Entity\TestPlan;
use MicroHis\Domain\Exception\QualityGateNotMetException;
use MicroHis\Domain\ValueObject\ExecutionStatus;

/**
 * Servicio de dominio que aplica el "quality gate": una versión solo puede
 * aprobarse para despliegue si el porcentaje de ejecuciones PASSED sobre el
 * total de ejecuciones finalizadas alcanza el umbral definido en el plan.
 *
 * Esta es la regla de negocio central del módulo QA/CI: separa el criterio
 * de "listo para desplegar" de la simple ejecución de pruebas.
 */
final class QualityGateEvaluator
{
    /**
     * @param TestExecution[] $executions
     *
     * @throws QualityGateNotMetException si no hay ejecuciones o el umbral no se alcanza.
     */
    public function evaluate(TestPlan $plan, array $executions): float
    {
        $finished = array_values(array_filter(
            $executions,
            static fn (TestExecution $e) => $e->status()->isFinal()
        ));

        if (count($finished) === 0) {
            throw QualityGateNotMetException::noExecutions();
        }

        $passed = count(array_filter(
            $finished,
            static fn (TestExecution $e) => $e->status() === ExecutionStatus::PASSED
        ));

        $passRate = $passed / count($finished);

        if ($passRate < $plan->qualityGateThreshold()) {
            throw QualityGateNotMetException::belowThreshold($passRate, $plan->qualityGateThreshold());
        }

        return $passRate;
    }
}
