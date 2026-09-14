<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use MicroHis\Application\RunQualityGate;
use MicroHis\Domain\TestPlan;
use MicroHis\Persistence\DeterministicE2ERunner;
use MicroHis\Persistence\PdoEvidenceRepository;
use MicroHis\Persistence\PdoQualityGateRunRepository;
use MicroHis\Presentation\CliQualityGate;

$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO('sqlite:' . $config['path']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$induceFailure = in_array('--induce-failure', $argv, true);
$useCase = new RunQualityGate(
    new PdoQualityGateRunRepository($pdo),
    new PdoEvidenceRepository($pdo),
    new DeterministicE2ERunner($induceFailure),
);

exit((new CliQualityGate($useCase))->run($argv));
