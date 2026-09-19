# ASII-25 — Refactorización SOLID y DIP

## 1. Objetivo del diseño

El flujo de QA necesita coordinar planificación, ejecución E2E, validación del tenant, conservación de evidencias y decisión del Quality Gate. El servicio de aplicación debe orquestar esas acciones sin conocer detalles de HTTP, base de datos, almacenamiento, GitHub Actions o GitLab CI.

El Principio de Inversión de Dependencias (DIP) establece que los módulos de alto nivel deben depender de abstracciones, y que los detalles concretos deben depender de esas abstracciones.

## 2. Diagnóstico inicial: diseño antes

El siguiente servicio concentra demasiadas responsabilidades: ejecuta HTTP, valida el tenant, escribe logs, calcula el Gate y actualiza GitHub directamente.

```php
<?php

final class TestRunnerRunnerService
{
    public function run(array $plan): bool
    {
        $client = new GuzzleHttp\\Client(['base_uri' => 'http://his.test']);
        $response = $client->post('/api/v1/e2e/run', [
            'headers' => ['X-Tenant-ID' => $plan['tenant_id']],
            'json' => ['suites' => $plan['suites']],
        ]);

        $results = json_decode($response->getBody()->getContents(), true);
        file_put_contents(
            storage_path('logs/qa/' . $plan['commit'] . '.json'),
            json_encode($results)
        );

        $gatePassed = $results['coverage'] >= 80
            && $results['critical_failures'] === 0;

        $github = new Github\\Api\\PullRequestClient($_ENV['GITHUB_TOKEN']);
        $github->setStatus($plan['pr_number'], $gatePassed ? 'success' : 'failure');

        return $gatePassed;
    }
}
```

### Problemas identificados

- **DIP:** la clase de alto nivel crea y conoce `GuzzleHttp`, el cliente de GitHub y el filesystem concreto.
- **Single Responsibility:** planifica, ejecuta, persiste, evalúa y notifica en un único método.
- **Open/Closed:** cambiar GitHub Actions por GitLab CI o Local Storage por S3 obliga a modificar el servicio.
- **Testabilidad:** las pruebas requieren HTTP, filesystem, token de GitHub y configuración real.
- **Riesgo de seguridad:** los detalles de autenticación y publicación de estados están mezclados con la lógica de negocio.

## 3. Aplicación de DIP y SOLID: diseño "Después"

Las interfaces representan políticas del dominio y puertos de salida del caso de uso.

```php
<?php

interface QualityGateEvaluatorInterface
{
    public function evaluate(TestRunResult $result): QualityGateDecision;
}

interface TestEvidenceStorageInterface
{
    public function store(TestRun $run, TestRunResult $result): EvidenceReceipt;
}

interface TenantContextValidatorInterface
{
    public function validate(TestPlan $plan): void;
}

interface E2ERunnerInterface
{
    public function execute(TestPlan $plan): TestRunResult;
}

interface PullRequestStatusInterface
{
    public function publish(TestPlan $plan, QualityGateDecision $decision): void;
}
```

El servicio de alto nivel depende únicamente de esas abstracciones:

```php
<?php

final class QualityExecutionService
{
    public function __construct(
        private TenantContextValidatorInterface $tenantValidator,
        private E2ERunnerInterface $runner,
        private TestEvidenceStorageInterface $evidenceStorage,
        private QualityGateEvaluatorInterface $gateEvaluator,
        private PullRequestStatusInterface $pullRequestStatus,
    ) {
    }

    public function execute(TestPlan $plan): QualityGateDecision
    {
        $this->tenantValidator->validate($plan);

        $run = TestRun::start($plan);
        $result = $this->runner->execute($plan);
        $this->evidenceStorage->store($run, $result);

        $decision = $this->gateEvaluator->evaluate($result);
        $this->pullRequestStatus->publish($plan, $decision);

        return $decision;
    }
}
```

## 4. Implementaciones concretas y composición Laravel

Las implementaciones concretas se conectan mediante el contenedor de servicios. El núcleo no necesita conocer si el pipeline corre en GitHub, GitLab o un entorno local.

