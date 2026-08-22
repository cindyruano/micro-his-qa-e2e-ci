<?php

declare(strict_types=1);

namespace MicroHis\Application\UseCase;

use MicroHis\Domain\Entity\TestPlan;
use MicroHis\Domain\Repository\TestPlanRepositoryInterface;

final class CreateTestPlanUseCase
{
    public function __construct(private TestPlanRepositoryInterface $testPlans)
    {
    }

    public function execute(
        string $id,
        string $releaseVersion,
        string $description,
        float $qualityGateThreshold = 0.90
    ): TestPlan {
        $plan = new TestPlan($id, $releaseVersion, $description, $qualityGateThreshold);
        $this->testPlans->save($plan);

        return $plan;
    }
}
