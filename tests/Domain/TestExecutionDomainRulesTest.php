<?php

declare(strict_types=1);

namespace MicroHis\Tests\Domain;

use MicroHis\Domain\Entity\TestExecution;
use MicroHis\Domain\Exception\EvidenceRequiredException;
use MicroHis\Domain\Exception\InvalidStatusTransitionException;
use MicroHis\Domain\ValueObject\Environment;
use MicroHis\Domain\ValueObject\ExecutionStatus;
use MicroHis\Tests\Support\TestCase;

/**
 * REGLA DE DOMINIO: una ejecución no puede finalizar (PASSED/FAILED)
 * sin evidencia asociada, y solo se permiten las transiciones de estado
 * definidas en la máquina de estados de ExecutionStatus.
 */
final class TestExecutionDomainRulesTest extends TestCase
{
    public function testNoSePuedeFinalizarSinEvidencia(): void
    {
        $execution = new TestExecution('EX-1', 'PLAN-1', 'Login exitoso', new Environment('QA'));
        $execution->start();

        $this->assertThrows(
            EvidenceRequiredException::class,
            static fn () => $execution->finish(ExecutionStatus::PASSED),
            'Debe exigir evidencia antes de cerrar la ejecución.'
        );
    }

    public function testSePuedeFinalizarCuandoHayEvidencia(): void
    {
        $execution = new TestExecution('EX-2', 'PLAN-1', 'Login exitoso', new Environment('QA'));
        $execution->start();
        $execution->attachEvidence();

        $execution->finish(ExecutionStatus::PASSED);

        $this->assertEquals(ExecutionStatus::PASSED, $execution->status());
        $this->assertTrue($execution->finishedAt() !== null);
    }

    public function testNoPermiteSaltarDePlanificadaAAprobada(): void
    {
        $execution = new TestExecution('EX-3', 'PLAN-1', 'Logout', new Environment('QA'));

        // PLANNED -> PASSED no es una transición válida (falta pasar por RUNNING).
        $this->assertThrows(
            InvalidStatusTransitionException::class,
            static fn () => $execution->finish(ExecutionStatus::PASSED)
        );
    }

    public function testNoPermiteReiniciarUnaEjecucionYaFinalizada(): void
    {
        $execution = new TestExecution('EX-4', 'PLAN-1', 'Registro de paciente', new Environment('STAGING'));
        $execution->start();
        $execution->attachEvidence();
        $execution->finish(ExecutionStatus::FAILED);

        $this->assertThrows(
            InvalidStatusTransitionException::class,
            static fn () => $execution->start(),
            'Un estado final (FAILED) no debe permitir volver a RUNNING.'
        );
    }
}
