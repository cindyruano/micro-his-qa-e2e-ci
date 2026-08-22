<?php

declare(strict_types=1);

namespace MicroHis\Domain\Entity;

use InvalidArgumentException;

/**
 * Plan de pruebas: agrupa las ejecuciones E2E de una versión (release)
 * candidata a despliegue.
 */
final class TestPlan
{
    private string $id;
    private string $releaseVersion;
    private string $description;
    private float $qualityGateThreshold;
    private string $createdAt;

    public function __construct(
        string $id,
        string $releaseVersion,
        string $description,
        float $qualityGateThreshold = 0.90,
        ?string $createdAt = null
    ) {
        if (trim($id) === '') {
            throw new InvalidArgumentException('El id del plan de pruebas no puede estar vacío.');
        }
        if (trim($releaseVersion) === '') {
            throw new InvalidArgumentException('La versión de release es obligatoria.');
        }
        if ($qualityGateThreshold < 0.0 || $qualityGateThreshold > 1.0) {
            throw new InvalidArgumentException('El umbral del quality gate debe estar entre 0.0 y 1.0.');
        }

        $this->id = $id;
        $this->releaseVersion = $releaseVersion;
        $this->description = $description;
        $this->qualityGateThreshold = $qualityGateThreshold;
        $this->createdAt = $createdAt ?? date(DATE_ATOM);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function releaseVersion(): string
    {
        return $this->releaseVersion;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function qualityGateThreshold(): float
    {
        return $this->qualityGateThreshold;
    }

    public function createdAt(): string
    {
        return $this->createdAt;
    }
}
