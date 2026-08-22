<?php

declare(strict_types=1);

namespace MicroHis\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Ambiente en el que se ejecuta una prueba E2E (QA, Staging, Producción).
 * Se modela como Value Object para evitar strings mágicos dispersos en el dominio.
 */
final class Environment
{
    private const ALLOWED = ['QA', 'STAGING', 'PRODUCTION'];

    private string $name;

    public function __construct(string $name)
    {
        $name = strtoupper(trim($name));
        if (!in_array($name, self::ALLOWED, true)) {
            throw new InvalidArgumentException(
                "Ambiente inválido: {$name}. Valores permitidos: " . implode(', ', self::ALLOWED)
            );
        }
        $this->name = $name;
    }

    public function value(): string
    {
        return $this->name;
    }

    public function equals(Environment $other): bool
    {
        return $this->name === $other->name;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
