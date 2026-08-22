<?php

declare(strict_types=1);

namespace MicroHis\Domain\Repository;

use MicroHis\Domain\Entity\Evidence;

interface EvidenceRepositoryInterface
{
    public function save(Evidence $evidence): void;

    /** @return Evidence[] */
    public function findByExecutionId(string $executionId): array;
}
