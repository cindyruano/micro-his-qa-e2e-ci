# ASII-25 — Implementación por capas y pruebas

## 1. Identificación

**Estudiante:** Cindy Maytté Ruano Calderón  
**Módulo:** ASII-25 — QA, pruebas E2E, CI y guía de despliegue final  
**Actividad:** Gestión de ejecuciones automatizadas y calidad de versiones  
**Tecnología:** PHP 8.2+ vanilla, PDO y pruebas nativas

## 2. Estructura del proyecto

```text
micro-his-qa/
├── public/
│   └── index.php
├── src/
│   ├── Presentation/
│   │   └── ExecutionController.php
│   ├── Application/
│   │   ├── ExecuteTestSuiteUseCase.php
│   │   └── EvaluateQualityGateUseCase.php
│   ├── Domain/
│   │   ├── Entity/TestExecution.php
│   │   ├── Policy/QualityGatePolicy.php
│   │   ├── ValueObjects/CoveragePercentage.php
│   │   └── Exception/QualityGateFailedException.php
│   └── Persistence/
│       ├── PdoTestExecutionRepository.php
│       └── PdoEvidenceStorage.php
├── tests/
│   ├── TestCase.php
│   ├── ExecuteTestSuiteTest.php
│   ├── QualityGatePolicyTest.php
│   └── PdoPersistenceFailureTest.php
├── bootstrap.php
└── composer.json
```

## 3. Dominio: entidades y regla de calidad

```php
<?php
declare(strict_types=1);

final class CoveragePercentage
{
    public function __construct(private readonly float $value)
    {
        if ($value < 0 || $value > 100) {
            throw new InvalidArgumentException('Coverage must be between 0 and 100.');
        }
    }
    public function value(): float
    {
        return $this->value;
    }
}

final class QualityGateFailedException extends DomainException
{
}

final class QualityGatePolicy
{
    public function __construct(private readonly float $minimumCoverage)
    {
    }

    public function assertPassed(
        CoveragePercentage $coverage,
        int $criticalFailures,
    ): void {
        if ($criticalFailures > 0 || $coverage->value() < $this->minimumCoverage) {
            throw new QualityGateFailedException(
                'Quality Gate rejected the execution metrics.'
            );
        }
    }
}
```

La excepción de dominio representa una decisión funcional, no un fallo de PDO ni un error HTTP.

## 4. Puertos de aplicación

```php
<?php
declare(strict_types=1);

interface TestExecutionRepositoryInterface
{
    public function save(TestExecution $execution): void;
}

interface EvidenceStorageInterface
{
    public function store(string $executionId, string $payload): string;
}

interface E2ERunnerInterface
{
    public function run(TestPlan $plan): TestRunResult;
}
```

Los casos de uso reciben estos puertos por inyección. No crean repositorios, PDO ni clientes externos.

## 5. Caso de uso de ejecución

```php
<?php
declare(strict_types=1);

final class ExecuteTestSuiteUseCase
{
    public function __construct(
        private E2ERunnerInterface $runner,
        private TestExecutionRepositoryInterface $executions,
        private EvidenceStorageInterface $evidence,
        private QualityGatePolicy $qualityGate,
    ) {
    }

    public function execute(TestPlan $plan): TestExecution
    {
        $result = $this->runner->run($plan);
        $execution = TestExecution::from($plan, $result);

        $this->qualityGate->assertPassed(
            $result->coverage(),
            $result->criticalFailures()
        );
        $this->executions->save($execution);
        $this->evidence->store($execution->id(), $result->toJson());

        return $execution->approve();
    }
}
```

En producción puede envolverse la persistencia y el almacenamiento en una transacción o en un mecanismo de compensación: si no se guarda la evidencia, la ejecución no debe publicarse como aprobada.

## 6. Persistencia PDO con sentencias preparadas

```php
<?php
declare(strict_types=1);

final class PdoTestExecutionRepository implements TestExecutionRepositoryInterface
{
    public function __construct(private PDO $connection)
    {
    }

    public function save(TestExecution $execution): void
    {
        $sql = <<<'SQL'
            INSERT INTO test_executions
                (id, tenant_id, commit_hash, coverage, critical_failures, status)
            VALUES
                (:id, :tenant_id, :commit_hash, :coverage, :critical_failures, :status)
        SQL;

        try {
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                ':id' => $execution->id(),
                ':tenant_id' => $execution->tenantId(),
                ':commit_hash' => $execution->commitHash(),
                ':coverage' => $execution->coverage()->value(),
                ':critical_failures' => $execution->criticalFailures(),
                ':status' => $execution->status(),
            ]);
        } catch (PDOException $exception) {
            throw new PersistenceException(
                'Unable to persist test execution.',
                previous: $exception
            );
        }
    }
}
```

