# Semana 10 - Diseño responsive y movilidad

### Resumen
Se adapta el flujo Micro-HIS QA a viewports de 320–430 px, priorizando estado, Quality Gate, evidencia y acción segura con conexión limitada y reintentos idempotentes.

**ID y tipo:** PB-010 | Diseño  
**Responsable:** CINDY MAYTTÉ RUANO CALDERÓN 
**Actividad:** Gestión de ejecuciones automatizadas y calidad de versiones  
**Módulo:** ASII-25 — QA, pruebas E2E, CI y guía de despliegue final  
**Rol / dominio:** QA / Aseguramiento de Calidad y CI/CD  
**Título:** Propuesta mobile-first para ejecuciones E2E, evidencias y despliegue  
**Período:** Semana 10 | Responsive, breakpoints, offline, caché y recuperación  
**Planificación:** Prioridad propuesta: Alta | Estimación: 10 horas | Fecha límite: Completado
**Propósito:** Reducir carga cognitiva y definir navegación, formularios, confirmaciones y recuperación ante conexión limitada para las acciones críticas de QA.

**Alcance:**
- Estrategia mobile-first, breakpoints `xs`, `sm`, `md`, cuatro pantallas y touch targets mínimos de `44x44 px` en [01-mobile-responsive-proposal-and-breakpoints.md](01-mobile-responsive-proposal-and-breakpoints.md).
- Dos escenarios de contenido y conexión limitada en [02-mobile-scenarios-content-and-offline-recovery.md](02-mobile-scenarios-content-and-offline-recovery.md).
- Tablas convertidas en cards, formularios en una columna, Background Sync, LocalStorage/Cache Storage, estados de red y retries.
- Protección de JWT, tenant, hashes y URLs temporales.
- Diagrama en [diagrams/mobile-architecture-and-viewport-flow.svg](diagrams/mobile-architecture-and-viewport-flow.svg).

### Fuera del alcance
- Implementación de Service Worker, aplicación nativa y sincronización productiva.

### Criterios propuestos de aceptación
| ID    | Criterio propuesto de aceptación                                                                                 |
|-------|------------------------------------------------------------------------------------------------------------------|
| CA-01 | La propuesta cubre viewports de 320–430 px con breakpoints `xs`, `sm` y `md`.                                    |
| CA-02 | Las cuatro pantallas priorizan estado, Quality Gate, evidencia y acción segura, con touch targets de `44x44 px`. |
| CA-03 | Se documentan dos escenarios de conexión limitada, modo offline, caché y retries idempotentes.                   |
| CA-04 | JWT, tenant, hashes, URLs temporales y el flujo por viewport están protegidos y representados en el diagrama.    |

### Desglose sugerido
- T-01 Definir estrategia mobile-first y breakpoints.
- T-02 Adaptar las cuatro pantallas y los controles táctiles.
- T-03 Diseñar escenarios offline y de conexión limitada.
- T-04 Documentar caché, sincronización, seguridad y recuperación.

### Condiciones para cerrar la entrega
La propuesta responsive, los dos escenarios y el diagrama están enlazados. Se verifican contratos, estados y límites, dejando explícito que la sincronización productiva queda para una implementación posterior.
