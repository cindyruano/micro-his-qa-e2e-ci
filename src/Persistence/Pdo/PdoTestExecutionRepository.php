<?php

declare(strict_types=1);

namespace MicroHis\Persistence\Pdo;

use MicroHis\Domain\Entity\TestExecution;
use MicroHis\Domain\Exception\PersistenceException;
use MicroHis\Domain\Repository\TestExecutionRepositoryInterface;
use MicroHis\Domain\ValueObject\Environment;
use MicroHis\Domain\ValueObject\ExecutionStatus;
use PDO;
use PDOException;

final class PdoTestExecutionRepository implements TestExecutionRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function save(TestExecution $execution): void
    {
        // Nota: test_plan_id tiene FOREIGN KEY hacia test_plans(id).
        // Si el plan no existe, PDO lanza PDOException por violación de
        // integridad referencial, la cual se envuelve como PersistenceException
        // (regla de dominio "error de persistencia" cubierta en pruebas).
        $sql = 'INSERT INTO test_executions (id, test_plan_id, scenario_name, environment, status, evidence_count, finished_at)
                VALUES (:id, :test_plan_id, :scenario_name, :environment, :status, :evidence_count, :finished_at)
                ON CONFLICT(id) DO UPDATE SET
                    status = excluded.status,
                    evidence_count = excluded.evidence_count,
                    finished_at = excluded.finished_at';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $execution->id(),
                ':test_plan_id' => $execution->testPlanId(),
                ':scenario_name' => $execution->scenarioName(),
                ':environment' => $execution->environment()->value(),
                ':status' => $execution->status()->value,
                ':evidence_count' => $execution->evidenceCount(),
                ':finished_at' => $execution->finishedAt(),
            ]);
        } catch (PDOException $e) {
            throw PersistenceException::fromPrevious('TestExecutionRepository::save', $e);
        }
    }

    public function findById(string $id): ?TestExecution
    {
        $stmt = $this->pdo->prepare('SELECT * FROM test_executions WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $this->hydrate($row);
    }

    public function findByTestPlanId(string $testPlanId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM test_executions WHERE test_plan_id = :plan_id');
        $stmt->execute([':plan_id' => $testPlanId]);

        return array_map(fn (array $row) => $this->hydrate($row), $stmt->fetchAll());
    }

    private function hydrate(array $row): TestExecution
    {
        return TestExecution::reconstruct(
            $row['id'],
            $row['test_plan_id'],
            $row['scenario_name'],
            new Environment($row['environment']),
            ExecutionStatus::from($row['status']),
            (int) $row['evidence_count'],
            $row['finished_at']
        );
    }
}
