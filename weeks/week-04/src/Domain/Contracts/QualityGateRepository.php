<?php

declare(strict_types=1);

namespace MicroHisWeek04\Domain\Contracts;

use MicroHisWeek04\Domain\QualityGateRun;

interface QualityGateRepository
{
    public function save(QualityGateRun $run): void;

    public function latest(): ?QualityGateRun;
}
