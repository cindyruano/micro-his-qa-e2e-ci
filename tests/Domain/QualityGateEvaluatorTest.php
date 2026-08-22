<?php

declare(strict_types=1);

namespace MicroHis\Tests\Domain;

use MicroHis\Domain\Entity\TestExecution;
use MicroHis\Domain\Entity\TestPlan;
use MicroHis\Domain\Exception\QualityGateNotMetException;
use MicroHis\Domain\Service\QualityGateEvaluator;
use MicroHis\Domain\ValueObject\Environment;
use MicroHis\Domain\ValueObject\ExecutionStatus;
use MicroHis\Tests\Support\TestCase;

/**
 * REGLA DE DOMINIO: el quality gate rechaza una versión cuya tasa de
 * aprobación esté por debajo del umbral definido en el plan de pruebas.
 */
final class QualityGateEvaluatorTest extends TestCase
{
    private function finishedExecution(string $id, ExecutionStatus $result): TestExecution
    {
        $execution = new TestExecution($id, 'PLAN-GATE', 'Escenario ' . $id, new Environment('QA'));
        $execution->start();
        $execution->attachEvidence();
        $execution->finish($result);
        return $execution;
    }

    public function testAprueba100PorCientoDeExito(): void
    {
        $plan = new TestPlan('PLAN-GATE', '1.0.0', 'Release completo', 0.90);
        $executions = [
            $this->finishedExecution('E1', ExecutionStatus::PASSED),
            $this->finishedExecution('E2', ExecutionStatus::PASSED),
        ];

        $rate = (new QualityGateEvaluator())->evaluate($plan, $executions);

        $this->assertEquals(1.0, $rate);
    }

    public function testRechazaCuandoLaTasaEsMenorAlUmbral(): void
    {
        $plan = new TestPlan('PLAN-GATE', '1.0.0', 'Release con fallas', 0.90);
        $executions = [
            $this->finishedExecution('E1', ExecutionStatus::PASSED),
            $this->finishedExecution('E2', ExecutionStatus::FAILED),
        ];

        // 50% de aprobación < 90% de umbral -> debe rechazar.
        $this->assertThrows(
            QualityGateNotMetException::class,
            fn () => (new QualityGateEvaluator())->evaluate($plan, $executions)
        );
    }

    public function testRechazaCuandoNoHayEjecuciones(): void
    {
        $plan = new TestPlan('PLAN-GATE', '1.0.0', 'Sin ejecuciones', 0.90);

        $this->assertThrows(
            QualityGateNotMetException::class,
            fn () => (new QualityGateEvaluator())->evaluate($plan, [])
        );
    }
}