```php
<?php

final class ConfiguredQualityGateEvaluator implements QualityGateEvaluatorInterface
{
    public function __construct(private array $thresholds)
    {
    }

    public function evaluate(TestRunResult $result): QualityGateDecision
    {
        $passed = $result->criticalFailures() === 0
            && $result->coverage() >= $this->thresholds['minimum_coverage']
            && $result->durationInSeconds() <= $this->thresholds['maximum_duration'];

        return $passed
            ? QualityGateDecision::passed()
            : QualityGateDecision::failed($result->failureReasons());
    }
}

final class S3TestEvidenceStorage implements TestEvidenceStorageInterface
{
    public function __construct(private FilesystemAdapter $disk)
    {
    }

    public function store(TestRun $run, TestRunResult $result): EvidenceReceipt
    {
        $path = 'qa-runs/' . $run->id() . '/results.json';
        $this->disk->put($path, $result->toJson());

        return EvidenceReceipt::fromPath($path);
    }
}
```

En Laravel, el `ServiceProvider` puede enlazar las interfaces a sus adaptadores:

```php
$this->app->bind(
    TestEvidenceStorageInterface::class,
    S3TestEvidenceStorage::class
);

$this->app->bind(
    PullRequestStatusInterface::class,
    GithubPullRequestStatus::class
);
```

Un entorno alternativo puede sustituir los enlaces por `LocalTestEvidenceStorage` y `GitlabPullRequestStatus` sin cambiar `QualityExecutionService`.

## 5. Testabilidad del diseño

Las pruebas unitarias pueden usar dobles de las interfaces y verificar la orquestación sin realizar solicitudes externas:

```php
public function test_blocks_pr_when_quality_gate_fails(): void
{
    $gate = Mockery::mock(QualityGateEvaluatorInterface::class);
    $gate->shouldReceive('evaluate')
        ->once()
        ->andReturn(QualityGateDecision::failed(['coverage']));

    $status = Mockery::mock(PullRequestStatusInterface::class);
    $status->shouldReceive('publish')->once();

    $service = new QualityExecutionService(
        Mockery::mock(TenantContextValidatorInterface::class),
        Mockery::mock(E2ERunnerInterface::class),
        Mockery::mock(TestEvidenceStorageInterface::class),
        $gate,
        $status,
    );

    $decision = $service->execute(TestPlan::fake());

    $this->assertTrue($decision->failed());
}
```

## 6. Justificación SOLID

- **DIP:** `QualityExecutionService` recibe puertos, no crea clientes concretos.
- **SRP:** cada componente tiene una razón principal de cambio: ejecutar, guardar, evaluar, validar o notificar.
- **OCP:** se agregan adaptadores para nuevos proveedores sin modificar el orquestador.
- **LSP:** cualquier implementación de una interfaz puede reemplazar a otra respetando el contrato.
- **ISP:** las interfaces son pequeñas y específicas; un adaptador no depende de métodos que no utiliza.

El resultado es un diseño más fácil de probar, auditar y evolucionar. La lógica de Quality Gate permanece estable aunque cambien el proveedor de CI, el almacenamiento o el mecanismo de validación del tenant.

## 7. Comparativa antes y después

| Métrica        | Antes                                                   | Después                                                           |
|----------------|---------------------------------------------------------|-------------------------------------------------------------------|
| Acoplamiento   | Alto: HTTP, GitHub, filesystem y reglas en una clase    | Bajo: dependencias recibidas mediante interfaces                  |
| Cohesión       | Baja: cinco responsabilidades mezcladas                 | Alta: orquestador y adaptadores con responsabilidades delimitadas |
| Testabilidad   | Requiere servicios externos y secretos de configuración | Permite mocks, fakes y pruebas deterministas                      |
| Mantenibilidad | Cambios de proveedor afectan el core                    | Los cambios quedan aislados en adaptadores                        |
| Seguridad      | Riesgo de mezclar tokens y evidencias en el flujo       | Puertos separados y contratos explícitos para datos sensibles     |
| Extensibilidad | Difícil incorporar GitLab CI o S3                       | Sustitución mediante nuevos bindings del contenedor               |

## 8. Resultado esperado

El diseño final separa la política de calidad del mecanismo de ejecución. El core de QA puede validar resultados y aplicar decisiones reproducibles, mientras Laravel resuelve las implementaciones concretas mediante inyección de dependencias.
