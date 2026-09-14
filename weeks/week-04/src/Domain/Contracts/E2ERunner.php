<?php

declare(strict_types=1);

namespace MicroHisWeek04\Domain\Contracts;

use MicroHisWeek04\Domain\TestPlan;

interface E2ERunner
{
    /** @return array<string, bool> */
    public function run(TestPlan $plan): array;
}
