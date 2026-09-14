# PR draft: ASII-25 quality gate, pruebas E2E y guía final

## Resumen

Implementa el flujo transversal de QA con arquitectura por capas, persistencia de ejecuciones/resultados/evidencia, comando Artisan, pruebas y quality gate en el workflow existente.

## Checklist

- [x] Especificación y ADR antes del código.
- [x] Cinco diagramas editables y renders.
- [x] Migración reversible, factory y seeder sintético.
- [x] Tres reglas de dominio, dos pruebas de aplicación y una integración PostgreSQL opt-in.
- [x] Pipeline bloqueante con SHA y ambiente.
- [x] Evidencia de éxito y fallo inducido.
- [x] Guía local y declaración de IA.
- [ ] Revisar en GitHub y solicitar aprobación.

## Pruebas ejecutadas

`php artisan test`, `php artisan migrate:fresh --seed --force`, Pint focal, gate normal y gate con `--induce-failure`.

## Riesgos

La integración PostgreSQL requiere un servicio disponible. Los casos clínicos CU-02 a CU-05 siguen pendientes porque no tienen API bajo prueba. El workflow publica evidencia aun si el job falla.
