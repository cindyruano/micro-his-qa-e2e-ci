# ASII-25 — Defensa de arquitectura

## Datos de la evaluación

| Campo            | Valor                                                                        |
|------------------|------------------------------------------------------------------------------|
| Estudiante       | Cindy Maytté Ruano Calderón                                                  |
| Módulo           | ASII-25 — QA, pruebas E2E, CI y guía de despliegue final                     |
| Actividad        | Gestión de ejecuciones automatizadas y calidad de versiones                  |
| Rol / dominio    | QA / Aseguramiento de Calidad y CI/CD                                        |
| Evaluación       | Primera evaluación parcial                                                   |
| Proyecto         | Micro-HIS QA                                                                 |
| Cambio defendido | Ejecuciones E2E concurrentes por tenant y evidencias comprimidas con SHA-256 |

## Diapositiva 1 — Portada

**Micro-HIS QA: arquitectura para ejecuciones E2E y Quality Gates**

- Estudiante: Cindy Maytté Ruano Calderón.
- Módulo ASII-25.
- Flujo: planificación, ejecución, evidencia y decisión de calidad.
- Primera evaluación parcial.

**Notas del orador:** Esta defensa conecta las decisiones de requisitos, capas, SOLID, Repository y API REST. El criterio de éxito es que una versión sea reproducible, trazable y segura por tenant.

## Diapositiva 2 — Problema y actores

**Problema:** el HIS debe validar versiones sin cruzar tenants, perder evidencias ni aceptar cambios con Quality Gates fallidos.

| Actor                         | Responsabilidad                                          |
|-------------------------------|----------------------------------------------------------|
| Developer / Contribuidor      | Abre PR y corrige hallazgos.                             |
| QA Engineer / QA Lead         | Define umbrales, revisa evidencias y decide excepciones. |
| CI / E2E Runner               | Ejecuta suites aisladas y publica resultados.            |
| HIS multi-tenant              | Valida JWT, RBAC y `X-Tenant-ID`.                        |
| Repository / Evidence Storage | Conserva runs, auditoría y artefactos con hash.          |

**Notas del orador:** La arquitectura asigna una responsabilidad verificable a cada actor. El tenant se obtiene del contexto autorizado, no de un dato confiado del payload.

## Diapositiva 3 — RF y RNF clave

**Requisitos funcionales:**

- `RF-01`: planificar y registrar una ejecución.
- `RF-02`: disparar suites E2E.
- `RF-03`: evaluar cobertura, fallos y duración.
- `RF-04`: conservar logs y artefactos.
- `RF-05`: bloquear o aprobar el PR.
- `RF-06`: auditar commit, tenant, actor y resultado.

**Requisitos no funcionales:**

- `RNF-01`: evaluación API con p95 menor que 2 segundos.
- `RNF-02`: aislamiento estricto por tenant bajo concurrencia.
- `RNF-03`: evidencias íntegras, firmadas por SHA-256 y sin secretos.
- `RNF-04`: diseño mantenible, reproducible y observable.

**Notas del orador:** El cambio práctico no altera los objetivos; obliga a demostrar que el empaquetado pesado no bloquea el Quality Gate.

## Diapositiva 4 — Capas y SOLID

```text
HTTP / Controller -> Application Use Cases -> Domain Policies
                                      -> RepositoryInterface
                                      -> PDO / InMemory / Evidence adapters
```

- **Presentation:** `ExecutionController` y `JsonResponseView`; SRP, sin SQL ni reglas.
- **Application:** orquesta runner, persistencia asíncrona y Quality Gate.
- **Domain:** `TestExecution`, `QualityGatePolicy` y objetos de valor.
- **Persistence:** `PdoTestExecutionRepository`, almacenamiento y auditoría.
- **DIP / Repository:** el caso de uso depende de interfaces, no de PDO o filesystem.

**Notas del orador:** La inversión de dependencias permite probar con memoria y ejecutar integración con SQLite sin modificar el núcleo.

## Diapositiva 5 — API REST y persistencia

- `POST /api/v1/qa/runs`: registra y dispara una ejecución.
- `POST /api/v1/qa/gates/evaluate`: decide el Quality Gate.
- `GET /api/v1/qa/runs/{id}/evidence`: consulta evidencias.
- Cabeceras: `Authorization`, `X-Tenant-ID`, `Content-Type` y `X-Correlation-ID`.
- Respuestas: `200`, `201`, `400`, `401`, `403`, `404` y `422`.
- `TestExecutionRepositoryInterface` permite `PdoTestExecutionRepository` o `InMemoryTestExecutionRepository`.

**Notas del orador:** REST es síncrono para comandos y decisiones pequeñas. Suites largas y archivos comprimidos pasan a trabajos asíncronos con estado `queued` y referencia trazable.

## Diapositiva 6 — Cambio práctico: concurrencia y evidencias

**Requisito:** soportar ejecuciones simultáneas por tenant y generar `.zip` o `.tar.gz` con hash SHA-256 sin afectar p95 < 2 s.

Decisiones:

1. Identificador único por ejecución y clave de idempotencia.
2. Cola de empaquetado independiente del request de evaluación.
3. `evidence_hash`, tamaño, formato y estado en persistencia.
4. Hash calculado sobre el archivo final y almacenado junto a la referencia.
5. Quality Gate evalúa métricas existentes; no espera la compresión.
6. Límites de CPU, disco y retención para evitar saturación.

**Notas del orador:** La API responde rápido con un estado de evidencia `pending`. Un worker publica `ready` solo después de cerrar el archivo y verificar su hash.

## Diapositiva 7 — CI/CD y despliegue reproducible

- Issue y rama `feature/asii-25-...` asociados al cambio.
- Worktree aislado para reproducir la tarea.
- PR con validación OpenAPI, PHP, unitarias, integración y tenant isolation.
- Imagen Docker fijada por versión de PHP y dependencias.
- Variables de entorno separadas de secretos y artefactos.
- Quality Gate bloquea merge ante fallo crítico, contrato inválido o evidencia inconsistente.
- Migraciones versionadas y rollback documentado.

**Notas del orador:** Reproducibilidad significa que el mismo commit, imagen y configuración producen la misma decisión, sin depender de cambios manuales en el entorno.

## Diapositiva 8 — Conclusión y métricas

| Métrica                    |                   Objetivo | Evidencia                                |
|----------------------------|---------------------------:|------------------------------------------|
| Latencia p95 de evaluación |                    `< 2 s` | Métrica API y prueba de carga controlada |
| Fallos críticos            |           `0` para aprobar | Reporte E2E y Quality Gate               |
| Aislamiento cross-tenant   |                `0` accesos | Prueba con `X-Tenant-ID` ajeno           |
| Integridad de artefactos   |         `100%` con SHA-256 | Hash persistido y verificado             |
| Reproducibilidad           | Mismo commit/configuración | Imagen, lockfile y logs                  |
| Auditoría                  |          Cada run trazable | `run_id`, commit, tenant, correlation ID |

**Cierre oral:** La arquitectura no agrega complejidad por apariencia. Introduce una cola solo donde el trabajo pesado lo exige, mantiene el Quality Gate rápido y conserva contratos, límites y evidencia verificable.

## Preguntas previsibles del jurado

- **¿Por qué no comprimir durante la petición?** Porque el empaquetado compite con el límite p95 de la API.
- **¿Cómo evita colisiones?** `run_id` único, idempotency key y clave compuesta por tenant.
- **¿Qué ocurre si falla el hash?** La evidencia queda inválida y el run no se marca como completo.
- **¿Por qué no usar microservicios desde el inicio?** Se necesita carga real para justificar coste operativo y escalado separado.
