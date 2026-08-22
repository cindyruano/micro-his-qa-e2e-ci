<?php

declare(strict_types=1);

namespace MicroHis\Domain\Repository;

use MicroHis\Domain\Entity\TestExecution;

interface TestExecutionRepositoryInterface
{
    public function save(TestExecution $execution): void;

    public function findById(string $id): ?TestExecution;

    /** @return TestExecution[] */
    public function findByTestPlanId(string $testPlanId): array;
}
