<?php

declare(strict_types=1);

namespace MicroHis\Domain\Repository;

use MicroHis\Domain\Entity\TestPlan;

interface TestPlanRepositoryInterface
{
    public function save(TestPlan $plan): void;

    public function findById(string $id): ?TestPlan;
}
