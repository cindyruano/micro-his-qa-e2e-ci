<?php

declare(strict_types=1);

namespace MicroHisWeek04\Persistence;

use MicroHisWeek04\Domain\Contracts\QualityGateRepository;
use MicroHisWeek04\Domain\QualityGateRun;
use PDO;

final class PdoQualityGateRepository implements QualityGateRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function save(QualityGateRun $run): void
    {
        $this->pdo->beginTransaction();
        try {
            $insertRun = $this->pdo->prepare(
                'INSERT INTO qa_runs (id, commit_sha, environment, status) VALUES (:id, :commit, :environment, :status)'
            );
            $insertRun->execute([
                ':id' => $run->id,
                ':commit' => $run->commit,
                ':environment' => $run->environment,
                ':status' => $run->status,
            ]);

            $insertControl = $this->pdo->prepare(
                'INSERT INTO qa_gate_results (run_id, control_name, passed) VALUES (:run_id, :control_name, :passed)'
            );
            foreach ($run->controls as $name => $passed) {
                $insertControl->execute([
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

    public function latest(): ?QualityGateRun
    {
        $row = $this->pdo->query(
            'SELECT id, commit_sha, environment, status FROM qa_runs ORDER BY created_at DESC, rowid DESC LIMIT 1'
        )->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }

        $controlsStatement = $this->pdo->prepare(
            'SELECT control_name, passed FROM qa_gate_results WHERE run_id = :run_id'
        );
        $controlsStatement->execute([':run_id' => $row['id']]);
        $controls = [];
        foreach ($controlsStatement->fetchAll(PDO::FETCH_ASSOC) as $control) {
            $controls[$control['control_name']] = (bool) $control['passed'];
        }

        return new QualityGateRun(
            $row['id'],
            $row['commit_sha'],
            $row['environment'],
            $controls,
            $row['status'],
        );
    }
}
