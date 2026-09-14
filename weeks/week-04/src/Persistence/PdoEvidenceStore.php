<?php

declare(strict_types=1);

namespace MicroHisWeek04\Persistence;

use MicroHisWeek04\Domain\Contracts\EvidenceStore;
use PDO;

final class PdoEvidenceStore implements EvidenceStore
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @param array<string, mixed> $evidence */
    public function save(string $runId, array $evidence): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO qa_evidence (run_id, payload_json) VALUES (:run_id, :payload_json)'
        );
        $statement->execute([
            ':run_id' => $runId,
            ':payload_json' => json_encode($evidence, JSON_THROW_ON_ERROR),
        ]);
    }
}
