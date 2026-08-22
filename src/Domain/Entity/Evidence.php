<?php

declare(strict_types=1);

namespace MicroHis\Domain\Entity;

use InvalidArgumentException;

/**
 * Evidencia de conservación asociada a una ejecución de prueba
 * (por ejemplo: ruta a un log, captura de pantalla o reporte de reporte E2E).
 */
final class Evidence
{
    private string $id;
    private string $executionId;
    private string $type;
    private string $reference;
    private string $recordedAt;

    private const ALLOWED_TYPES = ['LOG', 'SCREENSHOT', 'REPORT', 'VIDEO'];

    public function __construct(
        string $id,
        string $executionId,
        string $type,
        string $reference,
        ?string $recordedAt = null
    ) {
        $type = strtoupper(trim($type));
        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            throw new InvalidArgumentException(
                "Tipo de evidencia inválido: {$type}. Permitidos: " . implode(', ', self::ALLOWED_TYPES)
            );
        }
        if (trim($reference) === '') {
            throw new InvalidArgumentException('La referencia de la evidencia (ruta/URL) no puede estar vacía.');
        }

        $this->id = $id;
        $this->executionId = $executionId;
        $this->type = $type;
        $this->reference = $reference;
        $this->recordedAt = $recordedAt ?? date(DATE_ATOM);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function executionId(): string
    {
        return $this->executionId;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function reference(): string
    {
        return $this->reference;
    }

    public function recordedAt(): string
    {
        return $this->recordedAt;
    }
}
