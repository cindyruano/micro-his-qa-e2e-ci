<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use MicroHisWeek04\Application\GetLatestQualityGate;
use MicroHisWeek04\Application\RunQualityGate;
use MicroHisWeek04\Domain\Contracts\E2ERunner;
use MicroHisWeek04\Persistence\DeterministicE2ERunner;
use MicroHisWeek04\Persistence\PdoEvidenceStore;
use MicroHisWeek04\Persistence\PdoQualityGateRepository;
use MicroHisWeek04\Presentation\Http\JsonView;
use MicroHisWeek04\Presentation\Http\QualityGateController;
use MicroHisWeek04\Presentation\Http\QualityGateRequest;
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO('sqlite:' . $config['path']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$repository = new PdoQualityGateRepository($pdo);
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$runner = new DeterministicE2ERunner((bool) ($input['induce_failure'] ?? false));
$controller = new QualityGateController(
    new RunQualityGate($repository, new PdoEvidenceStore($pdo), $runner),
    new GetLatestQualityGate($repository),
    new JsonView(),
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->store(QualityGateRequest::fromArray($input));
    exit;
}

$controller->show();
