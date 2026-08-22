# Guía Breve para la Defensa Oral

## 1. Resumen de una frase

"Construí un micro-monolito en PHP vanilla que planifica pruebas E2E, exige
evidencia antes de cerrar una ejecución, y aplica un quality gate que
aprueba o rechaza una versión según el porcentaje de pruebas aprobadas."

## 2. Preguntas probables y respuestas clave

**¿Por qué separaste en 4 capas y qué gana el proyecto con eso?**
Domain no depende de nada (ni de PDO ni de la consola); Application orquesta
usando interfaces (puertos); Persistence y Presentation son detalles
reemplazables. Así puedo probar las reglas de negocio sin base de datos real
(dobles en memoria) y cambiar de SQLite a otro motor sin tocar el dominio.

**¿Dónde está la regla de negocio más importante y cómo la probaste?**
En `TestExecution::finish()` (no se puede cerrar sin evidencia) y en
`QualityGateEvaluator::evaluate()` (el % de aprobación debe superar el
umbral del plan). Ambas están cubiertas en `tests/Domain/`.

**¿Cómo probaste el error de persistencia sin mockear todo?**
Usé una base SQLite real en memoria, construida con el mismo `schema.sql` de
producción, y forcé una violación de llave foránea (ejecución referenciando
un plan inexistente). El repositorio la envuelve en `PersistenceException`
para que las capas superiores nunca dependan de `PDOException`.

**Modifica en vivo: agrega un nuevo tipo de evidencia, por ejemplo `AUDIO`.**
Se agrega a la constante `ALLOWED_TYPES` en `src/Domain/Entity/Evidence.php`.
Es el único punto de cambio porque la validación del tipo vive únicamente ahí
(no está duplicada en Persistence ni en Presentation).

**Modifica en vivo: cambia el umbral por defecto del quality gate.**
Se cambia el valor por defecto del parámetro `$qualityGateThreshold` en el
constructor de `TestPlan` (actualmente 0.90), o se pasa explícitamente al
crear el plan vía `plan:create <id> <version> <descripcion> <umbral>`.

**¿Por qué no usaste un framework ni PHPUnit?**
Por requisito explícito de la consigna ("ningún framework"). Implementé un
autoload PSR-4 manual (`bootstrap.php`) y un micro-framework de pruebas
propio (`tests/Support/TestCase.php`) con aserciones básicas
(`assertTrue`, `assertEquals`, `assertThrows`).

**¿Qué pasa si la ejecución intenta pasar de PLANNED directo a PASSED?**
`ExecutionStatus::canTransitionTo()` lo rechaza y se lanza
`InvalidStatusTransitionException`; está cubierto por
`testNoPermiteSaltarDePlanificadaAAprobada`.

## 3. Comandos para demostrar en vivo

```bash
php tests/run_tests.php
php bin/console.php plan:create DEMO 1.0.0 "Demo defensa" 0.80
php bin/console.php execution:start E1 DEMO "Escenario demo" QA
php bin/console.php evidence:attach EV1 E1 LOG ./storage/demo.log
php bin/console.php execution:finish E1 PASSED
php bin/console.php gate:evaluate DEMO
```

## 4. Evidencia Git a mostrar

```bash
git log --oneline
git log -1 --stat
```
