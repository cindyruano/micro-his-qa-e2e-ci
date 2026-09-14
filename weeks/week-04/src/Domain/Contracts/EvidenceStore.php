<?php

declare(strict_types=1);

namespace MicroHisWeek04\Domain\Contracts;

interface EvidenceStore
{
    /** @param array<string, mixed> $evidence */
    public function save(string $runId, array $evidence): void;
}
