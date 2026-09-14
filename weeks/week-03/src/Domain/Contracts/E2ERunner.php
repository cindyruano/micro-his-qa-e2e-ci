<?php

declare(strict_types=1);

namespace MicroHis\Domain\Contracts;

use MicroHis\Domain\TestPlan;

interface E2ERunner
{
    /** @return array<string, bool> */
    public function run(TestPlan $plan): array;
}
