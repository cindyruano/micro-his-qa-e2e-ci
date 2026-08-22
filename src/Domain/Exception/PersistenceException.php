<?php

declare(strict_types=1);

namespace MicroHis\Domain\Exception;

use RuntimeException;
use Throwable;

/**
 * Excepción de dominio que envuelve errores de la capa de persistencia
 * (por ejemplo violaciones de integridad referencial) para que las capas
 * superiores no dependan de PDOException directamente.
 */
final class PersistenceException extends RuntimeException
{
    public static function fromPrevious(string $context, Throwable $previous): self
    {
        return new self("Error de persistencia en '{$context}': " . $previous->getMessage(), 0, $previous);
    }
}
