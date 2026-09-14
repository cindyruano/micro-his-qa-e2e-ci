<?php

declare(strict_types=1);

namespace MicroHis\Persistence;

use MicroHis\Domain\Contracts\QualityGateRunRepository;
use MicroHis\Domain\QualityGateRun;
use PDO;

final class PdoQualityGateRunRepository implements QualityGateRunRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function save(QualityGateRun $run): void
    {
        $this->pdo->beginTransaction();
        try {
            $statement = $this->pdo->prepare(
                'INSERT INTO qa_runs (id, commit_sha, environment, status) VALUES (:id, :commit, :environment, :status)'
            );
            $statement->execute([
                ':id' => $run->id,
                ':commit' => $run->commit,
                ':environment' => $run->environment,
                ':status' => $run->status,
            ]);

            $result = $this->pdo->prepare(
                'INSERT INTO qa_gate_results (run_id, control_name, passed) VALUES (:run_id, :control_name, :passed)'
            );
            foreach ($run->controls as $name => $passed) {
                $result->execute([
                    ':run_id' => $run->id,
                    ':control_name' => $name,
                    ':passed' => $passed ? 1 : 0,
                ]);
            }
            $this->pdo->commit();
        } catch (\Throwable $throwable) {
            $this->pdo->rollBack();
            throw $throwable;
        }
    }
}
