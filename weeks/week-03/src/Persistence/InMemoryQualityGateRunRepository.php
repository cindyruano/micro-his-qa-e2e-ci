<?php

declare(strict_types=1);

namespace MicroHis\Persistence;

use MicroHis\Domain\Contracts\QualityGateRunRepository;
use MicroHis\Domain\QualityGateRun;

final class InMemoryQualityGateRunRepository implements QualityGateRunRepository
{
    /** @var array<string, QualityGateRun> */
    public array $runs = [];

    public function save(QualityGateRun $run): void
    {
        $this->runs[$run->id] = $run;
    }
}
