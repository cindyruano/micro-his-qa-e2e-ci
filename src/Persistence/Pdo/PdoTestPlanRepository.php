<?php

declare(strict_types=1);

namespace MicroHis\Persistence\Pdo;

use MicroHis\Domain\Entity\TestPlan;
use MicroHis\Domain\Exception\PersistenceException;
use MicroHis\Domain\Repository\TestPlanRepositoryInterface;
use PDO;
use PDOException;

final class PdoTestPlanRepository implements TestPlanRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function save(TestPlan $plan): void
    {
        $sql = 'INSERT INTO test_plans (id, release_version, description, quality_gate_threshold, created_at)
                VALUES (:id, :release_version, :description, :threshold, :created_at)
                ON CONFLICT(id) DO UPDATE SET
                    release_version = excluded.release_version,
                    description = excluded.description,
                    quality_gate_threshold = excluded.quality_gate_threshold';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $plan->id(),
                ':release_version' => $plan->releaseVersion(),
                ':description' => $plan->description(),
                ':threshold' => $plan->qualityGateThreshold(),
                ':created_at' => $plan->createdAt(),
            ]);
        } catch (PDOException $e) {
            throw PersistenceException::fromPrevious('TestPlanRepository::save', $e);
        }
    }

    public function findById(string $id): ?TestPlan
    {
        $stmt = $this->pdo->prepare('SELECT * FROM test_plans WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return new TestPlan(
            $row['id'],
            $row['release_version'],
            $row['description'],
            (float) $row['quality_gate_threshold'],
            $row['created_at']
        );
    }
}
