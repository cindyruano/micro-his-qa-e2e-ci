# ASII-25 — MVC y patrón Repositorio

## 1. Identificación

| Campo              | Valor                                                       |
|--------------------|-------------------------------------------------------------|
| Estudiante         | Cindy Maytté Ruano Calderón                                 |
| Módulo oficial     | ASII-25 — QA, pruebas E2E, CI y guía de despliegue final    |
| Actividad adaptada | Gestión de ejecuciones automatizadas y calidad de versiones |
| Rol / dominio      | QA / Aseguramiento de Calidad y CI/CD                       |
| Semana             | 4                                                           |
| Tema               | Arquitectura en capas y patrón repositorio                  |
| Tecnología         | PHP 8.2+ vanilla, MVC, PDO y SQLite                         |

## 2. Propósito y alcance

Este diseño continúa el Micro-HIS QA para planificar ejecuciones, ejecutar suites E2E, conservar evidencias y aplicar Quality Gates. La entrada HTTP se organiza con MVC sin framework y la persistencia se abstrae mediante `TestExecutionRepositoryInterface`.

La decisión central es que el controlador coordina la entrada y la salida, pero no conoce SQL, reglas de cobertura, transacciones ni estructuras concretas de almacenamiento.

## 3. Responsabilidades MVC

### Controller: `ExecutionController`

- Recibe la solicitud HTTP y el JSON de QA.
- Valida forma, tipos y presencia de campos de entrada.
- Construye el comando o DTO del caso de uso.
- Invoca `ExecuteTestSuiteUseCase` mediante inyección de dependencias.
- Traduce resultados y excepciones de aplicación a códigos HTTP.
- No contiene SQL, reglas de Quality Gate ni llamadas directas a PDO.

### Model / Domain

- `TestExecution`: entidad con `id`, `tenantId`, commit, métricas y estado.
- `QualityGatePolicy`: regla que aprueba o rechaza cobertura y fallos críticos.
- `CoveragePercentage`: objeto de valor que restringe el intervalo `0..100`.
- `TestExecutionRepositoryInterface`: contrato para guardar y consultar ejecuciones.

El modelo no depende de HTTP ni decide si el almacenamiento es SQLite, memoria, PostgreSQL o un repositorio compartido.

### View: `JsonResponseView`

- Serializa respuestas de éxito y error como JSON.
- Define encabezado `Content-Type: application/json`.
- Mantiene fuera del controlador los detalles de formato.
- No consulta repositorios ni aplica reglas de dominio.

## 4. Patrón Repositorio

El repositorio representa una colección de ejecuciones para la aplicación. La interfaz se ubica en el límite estable del dominio/aplicación y las implementaciones concretas se ubican en infraestructura.

```php
<?php
declare(strict_types=1);

interface TestExecutionRepositoryInterface
{
    public function save(TestExecution $execution): void;

    public function findById(string $tenantId, string $executionId): ?TestExecution;
}
```

### Adaptador real: `PdoTestExecutionRepository`

Usa SQLite mediante PDO, sentencias preparadas y filtros obligatorios por tenant. El SQL queda encapsulado en el adaptador.

```php
<?php
declare(strict_types=1);

final class PdoTestExecutionRepository implements TestExecutionRepositoryInterface
{
    public function __construct(private readonly PDO $connection)
    {
    }

    public function save(TestExecution $execution): void
    {
        $statement = $this->connection->prepare(
            <<<'SQL'
            INSERT INTO test_executions
                (id, tenant_id, commit_hash, coverage, critical_failures, status)
            VALUES
                (:id, :tenant_id, :commit_hash, :coverage, :critical_failures, :status)
            SQL
        );

        $statement->execute([
            ':id' => $execution->id(),
            ':tenant_id' => $execution->tenantId(),
            ':commit_hash' => $execution->commitHash(),
            ':coverage' => $execution->coverage()->value(),
            ':critical_failures' => $execution->criticalFailures(),
            ':status' => $execution->status(),
        ]);
    }

    public function findById(string $tenantId, string $executionId): ?TestExecution
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM test_executions
             WHERE tenant_id = :tenant_id AND id = :id'
        );
        $statement->execute([':tenant_id' => $tenantId, ':id' => $executionId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : TestExecution::fromPersistence($row);
    }
}
```

### Adaptador de pruebas: `InMemoryTestExecutionRepository`

Usa un arreglo indexado por tenant e identificador. Permite pruebas rápidas sin conexión, SQL ni estado externo.

```php
<?php
declare(strict_types=1);

final class InMemoryTestExecutionRepository implements TestExecutionRepositoryInterface
{
    /** @var array<string, TestExecution> */
    private array $items = [];

    public function save(TestExecution $execution): void
    {
        $key = $execution->tenantId() . ':' . $execution->id();
        $this->items[$key] = $execution;
    }

    public function findById(string $tenantId, string $executionId): ?TestExecution
    {
        return $this->items[$tenantId . ':' . $executionId] ?? null;
    }
}
```

Ambas implementaciones satisfacen el mismo contrato. El caso de uso no necesita cambiar cuando se sustituye memoria por SQLite.

## 5. Caso de uso y controlador limpio

```php
<?php
declare(strict_types=1);

final class ExecuteTestSuiteUseCase
{
    public function __construct(
        private readonly TestExecutionRepositoryInterface $executions,
        private readonly QualityGatePolicy $qualityGate,
        private readonly E2ERunnerInterface $runner,
    ) {
    }

    public function execute(ExecuteTestSuiteCommand $command): TestExecution
    {
        $result = $this->runner->run($command->plan());
        $this->qualityGate->assertPassed($result->coverage(), $result->criticalFailures());

        $execution = TestExecution::from($command->plan(), $result);
        $this->executions->save($execution);

        return $execution->approve();
    }
}

final class ExecutionController
{
    public function __construct(
        private readonly ExecuteTestSuiteUseCase $executeSuite,
        private readonly JsonResponseView $view,
    ) {
    }

    public function store(array $request): void
    {
        try {
            $command = ExecuteSuiteRequest::fromArray($request)->toCommand();
            $execution = $this->executeSuite->execute($command);

            $this->view->created(['id' => $execution->id(), 'status' => $execution->status()]);
        } catch (InvalidArgumentException $exception) {
            $this->view->unprocessable(['error' => $exception->getMessage()]);
        } catch (QualityGateFailedException $exception) {
            $this->view->unprocessable(['error' => $exception->getMessage()]);
        }
    }
}
```

El controlador contiene cero SQL, cero reglas de negocio y cero lógica de persistencia. La composición concreta se realiza en `bootstrap.php`:

```php
$repository = new PdoTestExecutionRepository($pdo);
$useCase = new ExecuteTestSuiteUseCase($repository, $qualityGate, $runner);
$controller = new ExecutionController($useCase, new JsonResponseView());
```

En pruebas, la única sustitución necesaria es:

```php
$repository = new InMemoryTestExecutionRepository();
```

## 6. Contrato SQLite mínimo

```sql
CREATE TABLE test_executions (
    id TEXT PRIMARY KEY,
    tenant_id TEXT NOT NULL,
    commit_hash TEXT NOT NULL,
    coverage REAL NOT NULL CHECK (coverage BETWEEN 0 AND 100),
    critical_failures INTEGER NOT NULL CHECK (critical_failures >= 0),
    status TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_test_executions_tenant
    ON test_executions (tenant_id, created_at);
```

El índice y el filtro compuesto por `tenant_id` e `id` reducen lecturas accidentales entre tenants y soportan consultas de auditoría.
