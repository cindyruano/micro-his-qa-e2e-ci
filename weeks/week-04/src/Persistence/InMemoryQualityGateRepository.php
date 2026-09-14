<?php

declare(strict_types=1);

namespace MicroHisWeek04\Persistence;

use MicroHisWeek04\Domain\Contracts\QualityGateRepository;
use MicroHisWeek04\Domain\QualityGateRun;

final class InMemoryQualityGateRepository implements QualityGateRepository
{
    /** @var list<QualityGateRun> */
    public array $runs = [];

    public function save(QualityGateRun $run): void
    {
        $this->runs[] = $run;
    }

    public function latest(): ?QualityGateRun
    {
        return $this->runs[array_key_last($this->runs)] ?? null;
    }
}
