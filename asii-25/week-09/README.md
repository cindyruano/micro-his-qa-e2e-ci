# Semana 9 - Evaluación de usabilidad y accesibilidad

### Resumen
Se evalúa el flujo UX del Micro-HIS QA con las 10 heurísticas de Nielsen y WCAG 2.1/2.2 AA, priorizando hallazgos verificables para CI y pruebas E2E.

**ID y tipo:** PB-009 | Pruebas  
**Responsable:** CINDY MAYTTÉ RUANO CALDERÓN 
**Actividad:** Gestión de ejecuciones automatizadas y calidad de versiones  
**Módulo:** ASII-25 — QA, pruebas E2E, CI y guía de despliegue final  
**Rol / dominio:** QA / Aseguramiento de Calidad y CI/CD  
**Título:** Evaluación UX, accesibilidad y backlog de correcciones  
**Período:** Semana 9 | Nielsen, WCAG, teclado, foco, contraste y prevención de errores  
**Planificación:** Prioridad propuesta: Alta | Estimación: 10 horas | Fecha límite: Completado
**Propósito:** Detectar y priorizar problemas de usabilidad y accesibilidad que puedan afectar ejecuciones E2E, CI, Quality Gates, evidencias y despliegue reproducible.

**Alcance:**
- Checklist de las 10 heurísticas de Nielsen en [01-usability-and-accessibility-checklist.md](01-usability-and-accessibility-checklist.md).
- Checklist WCAG 2.1/2.2 AA por Perceptible, Operable, Comprensible y Robusto.
- Seis hallazgos con evidencia, impacto y corrección, más backlog P1-P3 con criterios verificables en [02-findings-and-prioritized-backlog.md](02-findings-and-prioritized-backlog.md).
- Evaluación de Dashboard de Runs, planificación, visor de evidencias y Quality Gates.
- Diagrama en [diagrams/usability-accessibility-evaluation.svg](diagrams/usability-accessibility-evaluation.svg).
- Los estados `Parcial` o `No cumple` deben confirmarse en la interfaz implementada con teclado, lector de pantalla, analizador de contraste y pruebas E2E.

### Fuera del alcance
- Corrección directa de la interfaz implementada; esta semana documenta hallazgos y backlog.

### Criterios propuestos de aceptación
| ID    | Criterio propuesto de aceptación                                                                                                    |
|-------|-------------------------------------------------------------------------------------------------------------------------------------|
| CA-01 | El checklist cubre las 10 heurísticas de Nielsen y WCAG 2.1/2.2 AA.                                                                 |
| CA-02 | Se documentan al menos seis hallazgos con evidencia, impacto y corrección propuesta.                                                |
| CA-03 | El backlog P1-P3 vincula cada corrección con un criterio verificable.                                                               |
| CA-04 | La evaluación considera teclado, foco, contraste, lector de pantalla, errores recuperables y prevención de ejecuciones incorrectas. |

### Desglose sugerido
- T-01 Revisar wireframes y flujos de la Semana 8.
- T-02 Aplicar heurísticas de Nielsen.
- T-03 Evaluar WCAG 2.1/2.2 AA.
- T-04 Registrar hallazgos y priorizar el backlog.

### Condiciones para cerrar la entrega
Los dos checklists, los seis hallazgos, el backlog, sus criterios verificables y el SVG están disponibles. Los hallazgos documentales quedan preparados para confirmación en la interfaz real y CI.
