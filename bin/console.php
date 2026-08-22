#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use MicroHis\Application\UseCase\AttachEvidenceUseCase;
use MicroHis\Application\UseCase\CreateTestPlanUseCase;
use MicroHis\Application\UseCase\EvaluateQualityGateUseCase;
use MicroHis\Application\UseCase\ListExecutionsUseCase;
use MicroHis\Application\UseCase\RecordExecutionResultUseCase;
use MicroHis\Application\UseCase\StartExecutionUseCase;
use MicroHis\Domain\Service\QualityGateEvaluator;
use MicroHis\Persistence\Pdo\PdoConnectionFactory;
use MicroHis\Persistence\Pdo\PdoEvidenceRepository;
use MicroHis\Persistence\Pdo\PdoTestExecutionRepository;
use MicroHis\Persistence\Pdo\PdoTestPlanRepository;
use MicroHis\Presentation\Cli\Console;

$config = require __DIR__ . '/../config/database.php';
$pdo = PdoConnectionFactory::create($config);

$testPlans = new PdoTestPlanRepository($pdo);
$executions = new PdoTestExecutionRepository($pdo);
$evidences = new PdoEvidenceRepository($pdo);

$console = new Console(
    new CreateTestPlanUseCase($testPlans),
    new StartExecutionUseCase($testPlans, $executions),
    new AttachEvidenceUseCase($executions, $evidences),
    new RecordExecutionResultUseCase($executions),
    new EvaluateQualityGateUseCase($testPlans, $executions, new QualityGateEvaluator()),
    new ListExecutionsUseCase($executions)
);

exit($console->run($argv));
