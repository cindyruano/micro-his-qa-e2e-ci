<?php

declare(strict_types=1);

/**
 * Front controller HTTP mínimo (Presentation layer, sin framework).
 * Expone el mismo conjunto de casos de uso que la consola, mediante un
 * enrutador manual basado en la ruta y el método HTTP.
 *
 * Ejemplos:
 *   GET  /?route=executions&plan_id=PLAN-1
 *   GET  /?route=gate&plan_id=PLAN-1
 */

require __DIR__ . '/../bootstrap.php';

use MicroHis\Application\UseCase\EvaluateQualityGateUseCase;
use MicroHis\Application\UseCase\ListExecutionsUseCase;
use MicroHis\Domain\Exception\PersistenceException;
use MicroHis\Domain\Exception\QualityGateNotMetException;
use MicroHis\Domain\Service\QualityGateEvaluator;
use MicroHis\Persistence\Pdo\PdoConnectionFactory;
use MicroHis\Persistence\Pdo\PdoTestExecutionRepository;
use MicroHis\Persistence\Pdo\PdoTestPlanRepository;

header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/../config/database.php';
$pdo = PdoConnectionFactory::create($config);

$testPlans = new PdoTestPlanRepository($pdo);
$executions = new PdoTestExecutionRepository($pdo);

$route = $_GET['route'] ?? '';
$planId = $_GET['plan_id'] ?? '';

try {
    switch ($route) {
        case 'executions':
            $list = (new ListExecutionsUseCase($executions))->execute($planId);
            echo json_encode(array_map(static fn ($e) => [
                'id' => $e->id(),
                'scenario' => $e->scenarioName(),
                'environment' => (string) $e->environment(),
                'status' => $e->status()->value,
                'evidence_count' => $e->evidenceCount(),
            ], $list), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            break;

        case 'gate':
            $rate = (new EvaluateQualityGateUseCase($testPlans, $executions, new QualityGateEvaluator()))->execute($planId);
            http_response_code(200);
            echo json_encode(['plan_id' => $planId, 'pass_rate' => $rate, 'status' => 'APPROVED'], JSON_PRETTY_PRINT);
            break;

        default:
            http_response_code(200);
            echo json_encode([
                'service' => 'Micro-HIS QA / E2E / CI',
                'routes' => ['?route=executions&plan_id=...', '?route=gate&plan_id=...'],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
} catch (QualityGateNotMetException $e) {
    http_response_code(422);
    echo json_encode(['status' => 'REJECTED', 'reason' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (PersistenceException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'persistence', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['error' => 'bad_request', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
