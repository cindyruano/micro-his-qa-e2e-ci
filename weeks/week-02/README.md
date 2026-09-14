-
# Semana 2: implementación vertical de QA

Esta semana implementa la especificación y el ADR del quality gate transversal de ASII-25.

## Entregables

- `ESPECIFICACION.md` y `ADR-001-arquitectura.md`.
- Fuentes UML en `diagramas/` y sus renders SVG/PNG cuando el entorno de PlantUML esté disponible.
- Capas Domain, Application, Infrastructure y Presentation para `qa:quality-gate`.
- Migraciones, factories, seeders, pruebas y workflow CI.
- `EVIDENCIA.md`, `DECLARACION_IA.md`, guía local y borrador de PR.

## Ejecución local

```powershell
php artisan qa:quality-gate --commit=$(git rev-parse HEAD) --environment=local
php artisan qa:quality-gate --commit=$(git rev-parse HEAD) --environment=local --induce-failure
php artisan test
```

La primera ejecución debe terminar con código `0`; la segunda debe terminar con código distinto de cero y demostrar que el gate bloquea.
