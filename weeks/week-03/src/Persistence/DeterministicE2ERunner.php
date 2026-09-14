<?php

declare(strict_types=1);

namespace MicroHis\Persistence;

use MicroHis\Domain\Contracts\E2ERunner;
use MicroHis\Domain\TestPlan;

final class DeterministicE2ERunner implements E2ERunner
{
    public function __construct(private bool $induceFailure = false)
    {
    }

    /** @return array<string, bool> */
    public function run(TestPlan $plan): array
    {
        $controls = [];
        foreach ($plan->scenarios as $scenario) {
            $controls[$scenario] = !$this->induceFailure;
        }

        return $controls;
    }
}
