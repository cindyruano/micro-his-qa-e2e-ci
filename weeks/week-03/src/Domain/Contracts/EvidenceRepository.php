<?php

declare(strict_types=1);

namespace MicroHis\Domain\Contracts;

interface EvidenceRepository
{
    /** @param array<string, mixed> $evidence */
    public function save(string $runId, array $evidence): void;
}
