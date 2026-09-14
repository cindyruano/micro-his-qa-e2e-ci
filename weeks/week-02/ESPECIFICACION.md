# Especificación ASII-25: Quality Gate y Evidencia

## Problema

El repositorio tiene autenticación, modelos clínicos y reglas básicas de rama, pero no una ejecución reproducible que bloquee cambios cuando una prueba crítica falla. ASII-25 entrega el flujo transversal de planificación, ejecución, persistencia de evidencia y quality gates sin asumir propiedad de entidades clínicas.

## Historia de usuario

Como mantenedora de QA, quiero ejecutar un quality gate con un identificador de commit, ambiente y resultados deterministas, para que CI bloquee el merge ante una prueba crítica fallida y conserve evidencia auditable.

## Alcance

Incluye un comando Artisan `qa:quality-gate`, reglas de dominio, casos de aplicación, repositorio orientado a ejecución, adaptador Eloquent, fake para pruebas, tablas de ejecuciones/resultados/evidencias, seeders sintéticos, pruebas unitarias/feature/integración y workflow CI.

No incluye CRUD clínico, reglas de pacientes, expedientes o laboratorio, UI, despliegue productivo, ni relaciones FK entre bases. Los casos CU-02 a CU-05 de semana 1 permanecen como objetivos de cobertura futura mientras no exista su API.

## Actores

| Actor          | Responsabilidad                                                          |
|----------------|--------------------------------------------------------------------------|
| Runner CI/CD   | Envía commit, ambiente y controles; bloquea con código distinto de cero. |
| Mantenedora QA | Define controles, inspecciona resultados y conserva evidencia.           |
| Revisor        | Verifica contrato, trazabilidad y artefactos sin secretos.               |

## Contrato del comando

```text
php artisan qa:quality-gate --commit=<sha> --environment=<local|ci|staging> [--induce-failure]
```

El comando crea una ejecución, evalúa los controles declarados, guarda un resultado por control y finaliza con código `0` únicamente si todos pasan. `--induce-failure` es una herramienta de demostración controlada y siempre produce estado `failed`; no se usa en el workflow normal.

Controles iniciales: `unit`, `feature`, `migration` y `tenant-isolation`. El comando no ejecuta una segunda copia de PHPUnit dentro de sí mismo: recibe resultados mediante un runner de aplicación determinista, permitiendo que CI ejecute las pruebas una sola vez y registre sus códigos.

## Reglas de negocio QA

1. Commit y ambiente son obligatorios y se almacenan en cada ejecución.
2. Una ejecución es `passed` solo cuando no hay controles críticos fallidos.
3. Un control crítico fallido bloquea el pipeline.
4. Un fallo inducido es explícito, auditable y nunca se confunde con una ejecución normal.
5. La evidencia no contiene JWT, contraseñas ni datos clínicos reales.
6. `tenant_id` se conserva como UUID lógico; QA nunca consulta datos de un hospital por un tenant recibido sin validación.

## Criterios de aceptación

- CA-01: el comando exitoso persiste ejecución, cuatro resultados y estado `passed`.
- CA-02: un control crítico fallido retorna código no cero y estado `failed`.
- CA-03: una prueba de aplicación usa un fake del repositorio y no Eloquent.
- CA-04: la migración crea índices y permite `migrate:fresh` y rollback.
- CA-05: una integración PostgreSQL puede usar la conexión `pgsql` cuando está disponible; la suite local sigue siendo reproducible con SQLite.
- CA-06: CI ejecuta calidad, migración, pruebas y publica artefactos aun cuando el gate falla.
- CA-07: cada artefacto identifica SHA y ambiente.

## Propiedad de datos y contratos

ASII-25 es propietario únicamente de datos de ejecución QA: `qa_runs`, `qa_gate_results` y `qa_evidence`. Consume el contrato de Laravel TestCase, migraciones, PHPUnit y el identificador lógico de Tenant; no modifica modelos ni tablas clínicas. La conexión de persistencia es local a la base configurada y no mezcla CENTRAL/HOSPITAL.

## Ambigüedades resueltas

La configuración actual no define conexiones PostgreSQL separadas para CENTRAL y HOSPITAL. Se conserva la configuración existente y se deja PostgreSQL como objetivo de despliegue; el límite se garantiza por referencias lógicas y por no aceptar consultas clínicas en este módulo.

## Requisitos funcionales (RF)

