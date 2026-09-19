# ASII-25 — Refactorización antes/después

## 1. Identificación

**Estudiante:** Cindy Maytté Ruano Calderón  
**Módulo:** ASII-25 — QA, pruebas E2E, CI y guía de despliegue final  
**Actividad:** Gestión de ejecuciones automatizadas y calidad de versiones  
**Rol / dominio:** QA / Aseguramiento de Calidad y CI/CD  
**Semana:** 7

## 2. Punto de acoplamiento crítico

El punto de mayor acoplamiento es un controlador monolítico que recibe HTTP, ejecuta la regla del Quality Gate, consulta y modifica evidencias mediante SQL, calcula SHA-256 y construye la respuesta. Esa concentración impide probar las reglas sin una base real y hace que un cambio de almacenamiento altere la entrada HTTP.

## 3. Diseño antes: acoplado

```php
<?php
declare(strict_types=1);

final class QaController
{
    public function run(PDO $pdo): void
    {
        $payload = json_decode(file_get_contents('php://input'), true);
        $tenantId = $_SERVER['HTTP_X_TENANT_ID'];
        $failed = (int) ($payload['criticalFailures'] ?? 0);
        $coverage = (float) ($payload['coverage'] ?? 0);

        $result = $pdo->query(
            "SELECT id FROM test_executions
             WHERE tenant_id = '" . $tenantId . "' LIMIT 1"
        )->fetch(PDO::FETCH_ASSOC);

        if ($failed > 0 || $coverage < 80) {
            $status = 'failed';
        } else {
            $status = 'passed';
        }

        $hash = hash_file('sha256', $payload['evidencePath']);
        $pdo->exec(
            "UPDATE test_executions SET status = '" . $status . "',
             evidence_hash = '" . $hash . "' WHERE id = '" . $result['id'] . "'"
        );

        header('Content-Type: application/json');
        echo json_encode(['status' => $status, 'hash' => $hash]);
    }
}
```

### Problemas

- El controlador mezcla HTTP, validación, SQL, política de calidad, hashing y serialización.
- `PDO::query()` y `exec()` concatenan datos: existe riesgo de inyección SQL.
- No hay contrato de repositorio ni filtro seguro con parámetros.
- La prueba requiere una base, un archivo y variables HTTP reales.
- El empaquetado y hashing están dentro del camino de la petición.
- Cambiar SQLite por S3 o almacenamiento en memoria obliga a modificar el controlador.
- No se puede distinguir `gate_passed` de `evidence_ready`.

## 4. Diseño después: Clean Architecture y responsabilidades separadas

```text
QARunnerUI / EvidenceLogViewer
        -> ExecutionController + JsonResponseView
        -> EvaluateQualityGateUseCase
        -> QualityGatePolicy / EvidenceHashService
        -> RepositoryInterface / EvidenceStorageInterface
        -> PDO, InMemory, filesystem o S3
```

### Política de dominio

```php
<?php
declare(strict_types=1);

final class QualityGatePolicy
{
    public function __construct(private readonly float $minimumCoverage = 80.0)
    {
    }

    public function evaluate(QualityMetrics $metrics): QualityGateDecision
    {
        if ($metrics->criticalFailures > 0) {
            return QualityGateDecision::failed('critical_failures');
        }

        if ($metrics->coverage < $this->minimumCoverage) {
            return QualityGateDecision::failed('coverage');
        }

        return QualityGateDecision::passed();
    }
}
```

### Servicio de aplicación

```php
<?php
declare(strict_types=1);

final class EvaluateQualityGateUseCase
{
    public function __construct(
        private readonly TestExecutionRepositoryInterface $executions,
        private readonly QualityGateEvaluatorInterface $policy,
        private readonly EvidenceStorageManagerInterface $evidence,
    ) {
    }

    public function execute(EvaluateGateCommand $command): GateResult
    {
        $execution = $this->executions->findById(
            $command->tenantId,
            $command->runId
        );

        if ($execution === null) {
            throw new ExecutionNotFoundException();
        }

        $decision = $this->policy->evaluate($command->metrics);
        $execution->recordGateDecision($decision);
        $this->executions->save($execution);

        return GateResult::from($execution, $decision);
    }
}
```

