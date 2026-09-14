<?php

declare(strict_types=1);

namespace MicroHisWeek04\Persistence;

use MicroHisWeek04\Domain\Contracts\EvidenceStore;

final class InMemoryEvidenceStore implements EvidenceStore
{
    /** @var array<string, array<string, mixed>> */
    public array $items = [];

    /** @param array<string, mixed> $evidence */
    public function save(string $runId, array $evidence): void
    {
        $this->items[$runId] = $evidence;
    }
}
