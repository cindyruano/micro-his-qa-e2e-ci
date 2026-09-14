# Arquitectura - Week 04

## Continuidad con Week 03

El módulo conserva `QualityGateRun`, `TestPlan`, la ejecución E2E por puerto y la persistencia de `qa_runs`, `qa_gate_results` y `qa_evidence`. La diferencia es estructural: la entrada pasa por MVC y el acceso a datos se concentra en un repositorio sustituible.

## Capas y responsabilidades

| Capa         | Objetos                                                     | Responsabilidad                                   | Dependencias permitidas   |
|--------------|-------------------------------------------------------------|---------------------------------------------------|---------------------------|
| Presentation | `QualityGateRequest`, `QualityGateController`, `JsonView`   | Traducir HTTP a comandos y comandos a respuesta.  | Application y Domain DTOs |
| Application  | `RunQualityGate`, `GetLatestQualityGate`                    | Coordinar flujo y puertos; no conoce SQL.         | Domain contracts          |
| Domain       | `QualityGateRun`, `TestPlan`, `QualityGateRepository`       | Reglas, invariantes y contratos estables.         | PHP estándar              |
| Persistence  | `PdoQualityGateRepository`, `InMemoryQualityGateRepository` | Guardar/rehidratar runs y ejecutar SQL preparado. | PDO y Domain contracts    |

El controlador no crea `PDO`, no ejecuta SQL y no decide `passed`/`failed`. Esas decisiones pertenecen al caso de uso y al agregado de dominio.

## MVC dentro de Presentation

```text
HTTP request
    |
    v
QualityGateRequest -> QualityGateController -> RunQualityGate
                                                |
                                                v
                                             QualityGateRun
                                                |
                                                v
                                             JsonView
```

El punto de composición `public/index.php` selecciona el adaptador PDO. En pruebas, el mismo controlador/caso de uso puede recibir InMemory mediante inyección de dependencias.

## Patrón Repository

`QualityGateRepository` es la interfaz en el límite entre Application/Domain y Persistence:

```text
RunQualityGate ---> QualityGateRepository <--- PdoQualityGateRepository
                                              <--- InMemoryQualityGateRepository
```

- `PdoQualityGateRepository` usa sentencias preparadas y transacción para run y controles.
- `InMemoryQualityGateRepository` permite pruebas rápidas y deterministas.
- `GetLatestQualityGate` solo conoce `latest()`, no conoce tablas ni PDO.
- `PdoEvidenceStore` mantiene la evidencia como otra responsabilidad persistente, sin inflar el repositorio de runs.

## Objetos reutilizables

- `QualityGateRun`: entidad reutilizable por CLI, HTTP y CI.
- `TestPlan`: objeto de entrada con invariantes.
- `RunQualityGate`: caso de uso independiente de la interfaz.
- `QualityGateRepository`: contrato para cambiar SQLite por PostgreSQL o un fake.
- `E2ERunner`: puerto para sustituir el runner determinista por Playwright/Cypress.
- `JsonView`: salida HTTP reutilizable para las acciones MVC.

## Repositorio de datos compartido

La integración propuesta para un repositorio compartido debe conservar la propiedad del módulo QA y evitar que los módulos clínicos consulten tablas directamente:

```text
                 +----------------------+
                 | Shared Data Repository|
                 | qa_runs / evidence    |
                 +----------+-----------+
                            ^
                            |
             +--------------+--------------+
             |                             |
       Micro-HIS QA                   CI / Reportes
       Repository port               Read-only adapter
```

### Decisión

El repositorio compartido puede ser una base PostgreSQL administrada, pero cada módulo accede por su propio puerto y adaptador. QA es propietario de `qa_*`; los módulos clínicos no escriben esas tablas. Los reportes pueden usar un adaptador de lectura o una réplica, sin introducir consultas compartidas dentro del dominio.

### Riesgos y controles

| Riesgo                            | Control arquitectónico                                                           |
|-----------------------------------|----------------------------------------------------------------------------------|
| Acoplamiento a tablas compartidas | Solo Persistence conoce SQL; Domain conoce interfaces.                           |
| Escrituras cruzadas entre módulos | Propiedad explícita de tablas y permisos por esquema.                            |
| Falla del repositorio compartido  | Transacción, error propagado y pipeline bloqueado.                               |
| Mezcla de tenants                 | `tenant_id` debe viajar en el contrato de evidencia y validarse en el adaptador. |
| Lecturas costosas de reportes     | Réplica o proyección de lectura, no consultas desde casos de uso clínicos.       |

La fuente visual es [shared-repository.puml](diagrams/shared-repository.puml).
