# Semana 3 - Micro-HIS QA

Micro-HIS QA es un micro-monolito educativo en PHP 8.2+ vanilla para ejecutar el flujo de planificación de pruebas, ejecución E2E, conservación de evidencia y aplicación de quality gates.

## Entregable

- Arquitectura por capas: Presentation, Application, Domain y Persistence.
- PDO con SQLite y sentencias preparadas.
- Doble determinista para representar la ejecución E2E sin depender de un navegador.
- Pruebas de camino feliz, regla de dominio, persistencia PDO y error de persistencia.
- Vistas C4/UML en [docs/arquitectura.md](docs/arquitectura.md) y fuentes PlantUML en [docs/diagrams](docs/diagrams).

## Requisitos

- PHP 8.2 o superior.
- Extensión `pdo_sqlite`.
- No requiere framework, Composer ni base de datos externa.

## Ejecucion

Desde esta carpeta:

```powershell
php scripts/init-db.php
php scripts/run-tests.php
php bin/qa.php abc123 local
php bin/qa.php abc123 local --induce-failure
php -S localhost:8000 -t public
```

El primer gate termina con código `0`; el segundo termina con código `1` para demostrar el bloqueo. El endpoint HTTP devuelve el estado del módulo en `http://localhost:8000`.

## Flujo

1. Presentation recibe commit, ambiente y plan.
2. Application coordina el runner E2E y construye `QualityGateRun`.
3. Domain valida el plan y determina `passed` o `failed`.
4. Persistence guarda la ejecución, controles y evidencia mediante PDO.
5. Presentation devuelve el estado y el código de proceso para CI.

La implementación deja `E2ERunner` como puerto: el doble determinista permite pruebas locales y un adaptador de Playwright/Cypress puede incorporarse posteriormente sin modificar el caso de uso.
