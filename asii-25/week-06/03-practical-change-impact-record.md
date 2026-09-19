# ASII-25 — Registro de impacto del cambio práctico

## 1. Identificación y cambio

| Campo       | Valor                                                                        |
|-------------|------------------------------------------------------------------------------|
| Estudiante  | Cindy Maytté Ruano Calderón                                                  |
| Módulo      | ASII-25 — QA, pruebas E2E, CI y guía de despliegue final                     |
| Cambio      | Ejecuciones E2E concurrentes por tenant con evidencias comprimidas y SHA-256 |
| Restricción | Mantener p95 de `/qa/gates/evaluate` por debajo de 2 segundos                |
| Estado      | Diseñado para defensa parcial                                                |

## 2. Descripción

El HIS debe soportar múltiples ejecuciones E2E simultáneas por tenant. Cada ejecución genera logs, reportes, capturas y vídeos que deben empaquetarse como `.zip` o `.tar.gz`, calcularse con SHA-256 y conservarse sin mezclar datos entre tenants. La evaluación del Quality Gate no puede esperar a que termine el empaquetado.

## 3. Impacto por capa

### Presentation / API

- `POST /api/v1/qa/runs` valida `Idempotency-Key`, devuelve `201` y estado `queued` o `running`.
- `POST /api/v1/qa/gates/evaluate` usa únicamente resultados disponibles y conserva su camino síncrono.
- `GET /api/v1/qa/runs/{id}/evidence` informa `pending`, `ready` o `invalid`.
- Se propaga `X-Correlation-ID` para relacionar request, worker y artefactos.

### Application

- Se separa `ExecuteTestSuiteUseCase` de `PackageEvidenceJob`.
- El caso de uso registra el run y publica un mensaje idempotente para el empaquetador.
- Un worker procesa el artefacto con límites de CPU, memoria, disco y tiempo.
- El estado cambia atómicamente: `pending -> ready` o `pending -> invalid`.

### Domain / Quality Gate

- La política evalúa cobertura, fallos críticos, pass rate y latencia p95.
- La compresión es evidencia posterior, no criterio que bloquee la decisión de calidad.
- Un hash ausente o inválido impide publicar la evidencia como `ready`.
- La política distingue `gate_passed` de `evidence_ready` para no confundir calidad de código con disponibilidad del archivo.

### Persistence

Migración conceptual:

```sql
ALTER TABLE test_executions ADD COLUMN evidence_status TEXT NOT NULL DEFAULT 'pending';
ALTER TABLE test_executions ADD COLUMN evidence_hash CHAR(64);
ALTER TABLE test_executions ADD COLUMN evidence_format TEXT;
ALTER TABLE test_executions ADD COLUMN evidence_size_bytes INTEGER;
ALTER TABLE test_executions ADD COLUMN idempotency_key TEXT;

CREATE UNIQUE INDEX uq_run_tenant_idempotency
    ON test_executions (tenant_id, idempotency_key);
```

La escritura usa transacciones PDO y `WHERE tenant_id = :tenant_id`. La clave única evita duplicar un run cuando el cliente reintenta.

## 4. Impacto en concurrencia y aislamiento

- Cada run recibe un UUID independiente.
- Un `tenant_id` nunca se deriva del nombre de archivo ni de un dato no autenticado.
- La ruta de almacenamiento es `tenant/{tenantId}/runs/{runId}/evidence.tar.gz`.
- Los locks o estados atómicos evitan que dos workers empaqueten el mismo run.
- Los límites por tenant evitan que un cliente consuma todo el pool de workers.
- El repositorio compartido aplica restricciones de unicidad y transacciones.

## 5. Impacto en CI/CD y Quality Gates

Pipeline propuesto:

```text
PR -> ejecutar E2E concurrentes -> evaluar Gate (< 2 s p95)
   -> publicar decisión -> encolar evidencias -> comprimir y hash
   -> verificar SHA-256 -> publicar artefacto -> actualizar auditoría
```

Checks adicionales:

- Prueba de dos o más runs simultáneos del mismo tenant.
- Prueba de aislamiento con tenant diferente.
- Prueba de reintento usando la misma `Idempotency-Key`.
- Prueba de hash correcto, archivo truncado y artefacto alterado.
- Medición de p95 de evaluación sin esperar al worker.
- Revisión de retención y limpieza de artefactos huérfanos.

Un PR se bloquea si falla el Quality Gate, se detecta acceso cross-tenant, se duplica una ejecución o el hash no coincide. La evidencia `pending` no bloquea por sí sola una evaluación que ya cumple sus métricas, pero impide cerrar la auditoría como completa.

## 6. Versiones y despliegue reproducible

- El esquema se modifica mediante migración versionada.
- El worker se empaqueta con la misma imagen y lockfile del commit evaluado.
- Algoritmo, formato, compresor y versión se registran como metadatos.
- Variables de entorno contienen DSN, claves y límites; nunca se versionan secretos.
- El despliegue debe poder reconstruirse desde commit, imagen, migraciones y configuración documentada.
- El rollback conserva evidencias históricas y evita borrar registros de auditoría.

## 7. Riesgos y mitigaciones

| Riesgo                                 | Mitigación                                                     |
|----------------------------------------|----------------------------------------------------------------|
| La compresión consume CPU y afecta API | Worker separado, límites y medición de p95.                    |
| Dos workers procesan el mismo run      | Claim atómico y estado transaccional.                          |
| Archivo alterado                       | SHA-256 del archivo final y verificación antes de `ready`.     |
| Fuga entre tenants                     | Contexto autenticado, rutas aisladas y filtros SQL preparados. |
| Reintentos duplicados                  | `Idempotency-Key` y restricción única por tenant.              |
| Cola saturada                          | Backpressure, cuotas por tenant, retención y alertas.          |
| Infraestructura excesiva               | Extraer componentes solo después de medir carga real.          |

## 8. Criterios de aceptación del cambio

- Dos ejecuciones concurrentes del mismo tenant terminan con evidencias independientes.
- Un tenant no puede consultar ni descargar evidencia ajena.
- `/qa/gates/evaluate` mantiene p95 `< 2 s` bajo la carga objetivo.
- Cada archivo `ready` tiene SHA-256 verificable y metadatos completos.
- Un reintento no crea una segunda ejecución para la misma clave idempotente.
- Fallos del worker quedan auditados como `invalid` o `failed`, nunca como evidencia válida.
