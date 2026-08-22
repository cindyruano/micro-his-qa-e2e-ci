# Micro-HIS — QA, Pruebas E2E, CI y Guía de Despliegue Final

**Estudiante:** Cindy Maytté Ruano Calderón
**GitHub:** `cindyruano`
**Módulo oficial:** QA, pruebas E2E, CI y guía de despliegue final
**Adaptación de la actividad:** Gestión de ejecuciones automatizadas y calidad de versiones

## 1. Descripción

Micro-monolito educativo y ejecutable en **PHP 8.2+ vanilla** (sin framework) que
modela el proceso de:

1. **Planificación de pruebas** — crear un plan de pruebas (`TestPlan`) asociado a
   una versión (release) con un umbral de quality gate configurable.
2. **Ejecución E2E** — iniciar y cerrar ejecuciones de escenarios de prueba
   (`TestExecution`) siguiendo una máquina de estados controlada.
3. **Conservación de evidencia** — adjuntar evidencia (logs, reportes, capturas,
   video) a cada ejecución; una ejecución no puede cerrarse sin evidencia.
4. **Aplicación de quality gates** — aprobar o rechazar una versión según el
   porcentaje de ejecuciones aprobadas frente al umbral definido en el plan.

## 2. Arquitectura en capas

```
src/
  Domain/          Entidades, Value Objects, excepciones y reglas de negocio.
                    No depende de ninguna otra capa.
  Application/      Casos de uso que orquestan el Domain a través de
                    interfaces de repositorio (puertos).
  Persistence/      Implementación PDO/SQLite de los repositorios
                    (adaptador). Sentencias preparadas, sin ORM.
  Presentation/      CLI (consola) y front controller HTTP mínimo.
                    Traduce entrada externa a llamadas de Application.
```

La regla de dependencia es de afuera hacia adentro: `Presentation` depende de
`Application`, que depende de `Domain` a través de interfaces
(`TestPlanRepositoryInterface`, `TestExecutionRepositoryInterface`,
`EvidenceRepositoryInterface`). `Persistence` implementa esas interfaces, pero
`Domain` nunca conoce a `Persistence`.

## 3. Reglas de dominio implementadas

| # | Regla | Dónde vive |
|---|-------|------------|
| 1 | Una ejecución solo puede transicionar `PLANNED → RUNNING → PASSED/FAILED` | `ExecutionStatus::canTransitionTo()` |
| 2 | Una ejecución no puede cerrarse (`PASSED`/`FAILED`) sin al menos una evidencia | `TestExecution::finish()` |
| 3 | Un plan solo aprueba el quality gate si la tasa de aprobación ≥ umbral configurado | `QualityGateEvaluator::evaluate()` |
| 4 | Errores de integridad referencial en persistencia se traducen a `PersistenceException` de dominio, nunca se filtra `PDOException` a capas superiores | `Pdo*Repository::save()` |

## 4. Requisitos técnicos cumplidos

- PHP 8.2+ (probado en PHP 8.3.6).
- 4 capas: Presentation, Application, Domain, Persistence.
- PDO con **sentencias preparadas** en todas las consultas (`?`/`:named` params).
- Configuración fuera del código: `config/database.php` lee de variables de
  entorno (`.env`, ver `.env.example`), nunca credenciales hardcodeadas.
- Sin ningún framework (ni de dominio, ni de pruebas, ni de ruteo HTTP/CLI).
- Autoload propio (PSR-4 manual) en `bootstrap.php`, sin Composer.
- Pruebas automatizadas propias (`tests/Support/TestCase.php`) cubriendo:
  camino feliz, regla de dominio y error de persistencia (ver sección 6).

## 5. Cómo ejecutar

```bash
# 1. Copiar configuración de entorno
cp .env.example .env

# 2. Ejecutar la suite de pruebas
php tests/run_tests.php

# 3. Usar la consola (Presentation/CLI)
php bin/console.php plan:create PLAN-2.5.0 2.5.0 "Regresion QA/E2E" 0.80
php bin/console.php execution:start EXEC-1 PLAN-2.5.0 "Registro de plan" QA
php bin/console.php evidence:attach EVID-1 EXEC-1 REPORT ./storage/evidencias/exec-1.json
php bin/console.php execution:finish EXEC-1 PASSED
php bin/console.php execution:list PLAN-2.5.0
php bin/console.php gate:evaluate PLAN-2.5.0

# 4. Front controller HTTP (opcional)
php -S localhost:8080 -t public
# GET http://localhost:8080/?route=executions&plan_id=PLAN-2.5.0
# GET http://localhost:8080/?route=gate&plan_id=PLAN-2.5.0
```

## 6. Evidencia de pruebas (camino feliz, regla de dominio, error de persistencia)

Ejecutar:

```bash
php tests/run_tests.php
```

Resultado esperado (ver también `docs/evidencia_pruebas.txt`):

```
Total: 11 | Aprobadas: 11 | Fallidas: 0
```

Cobertura por tipo de prueba exigida en la consigna:

- **Camino feliz:** `tests/Application/HappyPathExecutionFlowTest.php`
  (flujo completo: crear plan → iniciar ejecuciones → adjuntar evidencia →
  cerrar ejecuciones → aprobar quality gate), usando dobles en memoria.
- **Regla de dominio:** `tests/Domain/TestExecutionDomainRulesTest.php`
  (evidencia obligatoria, transiciones de estado inválidas) y
  `tests/Domain/QualityGateEvaluatorTest.php` (umbral del quality gate).
- **Error de persistencia:** `tests/Persistence/PdoRepositoryPersistenceErrorTest.php`,
  usando una base de datos SQLite de prueba real (no un mock) creada a partir
  del mismo `schema.sql` de producción, forzando una violación de llave foránea.

## 7. CI

Ver `.github/workflows/ci.yml`: en cada `push`/`pull_request` se instala PHP
8.3 con extensión `pdo_sqlite` y se ejecuta `php tests/run_tests.php`. El
workflow falla si alguna prueba falla (código de salida distinto de 0).

## 8. Guía de despliegue

Ver `docs/guia_despliegue.md`.

## 9. Declaración de uso de IA

Ver `DECLARACION_IA.md`.

## 10. Estructura completa del repositorio

Ver `docs/arbol_archivos.txt` (generado con `find` sobre el proyecto).
