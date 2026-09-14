# Vista arquitectonica - Micro-HIS QA

## 1. Contexto C4

El sistema recibe una solicitud del Runner CI/QA con commit, ambiente y plan de pruebas. Ejecuta controles E2E, conserva resultados y evidencia, y devuelve un código que permite aprobar o bloquear el pipeline.

```text
[Runner CI/QA] -> [Micro-HIS QA] -> [Base QA SQLite/PostgreSQL]
                         |
                         +-> [Adaptador E2E real o doble de pruebas]
```

## 2. Vista de contenedores

| Contenedor   | Responsabilidad                                                       | Tecnología                                    |
|--------------|-----------------------------------------------------------------------|-----------------------------------------------|
| Presentation | CLI y endpoint HTTP; convierte entrada/salida y código de proceso.    | PHP vanilla                                   |
| Application  | Coordina planificación, ejecución, persistencia y resultado del gate. | Casos de uso PHP                              |
| Domain       | Define `TestPlan`, `QualityGateRun` y contratos.                      | PHP puro, sin PDO                             |
| Persistence  | Implementa repositorios y transacciones.                              | PDO + SQLite; PostgreSQL posible              |
| E2E adapter  | Ejecuta escenarios y entrega resultados booleanos.                    | Doble determinista; futuro Playwright/Cypress |

## 3. Vista de componentes

La fuente [components.puml](diagrams/components.puml) muestra la dependencia principal:

```text
Presentation -> Application -> Domain contracts
                              ^
Persistence PDO --------------|
E2E adapter ------------------|
```

La flecha de infraestructura apunta al contrato, no al revés. `RunQualityGate` recibe `QualityGateRunRepository`, `EvidenceRepository` y `E2ERunner` por constructor.

## 4. Vista de secuencia

La fuente [sequence.puml](diagrams/sequence.puml) representa el flujo completo:

1. CI envía commit, ambiente y plan.
2. Presentation invoca `RunQualityGate`.
3. Application ejecuta el puerto `E2ERunner`.
4. Domain crea la ejecución y calcula el estado.
5. Persistence usa `prepare()` para guardar ejecución y controles dentro de una transacción.
6. Persistence conserva evidencia JSON con otra sentencia preparada.
7. Presentation devuelve `0` si pasa o `1` si falla.

## 5. Vista de clases y patrones

La fuente [classes.puml](diagrams/classes.puml) muestra:

- **Ports and Adapters / Hexagonal:** Application depende de interfaces del Domain.
- **Repository:** PDO encapsula SQL y persistencia.
- **Dependency Injection:** los adaptadores se reciben por constructor.
- **Test Double:** `DeterministicE2ERunner` y repositorios en memoria aíslan pruebas.

## 6. Requisitos arquitectónicos

- RF-01: aceptar un plan con escenarios y ejecutarlo mediante `E2ERunner`.
- RF-02: guardar un resultado por control y evidencia asociada al run.
- RF-03: bloquear con código distinto de cero si un control falla.
- RF-04: permitir reemplazar PDO y el runner por dobles para pruebas.
- RNF-01: PHP 8.2+ vanilla, sin framework.
- RNF-02: SQL parametrizado mediante PDO; no se interpolan valores de entrada.
- RNF-03: el dominio no conoce PDO, SQLite ni clases de Presentation.
- RNF-04: una persistencia fallida se propaga y nunca se reporta como éxito.
- RNF-05: la evidencia conserva commit, ambiente, plan, controles y estado.

## 7. Limites

La ejecución E2E de esta primera versión es determinista y sustituye al navegador por un puerto. Esto permite demostrar el diseño y los quality gates sin inventar una UI clínica. La integración con Playwright o Cypress es un adaptador posterior, no una dependencia del dominio.
