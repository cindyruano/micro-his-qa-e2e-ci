<?php

declare(strict_types=1);

namespace MicroHis\Domain\Contracts;

use MicroHis\Domain\QualityGateRun;

interface QualityGateRunRepository
{
    public function save(QualityGateRun $run): void;
}
