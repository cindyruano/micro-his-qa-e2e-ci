<?php

declare(strict_types=1);

namespace MicroHis\Application\UseCase;

use InvalidArgumentException;
use MicroHis\Domain\Entity\Evidence;
use MicroHis\Domain\Repository\EvidenceRepositoryInterface;
use MicroHis\Domain\Repository\TestExecutionRepositoryInterface;

/**
 * Adjunta y conserva una evidencia (log, captura, reporte o video) a una
 * ejecución de prueba existente.
 */
final class AttachEvidenceUseCase
{
    public function __construct(
        private TestExecutionRepositoryInterface $executions,
        private EvidenceRepositoryInterface $evidences
    ) {
    }

    public function execute(string $evidenceId, string $executionId, string $type, string $reference): Evidence
    {
        $execution = $this->executions->findById($executionId);
        if ($execution === null) {
            throw new InvalidArgumentException("La ejecución '{$executionId}' no existe.");
        }

        $evidence = new Evidence($evidenceId, $executionId, $type, $reference);
        $this->evidences->save($evidence);

        $execution->attachEvidence();
        $this->executions->save($execution);

        return $evidence;
    }
}
