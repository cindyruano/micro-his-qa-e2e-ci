# Declaracion de IA

Esta semana continúa el mismo Micro-HIS QA de week-03 y reorganiza su entrada con MVC y Repository. La implementación mantiene PHP vanilla, PDO y SQLite de prueba, sin introducir framework.

Decisiones verificables:

- `QualityGateRepository` es el contrato estable del acceso a ejecuciones.
- PDO e InMemory son adaptadores intercambiables.
- `QualityGateController` no conoce SQL ni persiste directamente.
- La evidencia conserva la separación entre ejecución y almacenamiento de evidencia.
- El repositorio compartido se analiza como una frontera de infraestructura con propiedad de tablas y adaptadores por módulo.
