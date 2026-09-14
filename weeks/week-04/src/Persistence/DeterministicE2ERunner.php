<?php

declare(strict_types=1);

namespace MicroHisWeek04\Persistence;

use MicroHisWeek04\Domain\Contracts\E2ERunner;
use MicroHisWeek04\Domain\TestPlan;

final class DeterministicE2ERunner implements E2ERunner
{
    public function __construct(private bool $induceFailure = false)
    {
    }

    /** @return array<string, bool> */
    public function run(TestPlan $plan): array
    {
        $results = [];
        foreach ($plan->scenarios as $scenario) {
            $results[$scenario] = !$this->induceFailure;
        }

        return $results;
    }
}
