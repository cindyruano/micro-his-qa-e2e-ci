# Semana 4 - Arquitectura en capas y Repository

Esta entrega continúa el mismo Micro-HIS QA de la Semana 3. El flujo sigue siendo planificación de pruebas, ejecución E2E, conservación de evidencia y aplicación de quality gates, pero la entrada se organiza con MVC y la persistencia queda detrás de `QualityGateRepository`.

## Capas

- **Presentation/MVC:** request, controller y vista JSON. No contiene SQL ni reglas del gate.
- **Application:** coordina casos de uso `RunQualityGate` y `GetLatestQualityGate`.
- **Domain:** `QualityGateRun`, `TestPlan` y contratos de repositorio, evidencia y runner E2E.
- **Persistence:** adaptadores `PdoQualityGateRepository`, `InMemoryQualityGateRepository`, `PdoEvidenceStore` y sus dobles.

## Ejecucion

Requisitos: PHP 8.2+ y `pdo_sqlite`.

```powershell
php scripts/init-db.php
php scripts/run-tests.php
php bin/qa.php abc123 local
php bin/qa.php abc123 local --induce-failure
php -S localhost:8000 -t public
```

El endpoint MVC acepta `POST /` con JSON como:

```json
{"commit":"abc123","environment":"local","plan":"smoke","scenarios":["login","evidence_saved"]}
```

Para el análisis de arquitectura consulte [docs/arquitectura.md](docs/arquitectura.md) y las fuentes PlantUML en [docs/diagrams](docs/diagrams).
