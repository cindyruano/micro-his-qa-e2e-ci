<?php

declare(strict_types=1);

namespace MicroHis\Domain\Exception;

use DomainException;
use MicroHis\Domain\ValueObject\ExecutionStatus;

/**
 * Regla de dominio: solo se permiten transiciones de estado válidas
 * en el ciclo de vida de una ejecución de prueba.
 */
final class InvalidStatusTransitionException extends DomainException
{
    public static function from(ExecutionStatus $current, ExecutionStatus $attempted): self
    {
        return new self(
            "Transición inválida: no se puede pasar de '{$current->value}' a '{$attempted->value}'."
        );
    }
}
