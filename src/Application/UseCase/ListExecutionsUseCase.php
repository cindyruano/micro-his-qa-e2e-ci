<?php

declare(strict_types=1);

namespace MicroHis\Application\UseCase;

use MicroHis\Domain\Repository\TestExecutionRepositoryInterface;

final class ListExecutionsUseCase
{
    public function __construct(private TestExecutionRepositoryInterface $executions)
    {
    }

    /** @return \MicroHis\Domain\Entity\TestExecution[] */
    public function execute(string $testPlanId): array
    {
        return $this->executions->findByTestPlanId($testPlanId);
    }
}
