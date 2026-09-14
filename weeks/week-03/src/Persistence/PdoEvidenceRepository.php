<?php

declare(strict_types=1);

namespace MicroHis\Persistence;

use MicroHis\Domain\Contracts\EvidenceRepository;
use PDO;

final class PdoEvidenceRepository implements EvidenceRepository
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
