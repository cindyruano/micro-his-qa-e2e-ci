<?php

declare(strict_types=1);

namespace MicroHisWeek04\Application;

use MicroHisWeek04\Domain\Contracts\QualityGateRepository;
use MicroHisWeek04\Domain\QualityGateRun;

final class GetLatestQualityGate
{
    public function __construct(private QualityGateRepository $repository)
    {
    }

    public function execute(): ?QualityGateRun
    {
        return $this->repository->latest();
    }
}
