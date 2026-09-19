# ASII-25 — Matriz decisión a evidencia

## Identificación

**Estudiante:** Cindy Maytté Ruano Calderón  
**Módulo:** ASII-25 — QA, pruebas E2E, CI y guía de despliegue final  
**Actividad:** Gestión de ejecuciones automatizadas y calidad de versiones  
**Evaluación:** Primera evaluación parcial

## Matriz de trazabilidad

| ID Requerimiento        | Decisión de Arquitectura                                        | Principio SOLID / Patrón        | Ubicación en Código / Test                               | Evidencia de Validación                       |
|-------------------------|-----------------------------------------------------------------|---------------------------------|----------------------------------------------------------|-----------------------------------------------|
| RF-01 Ejecución E2E     | Separar el motor de tests del controlador HTTP                  | SRP / Clean Architecture        | `ExecutionController.php`, `ExecuteTestSuiteUseCase.php` | Test unitario del Controller y caso de uso    |
| RF-02 Quality Gate      | Aislar aprobación/rechazo en una política de dominio            | OCP / Strategy                  | `QualityGatePolicy.php`                                  | Tests de umbral de cobertura, pass rate y p95 |
| RF-03 Evidencias        | Usar un worker para comprimir artefactos fuera de la petición   | SRP / Producer-Consumer         | `EvidencePackagingJob.php`                               | Prueba de estado `pending` a `ready`          |
| RF-04 Auditoría         | Registrar run, commit, tenant, actor y hash                     | Repository / Audit Log          | `TestExecutionRepositoryInterface`                       | Consulta de auditoría por `run_id`            |
| RF-05 PR                | Traducir el resultado del Gate a un check obligatorio           | Adapter / DIP                   | `PullRequestStatusAdapter.php`                           | Check de CI bloquea un PR fallido             |
| RF-06 Versiones         | Identificar la ejecución por commit e idempotency key           | Repository / Idempotency        | `ExecutionIdempotencyStore.php`                          | Reintento no duplica la ejecución             |
| RNF-01 Multi-tenancy    | Inyectar `X-Tenant-ID` en cada consulta y job                   | DIP / Repository                | `PdoTestExecutionRepository.php`                         | Prueba con tenant ajeno sin datos             |
| RNF-02 p95 < 2 s        | Mantener evaluación síncrona y diferir compresión               | CQRS ligero / Async Job         | `EvaluateQualityGateUseCase.php`                         | Prueba de latencia p95 bajo carga controlada  |
| RNF-03 Integridad       | Calcular SHA-256 del archivo final y persistir metadatos        | Value Object / Integrity Port   | `EvidenceHash.php`, `EvidenceVerifier.php`               | Hash calculado igual al archivo descargado    |
| RNF-04 Seguridad        | JWT, RBAC, secretos en entorno y logs redactados                | Least Privilege / Adapter       | Middleware API y logger                                  | Escaneo de secretos y respuestas `401/403`    |
| RNF-05 Concurrencia     | `run_id` único, claves compuestas y estados atómicos            | Repository / Optimistic Control | SQLite/PDO schema y repository                           | Dos runs concurrentes sin colisión            |
| RNF-06 Reproducibilidad | Imagen fijada, lockfile, migraciones y configuración versionada | Infrastructure as Code          | Dockerfile, lockfile y CI workflow                       | Reejecución desde el mismo commit             |

## Conexión entre capas

```text
Actor -> API REST -> Controller -> Use Case -> Domain Policy
                                      |              |
                                      v              v
                              Repository       Quality Gate
                                      |
                         PDO / InMemory / Evidence Worker
```

## Criterios de evidencia defendible

1. Cada fila debe apuntar a un artefacto local, un test o un check de CI.
2. La evidencia debe incluir commit, tenant, `run_id` y `X-Correlation-ID`.
3. Un resultado `passed` requiere cero fallos críticos y métricas dentro de umbral.
4. La compresión no forma parte del camino crítico de evaluación.
5. La prueba de concurrencia debe ejecutarse con al menos dos runs del mismo tenant y un run de tenant distinto.
6. Los artefactos se verifican por SHA-256 antes de marcar `ready`.

## Mapa de decisiones

| Decisión                           | Motivo                   | Riesgo controlado           |
|------------------------------------|--------------------------|-----------------------------|
| Evaluación antes del empaquetado   | Cumplir p95 < 2 s        | Bloqueo por I/O pesado      |
| Cola para evidencias               | Aislar CPU y disco       | Saturación del API          |
| Datos propios de QA                | Propiedad clara          | Acoplamiento al HIS clínico |
| Filtro obligatorio por tenant      | Seguridad multitenant    | Fuga cross-tenant           |
| Hash del resultado final           | Integridad verificable   | Alteración de evidencia     |
| Métricas antes de extraer servicio | Decisión basada en datos | Sobreingeniería             |
