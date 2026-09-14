# ADR-001: Arquitectura del quality gate

## Estado

Aceptado.

## Contexto

QA necesita un flujo transversal auditable en un Laravel multi-tenant. No es dueño de entidades clínicas y no debe introducir acoplamiento HTTP, Eloquent o SQL en el dominio.

## Decisión

Se adopta la separación `Presentation -> Application -> Domain -> Infrastructure`:

- **Domain**: `QualityGateRun`, reglas de estado y contrato `QualityGateRunRepository`.
- **Application**: caso de uso que crea una ejecución y evalúa controles.
- **Infrastructure**: Eloquent/PostgreSQL para persistencia y fake in-memory para tests.
- **Presentation**: comando Artisan, con salida humana y código de proceso.

El repositorio es orientado al caso de uso, no un CRUD genérico. Las transacciones abarcan solo la base local del quality gate.

## Propiedad y almacenamiento

ASII-25 es propietario de `qa_runs`, `qa_gate_results` y `qa_evidence`, almacenados en la conexión por defecto. PostgreSQL es el motor objetivo; SQLite en memoria queda como adaptador rápido de pruebas. No se agregan FK entre CENTRAL y HOSPITAL: cualquier tenant se representa como UUID lógico y se valida en la frontera que lo reciba.

## Desconexión parcial

Si la base local no está disponible, el comando falla con código no cero y no reporta éxito. Un fallo de un control se persiste si la transacción está disponible; si la persistencia falla, el pipeline conserva la salida del runner como evidencia externa y permanece bloqueado.

## Consecuencias

El flujo es testeable sin base mediante el fake, conserva trazabilidad y evita duplicar reglas clínicas. La integración PostgreSQL requiere variables de entorno y servicio efímero en CI; la prueba SQLite no sustituye esa comprobación, solo acelera el ciclo local.
