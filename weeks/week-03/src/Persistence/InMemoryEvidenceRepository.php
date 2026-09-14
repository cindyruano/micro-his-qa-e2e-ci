<?php

declare(strict_types=1);

namespace MicroHis\Persistence;

use MicroHis\Domain\Contracts\EvidenceRepository;

final class InMemoryEvidenceRepository implements EvidenceRepository
{
    /** @var array<string, array<string, mixed>> */
    public array $evidence = [];

    /** @param array<string, mixed> $evidence */
    public function save(string $runId, array $evidence): void
    {
        $this->evidence[$runId] = $evidence;
    }
}