| ID | Requisito | Criterio de aceptación verificable |
|---|---|---|
| RF-01 | Registrar una ejecución con commit, ambiente y controles declarados. | CA-01: la ejecución conserva esos datos y crea un resultado por control. |
| RF-02 | Evaluar los controles de planificación, pruebas, migración y aislamiento de tenant. | CA-01: los cuatro controles quedan persistidos con su resultado. |
| RF-03 | Conservar resultados y evidencia asociada a cada ejecución. | CA-01 y CA-07: las tablas QA y el artefacto identifican SHA y ambiente. |
| RF-04 | Bloquear el pipeline cuando falla un control crítico. | CA-02: el comando retorna código distinto de cero y estado `failed`. |
| RF-05 | Permitir sustituir la persistencia por un fake para probar el caso de uso. | CA-03: la prueba de aplicación no depende de Eloquent. |

## Requisitos no funcionales (RNF)

| ID | Categoría | Requisito | Verificación |
|---|---|---|---|
| RNF-01 | Seguridad | No guardar JWT, contraseñas ni datos clínicos reales en la evidencia. | Inspección de fixtures, salidas y artefactos publicados. |
| RNF-02 | Trazabilidad | Cada ejecución debe identificar commit, ambiente, estado y controles. | Consulta de `qa_runs`, `qa_gate_results` y `qa_evidence`. |
| RNF-03 | Disponibilidad | Un fallo de persistencia o control no puede producir un éxito falso. | CA-02 y prueba de fallo inducido. |
| RNF-04 | Mantenibilidad | El dominio y la aplicación deben depender de contratos, no de Eloquent o PostgreSQL. | CA-03 y revisión de `RunQualityGate`. |
| RNF-05 | Reproducibilidad | La suite local debe ejecutarse con SQLite y CI debe poder validar PostgreSQL. | CA-04, CA-05 y workflow de CI. |

## Diseño antes y después de aplicar DIP

| Antes: diseño acoplado | Después: inversión de dependencias |
|---|---|
| `QualityGateCommand` crea o invoca directamente un modelo Eloquent. | `QualityGateCommand` entrega datos a `RunQualityGate`. |
| La lógica de estado conoce tablas, transacciones y PostgreSQL. | `RunQualityGate` conoce `QualityGateRun` y el contrato `QualityGateRunRepository`. |
| Cambiar Eloquent por un fake o por otro motor obliga a modificar el caso de uso. | `EloquentQualityGateRunRepository` y el fake implementan el mismo contrato. |
| Las pruebas requieren infraestructura concreta. | Las pruebas de aplicación sustituyen la infraestructura por un fake. |

La dependencia apunta hacia la abstracción: `Presentation -> Application -> Domain`, mientras `Infrastructure` implementa el puerto del dominio. El ejemplo ejecutable está en `app/Application/QA/RunQualityGate.php` y `app/Domain/QA/QualityGateRunRepository.php`; la relación visual está en `diagramas/componentes-capas.puml` y `diagramas/clases-diseno.puml`.

## Responsabilidades y dependencias

| Componente | Responsabilidad | No debe conocer |
|---|---|---|
| `QualityGateCommand` | Recibir opciones, mostrar el resultado y devolver código de proceso. | SQL, Eloquent y reglas clínicas. |
| `RunQualityGate` | Orquestar la ejecución y construir el estado del gate. | La tecnología de persistencia. |
| `QualityGateRun` | Representar una ejecución y su regla `passes()`. | Laravel y la salida de consola. |
| `QualityGateRunRepository` | Definir el puerto para guardar una ejecución. | Tablas y detalles del motor. |
| `EloquentQualityGateRunRepository` | Persistir ejecución, resultados y evidencia. | La decisión de negocio del caso de uso. |

## Trazabilidad de la evidencia

| Afirmación | Evidencia verificable |
|---|---|
| El diseño aplica DIP | Constructor de `RunQualityGate` recibe `QualityGateRunRepository`; prueba de aplicación usa fake. |
| El gate bloquea fallos | `php artisan qa:quality-gate ... --induce-failure` retorna `1`. |
| El gate permite éxitos | El mismo comando sin `--induce-failure` retorna `0`. |
| La solución es reproducible | `php artisan test`: 50 pruebas pasadas, 5 omitidas y 115 aserciones. |
| La arquitectura está documentada | Fuentes `diagramas/componentes-capas.puml`, `diagramas/clases-diseno.puml` y `diagramas/secuencia.puml`. |
