# ASII-25 — Diseño de componentes y contratos

## 1. Identificación

| Campo              | Valor                                                       |
|--------------------|-------------------------------------------------------------|
| Estudiante         | Cindy Maytté Ruano Calderón                                 |
| Módulo oficial     | ASII-25 — QA, pruebas E2E, CI y guía de despliegue final    |
| Actividad adaptada | Gestión de ejecuciones automatizadas y calidad de versiones |
| Rol / dominio      | QA / Aseguramiento de Calidad y CI/CD                       |
| Semana             | 7                                                           |
| Tema               | Diseño de componentes y refactorización                     |
| Tecnología         | PHP 8.2+, API REST y frontend QA desacoplado                |

## 2. Propósito y límite

El Micro-HIS QA organiza la planificación de pruebas, la ejecución E2E, la conservación de evidencias y la evaluación de Quality Gates. Esta semana define componentes, contratos y límites internos sin ampliar el módulo clínico ni introducir funcionalidades ajenas al proceso de calidad.

## 3. Componentes frontend

### `QARunnerUI`

- Captura commit, branch, tenant y suites seleccionadas.
- Envía un DTO `CreateRunRequest` a la API.
- Muestra estados `queued`, `running`, `passed` y `failed`.
- No evalúa cobertura ni decide el Quality Gate.
- No construye URLs de almacenamiento ni interpreta SQL.

### `EvidenceLogViewer`

- Consulta evidencias mediante `GET /api/v1/qa/runs/{id}/evidence`.
- Muestra estado `pending`, `ready` o `invalid`.
- Presenta nombre, tipo, tamaño y SHA-256 sin recalcular reglas de dominio.
- Descarga únicamente URLs autorizadas por el backend.
- No confía en un hash enviado por el navegador.

## 4. Componentes backend

| Componente                   | Responsabilidad                                                       | No debe hacer                              |
|------------------------------|-----------------------------------------------------------------------|--------------------------------------------|
| `ExecutionController`        | Validar forma de entrada, invocar el caso de uso y devolver JSON.     | SQL, reglas de Quality Gate o empaquetado. |
| `EvaluateQualityGateUseCase` | Coordinar métricas, política de dominio y persistencia del resultado. | Formatear HTML o conocer detalles de PDO.  |
| `QualityGatePolicy`          | Aprobar/rechazar cobertura, fallos críticos y p95.                    | Leer requests o escribir archivos.         |
| `EvidenceStorageManager`     | Registrar estado, guardar/consultar artefactos y verificar SHA-256.   | Decidir cobertura o permisos clínicos.     |
| `PdoTestExecutionRepository` | Persistir runs con SQL preparado y tenant filtrado.                   | Exponer SQL al controlador.                |

Flujo principal:

```text
QARunnerUI -> ExecutionController -> ExecuteTestSuiteUseCase
           -> QualityGatePolicy -> Repository / EvidenceStorageManager
           -> JsonResponse -> QARunnerUI

EvidenceLogViewer -> Evidence API -> EvidenceStorageManager -> artefacto autorizado
```

## 5. Contratos DTO de entrada y salida

```php
<?php
declare(strict_types=1);

final readonly class CreateRunRequest
{
    /** @param list<string> $suites */
    public function __construct(
        public string $commit,
        public string $tenantId,
        public array $suites,
        public ?string $branch = null,
    ) {
    }
}

final readonly class RunResponse
{
    public function __construct(
        public string $id,
        public string $status,
        public string $tenantId,
        public string $correlationId,
    ) {
    }
}

final readonly class EvidenceResponse
{
    public function __construct(
        public string $runId,
        public string $status,
        /** @var list<array{name:string,url:string,sha256:string,sizeBytes:int}> */
        public array $artifacts,
    ) {
    }
}
```

### JSON Schema: crear una ejecución

```json
{
  "$schema": "https://json-schema.org/draft/2020-12/schema",
  "$id": "https://his.example.test/schemas/create-run-request.json",
  "type": "object",
  "additionalProperties": false,
  "required": ["commit", "suites"],
  "properties": {
    "commit": { "type": "string", "minLength": 7, "maxLength": 64 },
    "branch": { "type": "string", "maxLength": 255 },
    "suites": { "type": "array", "minItems": 1, "items": { "type": "string" } }
  }
}
```

### JSON Schema: respuesta de evidencia

```json
{
  "$schema": "https://json-schema.org/draft/2020-12/schema",
  "$id": "https://his.example.test/schemas/evidence-response.json",
  "type": "object",
  "required": ["runId", "status", "artifacts"],
  "properties": {
    "runId": { "type": "string", "format": "uuid" },
    "status": { "enum": ["pending", "ready", "invalid"] },
    "artifacts": {
      "type": "array",
      "items": {
        "type": "object",
        "required": ["name", "url", "sha256", "sizeBytes"],
        "properties": {
          "name": { "type": "string" },
          "url": { "type": "string", "format": "uri" },
          "sha256": { "type": "string", "pattern": "^[a-f0-9]{64}$" },
          "sizeBytes": { "type": "integer", "minimum": 0 }
        }
      }
    }
  }
}
```

## 6. Contratos internos

```php
<?php
declare(strict_types=1);

interface QualityGateEvaluatorInterface
{
    public function evaluate(QualityMetrics $metrics): QualityGateDecision;
}

interface EvidenceStorageManagerInterface
{
    public function registerPending(string $runId, string $tenantId): void;
    public function get(string $runId, string $tenantId): EvidenceResponse;
}

interface TestExecutionRepositoryInterface
{
    public function save(TestExecution $execution): void;
    public function findById(string $tenantId, string $runId): ?TestExecution;
}
```

Los DTO transportan datos; las interfaces protegen las decisiones del dominio. Ningún DTO contiene la lógica para aprobar un Gate ni para autorizar un tenant.

## 7. Criterios de integración

- La UI recibe solo estados y datos serializados por el backend.
- La API valida JWT, RBAC y `X-Tenant-ID` antes del caso de uso.
- El repositorio filtra siempre por `tenant_id` y utiliza sentencias preparadas.
- El hash se calcula en backend sobre el archivo final.
- El cambio de almacenamiento no modifica `QARunnerUI`, `EvidenceLogViewer` ni `QualityGatePolicy`.

`Evidence Storage` es la responsabilidad técnica detrás de `EvidenceStorageManager`; se accede mediante su interfaz y no desde la UI.
