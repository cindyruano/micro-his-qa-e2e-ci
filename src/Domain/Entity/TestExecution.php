<?php

declare(strict_types=1);

namespace MicroHis\Domain\Entity;

use InvalidArgumentException;
use MicroHis\Domain\Exception\EvidenceRequiredException;
use MicroHis\Domain\Exception\InvalidStatusTransitionException;
use MicroHis\Domain\ValueObject\Environment;
use MicroHis\Domain\ValueObject\ExecutionStatus;

/**
 * Ejecución individual de una prueba E2E dentro de un plan de pruebas.
 *
 * Reglas de dominio encapsuladas aquí:
 *  1. Las transiciones de estado siguen la máquina de estados de ExecutionStatus.
 *  2. No puede finalizarse (PASSED/FAILED) sin evidencia asociada (ver hasEvidence()).
 */
final class TestExecution
{
    private string $id;
    private string $testPlanId;
    private string $scenarioName;
    private Environment $environment;
    private ExecutionStatus $status;
    private int $evidenceCount = 0;
    private ?string $finishedAt = null;

    public function __construct(
        string $id,
        string $testPlanId,
        string $scenarioName,
        Environment $environment,
        ExecutionStatus $status = ExecutionStatus::PLANNED
    ) {
        if (trim($scenarioName) === '') {
            throw new InvalidArgumentException('El nombre del escenario E2E no puede estar vacío.');
        }

        $this->id = $id;
        $this->testPlanId = $testPlanId;
        $this->scenarioName = $scenarioName;
        $this->environment = $environment;
        $this->status = $status;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function testPlanId(): string
    {
        return $this->testPlanId;
    }

    public function scenarioName(): string
    {
        return $this->scenarioName;
    }

    public function environment(): Environment
    {
        return $this->environment;
    }

    public function status(): ExecutionStatus
    {
        return $this->status;
    }

    public function evidenceCount(): int
    {
        return $this->evidenceCount;
    }

    public function finishedAt(): ?string
    {
        return $this->finishedAt;
    }

    /** Se invoca al comenzar la ejecución (PLANNED -> RUNNING). */
    public function start(): void
    {
        $this->transitionTo(ExecutionStatus::RUNNING);
    }

    /** Registra que se adjuntó una evidencia a esta ejecución. */
    public function attachEvidence(): void
    {
        $this->evidenceCount++;
    }

    public function hasEvidence(): bool
    {
        return $this->evidenceCount > 0;
    }

    /**
     * Cierra la ejecución con un resultado final.
     * Regla de dominio: exige al menos una evidencia antes de permitir el cierre.
     *
     * @throws EvidenceRequiredException
     * @throws InvalidStatusTransitionException
     */
    public function finish(ExecutionStatus $result): void
    {
        if (!in_array($result, [ExecutionStatus::PASSED, ExecutionStatus::FAILED], true)) {
            throw new InvalidArgumentException('El resultado final debe ser PASSED o FAILED.');
        }

        // 1) Primero se valida que la transición de estado sea legal
        //    (ej.: no se puede finalizar algo que sigue en PLANNED).
        if (!$this->status->canTransitionTo($result)) {
            throw InvalidStatusTransitionException::from($this->status, $result);
        }

        // 2) Luego se aplica la regla de negocio de evidencia obligatoria.
        if (!$this->hasEvidence()) {
            throw EvidenceRequiredException::forExecution($this->id);
        }

        $this->status = $result;
        $this->finishedAt = date(DATE_ATOM);
    }

    private function transitionTo(ExecutionStatus $next): void
    {
        if (!$this->status->canTransitionTo($next)) {
            throw InvalidStatusTransitionException::from($this->status, $next);
        }
        $this->status = $next;
    }

    /**
     * Reconstruye una ejecución ya persistida (usado solo por la capa de
     * Persistence al leer de la base de datos). A diferencia del
     * constructor + start()/finish(), este método NO revalida las
     * transiciones de estado porque el dato ya fue validado al guardarse.
     */
    public static function reconstruct(
        string $id,
        string $testPlanId,
        string $scenarioName,
        Environment $environment,
        ExecutionStatus $status,
        int $evidenceCount,
        ?string $finishedAt
    ): self {
        $execution = new self($id, $testPlanId, $scenarioName, $environment, $status);
        $execution->evidenceCount = $evidenceCount;
        $execution->finishedAt = $finishedAt;

        return $execution;
    }
}
