<?php

declare(strict_types=1);

namespace MicroHis\Tests\Application;

use MicroHis\Application\UseCase\AttachEvidenceUseCase;
use MicroHis\Application\UseCase\CreateTestPlanUseCase;
use MicroHis\Application\UseCase\EvaluateQualityGateUseCase;
use MicroHis\Application\UseCase\RecordExecutionResultUseCase;
use MicroHis\Application\UseCase\StartExecutionUseCase;
use MicroHis\Domain\Service\QualityGateEvaluator;
use MicroHis\Domain\ValueObject\ExecutionStatus;
use MicroHis\Tests\Support\InMemoryEvidenceRepository;
use MicroHis\Tests\Support\InMemoryTestExecutionRepository;
use MicroHis\Tests\Support\InMemoryTestPlanRepository;
use MicroHis\Tests\Support\TestCase;

/**
 * CAMINO FELIZ (happy path): flujo completo de QA/E2E desde la creación
 * del plan de pruebas hasta la aprobación del quality gate, usando dobles
 * en memoria (sin tocar la base de datos real) para aislar la orquestación
 * de la capa Application.
 */
final class HappyPathExecutionFlowTest extends TestCase
{
    public function testFlujoCompletoDePlanificacionEjecucionYQualityGate(): void
    {
        $testPlans = new InMemoryTestPlanRepository();
        $executions = new InMemoryTestExecutionRepository();
        $evidences = new InMemoryEvidenceRepository();

        $createPlan = new CreateTestPlanUseCase($testPlans);
        $startExecution = new StartExecutionUseCase($testPlans, $executions);
        $attachEvidence = new AttachEvidenceUseCase($executions, $evidences);
        $recordResult = new RecordExecutionResultUseCase($executions);
        $evaluateGate = new EvaluateQualityGateUseCase($testPlans, $executions, new QualityGateEvaluator());

        // 1. Crear plan de pruebas para la versión 2.4.0 con umbral de 80%.
        $plan = $createPlan->execute('PLAN-2.4.0', '2.4.0', 'Regresión de módulo QA/E2E', 0.80);
        $this->assertEquals('PLAN-2.4.0', $plan->id());

        // 2. Iniciar dos ejecuciones E2E planificadas.
        $exec1 = $startExecution->execute('EXEC-1', 'PLAN-2.4.0', 'Registro de plan de pruebas', 'QA');
        $exec2 = $startExecution->execute('EXEC-2', 'PLAN-2.4.0', 'Aplicación de quality gate', 'QA');
        $this->assertEquals(ExecutionStatus::RUNNING, $exec1->status());

        // 3. Conservar evidencia de cada ejecución (requisito de la consigna).
        $attachEvidence->execute('EVID-1', 'EXEC-1', 'REPORT', '/storage/evidencias/exec-1-reporte.json');
        $attachEvidence->execute('EVID-2', 'EXEC-2', 'LOG', '/storage/evidencias/exec-2.log');

        // 4. Registrar resultados: ambas ejecuciones aprobadas.
        $recordResult->execute('EXEC-1', ExecutionStatus::PASSED);
        $recordResult->execute('EXEC-2', ExecutionStatus::PASSED);

        // 5. Evaluar el quality gate: debe aprobar con 100% (>= 80% requerido).
        $passRate = $evaluateGate->execute('PLAN-2.4.0');

        $this->assertEquals(1.0, $passRate);
    }
}
