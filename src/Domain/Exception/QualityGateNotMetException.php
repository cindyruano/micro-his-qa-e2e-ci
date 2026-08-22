<?php

declare(strict_types=1);

namespace MicroHis\Domain\Exception;

use DomainException;

/**
 * Regla de dominio: una versión (release) no puede aprobarse si el
 * porcentaje de ejecuciones aprobadas no alcanza el umbral configurado
 * del quality gate.
 */
final class QualityGateNotMetException extends DomainException
{
    public static function belowThreshold(float $passRate, float $threshold): self
    {
        $passPct = number_format($passRate * 100, 2);
        $thresholdPct = number_format($threshold * 100, 2);

        return new self(
            "Quality gate no superado: tasa de aprobación {$passPct}% es menor al umbral requerido {$thresholdPct}%."
        );
    }

    public static function noExecutions(): self
    {
        return new self('Quality gate no evaluable: no existen ejecuciones registradas para el plan.');
    }
}
