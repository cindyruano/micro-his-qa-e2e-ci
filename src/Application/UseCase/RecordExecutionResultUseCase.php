<?php

declare(strict_types=1);

namespace MicroHis\Application\UseCase;

use InvalidArgumentException;
use MicroHis\Domain\Entity\TestExecution;
use MicroHis\Domain\Repository\TestExecutionRepositoryInterface;
use MicroHis\Domain\ValueObject\ExecutionStatus;

/**
 * Registra el resultado final (PASSED/FAILED) de una ejecución E2E.
 * La regla "no se puede cerrar sin evidencia" vive en TestExecution::finish()
 * (capa Domain); este caso de uso solo orquesta la carga/guardado.
 */
final class RecordExecutionResultUseCase
{
    public function __construct(private TestExecutionRepositoryInterface $executions)
    {
    }

    public function execute(string $executionId, ExecutionStatus $result): TestExecution
    {
        $execution = $this->executions->findById($executionId);
        if ($execution === null) {
            throw new InvalidArgumentException("La ejecución '{$executionId}' no existe.");
        }

        $execution->finish($result);
        $this->executions->save($execution);

        return $execution;
    }
}
