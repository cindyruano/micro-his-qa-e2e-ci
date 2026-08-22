<?php

declare(strict_types=1);

namespace MicroHis\Tests\Support;

use MicroHis\Domain\Entity\Evidence;
use MicroHis\Domain\Entity\TestExecution;
use MicroHis\Domain\Entity\TestPlan;
use MicroHis\Domain\Repository\EvidenceRepositoryInterface;
use MicroHis\Domain\Repository\TestExecutionRepositoryInterface;
use MicroHis\Domain\Repository\TestPlanRepositoryInterface;

/**
 * Dobles de prueba (fakes en memoria) para las pruebas de Application/Domain
 * que no necesitan tocar la base de datos real.
 */
final class InMemoryTestPlanRepository implements TestPlanRepositoryInterface
{
    /** @var array<string, TestPlan> */
    private array $storage = [];

    public function save(TestPlan $plan): void
    {
        $this->storage[$plan->id()] = $plan;
    }

    public function findById(string $id): ?TestPlan
    {
        return $this->storage[$id] ?? null;
    }
}

final class InMemoryTestExecutionRepository implements TestExecutionRepositoryInterface
{
    /** @var array<string, TestExecution> */
    private array $storage = [];

    public function save(TestExecution $execution): void
    {
        $this->storage[$execution->id()] = $execution;
    }

    public function findById(string $id): ?TestExecution
    {
        return $this->storage[$id] ?? null;
    }

    public function findByTestPlanId(string $testPlanId): array
    {
        return array_values(array_filter(
            $this->storage,
            static fn (TestExecution $e) => $e->testPlanId() === $testPlanId
        ));
    }
}

final class InMemoryEvidenceRepository implements EvidenceRepositoryInterface
{
    /** @var Evidence[] */
    private array $storage = [];

    public function save(Evidence $evidence): void
    {
        $this->storage[] = $evidence;
    }

    public function findByExecutionId(string $executionId): array
    {
        return array_values(array_filter(
            $this->storage,
            static fn (Evidence $e) => $e->executionId() === $executionId
        ));
    }
}
