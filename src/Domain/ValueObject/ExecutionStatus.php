<?php

declare(strict_types=1);

namespace MicroHis\Domain\ValueObject;

/**
 * Estados válidos de una ejecución de prueba E2E.
 * La máquina de estados permitida es:
 *   PLANNED -> RUNNING -> PASSED
 *                      \-> FAILED
 * Cualquier otra transición es inválida (regla de dominio).
 */
enum ExecutionStatus: string
{
    case PLANNED = 'PLANNED';
    case RUNNING = 'RUNNING';
    case PASSED  = 'PASSED';
    case FAILED  = 'FAILED';

    /**
     * @return ExecutionStatus[] Estados a los que se puede transicionar desde el actual.
     */
    public function allowedNextStates(): array
    {
        return match ($this) {
            self::PLANNED => [self::RUNNING],
            self::RUNNING => [self::PASSED, self::FAILED],
            self::PASSED, self::FAILED => [],
        };
    }

    public function canTransitionTo(ExecutionStatus $next): bool
    {
        return in_array($next, $this->allowedNextStates(), true);
    }

    public function isFinal(): bool
    {
        return $this === self::PASSED || $this === self::FAILED;
    }
}
