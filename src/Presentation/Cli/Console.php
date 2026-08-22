<?php

declare(strict_types=1);

namespace MicroHis\Presentation\Cli;

use MicroHis\Application\UseCase\AttachEvidenceUseCase;
use MicroHis\Application\UseCase\CreateTestPlanUseCase;
use MicroHis\Application\UseCase\EvaluateQualityGateUseCase;
use MicroHis\Application\UseCase\ListExecutionsUseCase;
use MicroHis\Application\UseCase\RecordExecutionResultUseCase;
use MicroHis\Application\UseCase\StartExecutionUseCase;
use MicroHis\Domain\Exception\EvidenceRequiredException;
use MicroHis\Domain\Exception\PersistenceException;
use MicroHis\Domain\Exception\QualityGateNotMetException;
use MicroHis\Domain\ValueObject\ExecutionStatus;
use Throwable;

/**
 * Presentación por línea de comandos (vanilla PHP, sin ningún framework CLI).
 * Traduce argumentos de consola a llamadas de los casos de uso de Application
 * y formatea la salida para el operador de QA/CI.
 */
final class Console
{
    public function __construct(
        private CreateTestPlanUseCase $createTestPlan,
        private StartExecutionUseCase $startExecution,
        private AttachEvidenceUseCase $attachEvidence,
        private RecordExecutionResultUseCase $recordResult,
        private EvaluateQualityGateUseCase $evaluateGate,
        private ListExecutionsUseCase $listExecutions
    ) {
    }

    /** @param string[] $argv */
    public function run(array $argv): int
    {
        $command = $argv[1] ?? 'help';

        try {
            return match ($command) {
                'plan:create' => $this->handlePlanCreate($argv),
                'execution:start' => $this->handleExecutionStart($argv),
                'evidence:attach' => $this->handleEvidenceAttach($argv),
                'execution:finish' => $this->handleExecutionFinish($argv),
                'gate:evaluate' => $this->handleGateEvaluate($argv),
                'execution:list' => $this->handleExecutionList($argv),
                default => $this->printHelp(),
            };
        } catch (EvidenceRequiredException|QualityGateNotMetException $e) {
            fwrite(STDERR, "[REGLA DE DOMINIO] {$e->getMessage()}\n");
            return 1;
        } catch (PersistenceException $e) {
            fwrite(STDERR, "[ERROR DE PERSISTENCIA] {$e->getMessage()}\n");
            return 2;
        } catch (Throwable $e) {
            fwrite(STDERR, "[ERROR] {$e->getMessage()}\n");
            return 3;
        }
    }

    private function handlePlanCreate(array $argv): int
    {
        [$id, $version, $description, $threshold] = [
            $argv[2] ?? '', $argv[3] ?? '', $argv[4] ?? '', isset($argv[5]) ? (float) $argv[5] : 0.90,
        ];
        $plan = $this->createTestPlan->execute($id, $version, $description, $threshold);
        echo "Plan creado: {$plan->id()} (release {$plan->releaseVersion()}, umbral " . ($plan->qualityGateThreshold() * 100) . "%)\n";
        return 0;
    }

    private function handleExecutionStart(array $argv): int
    {
        [$id, $planId, $scenario, $env] = [$argv[2] ?? '', $argv[3] ?? '', $argv[4] ?? '', $argv[5] ?? 'QA'];
        $execution = $this->startExecution->execute($id, $planId, $scenario, $env);
        echo "Ejecución iniciada: {$execution->id()} [{$execution->status()->value}]\n";
        return 0;
    }

    private function handleEvidenceAttach(array $argv): int
    {
        [$evidenceId, $executionId, $type, $reference] = [$argv[2] ?? '', $argv[3] ?? '', $argv[4] ?? '', $argv[5] ?? ''];
        $evidence = $this->attachEvidence->execute($evidenceId, $executionId, $type, $reference);
        echo "Evidencia registrada: {$evidence->id()} ({$evidence->type()}) -> {$evidence->reference()}\n";
        return 0;
    }

    private function handleExecutionFinish(array $argv): int
    {
        [$executionId, $result] = [$argv[2] ?? '', $argv[3] ?? ''];
        $execution = $this->recordResult->execute($executionId, ExecutionStatus::from(strtoupper($result)));
        echo "Ejecución finalizada: {$execution->id()} [{$execution->status()->value}]\n";
        return 0;
    }

    private function handleGateEvaluate(array $argv): int
    {
        $planId = $argv[2] ?? '';
        $passRate = $this->evaluateGate->execute($planId);
        echo "Quality gate APROBADO para plan {$planId}: " . number_format($passRate * 100, 2) . "% de aprobación.\n";
        return 0;
    }

    private function handleExecutionList(array $argv): int
    {
        $planId = $argv[2] ?? '';
        $executions = $this->listExecutions->execute($planId);
        foreach ($executions as $execution) {
            echo "- {$execution->id()} | {$execution->scenarioName()} | {$execution->environment()} | {$execution->status()->value} | evidencias: {$execution->evidenceCount()}\n";
        }
        return 0;
    }

    private function printHelp(): int
    {
        echo <<<HELP
        Micro-HIS QA / E2E / CI - Consola (Presentation layer)

        Comandos disponibles:
          plan:create      <id> <version> <descripcion> [umbral=0.90]
          execution:start  <execId> <planId> <escenario> [ambiente=QA]
          evidence:attach  <evidId> <execId> <tipo> <referencia>
          execution:finish <execId> <PASSED|FAILED>
          gate:evaluate    <planId>
          execution:list   <planId>

        HELP;
        return 0;
    }
}