### Controlador limpio

```php
<?php
declare(strict_types=1);

final class ExecutionController
{
    public function __construct(
        private readonly EvaluateQualityGateUseCase $evaluateGate,
        private readonly JsonResponseView $view,
    ) {
    }

    public function evaluate(array $request, string $tenantId): void
    {
        try {
            $command = EvaluateGateRequest::fromArray($request)
                ->toCommand($tenantId);
            $result = $this->evaluateGate->execute($command);
            $this->view->ok($result->toArray());
        } catch (InvalidArgumentException $exception) {
            $this->view->unprocessable(['error' => $exception->getMessage()]);
        } catch (ExecutionNotFoundException) {
            $this->view->notFound(['error' => 'Execution not found.']);
        }
    }
}
```

El controlador tiene cero SQL, cero `if` de negocio, cero hashing y cero conocimiento de filesystem. Solo transforma entrada, delega y formatea la salida.

## 5. Evidencia y persistencia desacopladas

```php
<?php
declare(strict_types=1);

interface EvidenceStorageManagerInterface
{
    public function registerPending(string $runId, string $tenantId): void;
    public function storeHash(string $runId, string $tenantId, string $sha256): void;
}

final class EvidenceHashService
{
    public function __construct(
        private readonly EvidenceStorageManagerInterface $storage,
    ) {
    }

    public function finalize(string $runId, string $tenantId, string $path): void
    {
        $hash = hash_file('sha256', $path);
        if ($hash === false) {
            throw new EvidenceIntegrityException();
        }

        $this->storage->storeHash($runId, $tenantId, $hash);
    }
}
```

La compresión puede ejecutarse en un worker. El caso de uso de Quality Gate no espera al archivo pesado, pero la evidencia no pasa a `ready` hasta que el hash fue calculado y persistido.

## 6. Comparativa

| Criterio            | Antes                                 | Después                                                 |
|---------------------|---------------------------------------|---------------------------------------------------------|
| Responsabilidad     | Controlador monolítico                | Controller, Use Case, Domain y adapters separados       |
| SQL                 | Concatenado en el controlador         | PDO preparado dentro del repositorio                    |
| Reglas de Gate      | Mezcladas con HTTP                    | `QualityGatePolicy` aislada y testeable                 |
| Evidencias          | Hash síncrono en la petición          | `EvidenceHashService` y worker desacoplado              |
| Testabilidad        | Requiere DB, archivo y HTTP           | Fakes/InMemory para casos unitarios                     |
| Mantenibilidad      | Cambiar storage obliga a tocar UI/API | Se cambia el adapter por inyección                      |
| Seguridad           | Riesgo de inyección y fuga de tenant  | DTO validado, parámetros y contexto de tenant           |
| Reproducibilidad CI | Dependencia del entorno local         | Contratos, imagen, configuración y dobles deterministas |

## 7. Estrategia de pruebas del refactor

- **Controller:** verifica que un payload válido invoque el caso de uso y que un payload inválido devuelva `422`.
- **QualityGatePolicy:** cubre cobertura mínima, fallos críticos y aprobación.
- **Use Case:** usa `InMemoryTestExecutionRepository` y un fake de política.
- **EvidenceHashService:** verifica hash correcto, archivo ausente y transición `pending -> ready`.
- **Repositorio PDO:** prueba SQL preparado, aislamiento por tenant y error de persistencia.
- **Contrato:** valida JSON Schema y respuestas HTTP sin ejecutar lógica clínica.

## 8. Justificación técnica

El refactor mejora mantenibilidad porque cada componente tiene una razón de cambio clara; mejora testabilidad porque el dominio se ejecuta sin infraestructura; y mejora el despliegue reproducible porque CI puede componer adaptadores conocidos y fijar sus dependencias. No amplía el alcance: solo reorganiza el flujo existente de ejecuciones, gates y evidencias.