El `tenant_id` siempre se envía como parámetro. No se concatena entrada del usuario en SQL y la conexión debe configurarse con `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`.

## 7. Controlador de entrada

```php
<?php
declare(strict_types=1);

final class ExecutionController
{
    public function __construct(private ExecuteTestSuiteUseCase $executeSuite)
    {
    }

    public function run(array $request): void
    {
        try {
            $execution = $this->executeSuite->execute(
                TestPlan::fromRequest($request)
            );

            http_response_code(201);
            echo json_encode(['id' => $execution->id(), 'status' => 'approved']);
        } catch (QualityGateFailedException $exception) {
            http_response_code(422);
            echo json_encode(['error' => $exception->getMessage()]);
        } catch (PersistenceException $exception) {
            http_response_code(503);
            echo json_encode(['error' => 'Quality execution could not be stored.']);
        }
    }
}
```

El controlador no expone mensajes internos de PDO ni credenciales; solo traduce resultados a contratos HTTP.

## 8. Estrategia de pruebas

Los siguientes ejemplos usan PHPUnit como runner opcional de pruebas; las clases de producción permanecen vanilla PHP. Si se requiere ejecución sin dependencias, los mismos dobles pueden invocarse desde un script nativo con `assert()`.

### 8.1 Camino feliz: ejecución y aprobación

```php
public function test_approves_execution_and_stores_evidence(): void
{
    $runner = new FakeE2ERunner(
        coverage: new CoveragePercentage(92),
        criticalFailures: 0
    );
    $repository = new InMemoryTestExecutionRepository();
    $evidence = new InMemoryEvidenceStorage();

    $useCase = new ExecuteTestSuiteUseCase(
        $runner,
        $repository,
        $evidence,
        new QualityGatePolicy(minimumCoverage: 80)
    );

    $execution = $useCase->execute(TestPlan::fake());

    self::assertSame('approved', $execution->status());
    self::assertCount(1, $repository->all());
    self::assertTrue($evidence->has($execution->id()));
}
```

### 8.2 Regla de dominio: cobertura insuficiente

```php
public function test_rejects_execution_when_coverage_is_below_threshold(): void
{
    $policy = new QualityGatePolicy(minimumCoverage: 80);

    $this->expectException(QualityGateFailedException::class);

    $policy->assertPassed(
        new CoveragePercentage(64),
        criticalFailures: 0
    );
}
```

La prueba demuestra que el rechazo depende de una regla pura y no de una base de datos o un servicio CI.

### 8.3 Error de persistencia: base de datos caída

```php
public function test_translates_pdo_failure_to_persistence_error(): void
{
    $pdo = new FailingPdoConnection(
        new PDOException('database is unavailable')
    );
    $repository = new PdoTestExecutionRepository($pdo);

    $this->expectException(PersistenceException::class);

    $repository->save(TestExecution::fakeApproved());
}
```

`FailingPdoConnection` es un doble de prueba que hace que `prepare()` lance `PDOException`. En una implementación concreta puede sustituirse por una base SQLite temporal o por una subclase controlada de `PDO`; el objetivo es verificar que el adaptador no filtre la excepción de infraestructura y que el caso de uso nunca marque la ejecución como aprobada si falla el almacenamiento.

## 9. Cobertura y evidencia esperada

| Escenario                        | Capa principal            | Evidencia                                                             |
|----------------------------------|---------------------------|-----------------------------------------------------------------------|
| Camino feliz                     | Application + Persistence | Ejecución aprobada, fila guardada y artefacto asociado                |
| Cobertura/métricas insuficientes | Domain                    | `QualityGateFailedException` y estado no aprobado                     |
| Base de datos caída              | Persistence + Application | `PersistenceException`, respuesta controlada y ausencia de aprobación |

Estas tres pruebas cubren el contrato mínimo de la Semana 3: integración entre capas, regla de dominio aislada y traducción segura de errores de persistencia.
