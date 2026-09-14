<?php

declare(strict_types=1);

use MicroHis\Application\RunQualityGate;
use MicroHis\Domain\TestPlan;
use MicroHis\Persistence\DeterministicE2ERunner;
use MicroHis\Persistence\PdoEvidenceRepository;
use MicroHis\Persistence\PdoQualityGateRunRepository;

return [
    'pdo persists a run, controls and evidence' => static function (): void {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec(file_get_contents(__DIR__ . '/../database/schema.sql'));
        $run = (new RunQualityGate(
            new PdoQualityGateRunRepository($pdo),
            new PdoEvidenceRepository($pdo),
            new DeterministicE2ERunner(),
        ))->execute('abc123', 'test', new TestPlan('smoke', ['login', 'evidence']));

        assertSameValue(1, (int) $pdo->query('SELECT COUNT(*) FROM qa_runs')->fetchColumn());
        assertSameValue(2, (int) $pdo->query('SELECT COUNT(*) FROM qa_gate_results')->fetchColumn());
        assertSameValue(1, (int) $pdo->query('SELECT COUNT(*) FROM qa_evidence')->fetchColumn());
        assertSameValue('passed', $run->status);
    },
];
