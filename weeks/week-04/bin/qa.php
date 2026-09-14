<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use MicroHisWeek04\Application\RunQualityGate;
use MicroHisWeek04\Domain\TestPlan;
use MicroHisWeek04\Persistence\DeterministicE2ERunner;
use MicroHisWeek04\Persistence\PdoEvidenceStore;
use MicroHisWeek04\Persistence\PdoQualityGateRepository;
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO('sqlite:' . $config['path']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$failed = in_array('--induce-failure', $argv, true);
$useCase = new RunQualityGate(
    new PdoQualityGateRepository($pdo),
    new PdoEvidenceStore($pdo),
    new DeterministicE2ERunner($failed),
);

try {
    $run = $useCase->execute(
        $argv[1] ?? 'local',
        $argv[2] ?? 'local',
        new TestPlan('micro-his-qa', ['e2e_login', 'evidence_saved', 'quality_gate']),
    );
    echo "QUALITY GATE PASSED\nRun: {$run->id}\n";
    exit(0);
} catch (RuntimeException) {
    echo "QUALITY GATE FAILED\n";
    exit(1);
}
