<?php

declare(strict_types=1);

namespace MicroHis\Persistence\Pdo;

use MicroHis\Domain\Entity\Evidence;
use MicroHis\Domain\Exception\PersistenceException;
use MicroHis\Domain\Repository\EvidenceRepositoryInterface;
use PDO;
use PDOException;

final class PdoEvidenceRepository implements EvidenceRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function save(Evidence $evidence): void
    {
        $sql = 'INSERT INTO evidences (id, execution_id, type, reference, recorded_at)
                VALUES (:id, :execution_id, :type, :reference, :recorded_at)';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $evidence->id(),
                ':execution_id' => $evidence->executionId(),
                ':type' => $evidence->type(),
                ':reference' => $evidence->reference(),
                ':recorded_at' => $evidence->recordedAt(),
            ]);
        } catch (PDOException $e) {
            throw PersistenceException::fromPrevious('EvidenceRepository::save', $e);
        }
    }

    public function findByExecutionId(string $executionId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM evidences WHERE execution_id = :execution_id ORDER BY recorded_at ASC');
        $stmt->execute([':execution_id' => $executionId]);

        return array_map(
            static fn (array $row) => new Evidence($row['id'], $row['execution_id'], $row['type'], $row['reference'], $row['recorded_at']),
            $stmt->fetchAll()
        );
    }
}
