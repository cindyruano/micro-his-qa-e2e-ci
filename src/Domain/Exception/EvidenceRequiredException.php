<?php

declare(strict_types=1);

namespace MicroHis\Domain\Exception;

use DomainException;

/**
 * Regla de dominio: una ejecución no puede cerrarse (PASSED/FAILED)
 * sin al menos una evidencia asociada.
 */
final class EvidenceRequiredException extends DomainException
{
    public static function forExecution(string $executionId): self
    {
        return new self(
            "La ejecución '{$executionId}' no puede finalizarse sin evidencia registrada."
        );
    }
}
