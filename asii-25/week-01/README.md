# Semana 1 - Alcance, actores y trazabilidad de QA/CI

### Resumen
Se define el modelo conceptual para ejecutar pruebas E2E del HIS dentro de CI, comprobar compatibilidad multitenant y RBAC, evaluar Quality Gates y publicar evidencia de calidad para cada versión.

**ID y tipo:** PB-001 | Análisis  
**Responsable:** CINDY MAYTTÉ RUANO CALDERÓN 
**Actividad:** Gestión de ejecuciones automatizadas y calidad de versiones  
**Módulo:** QA, pruebas E2E, CI y guía de despliegue final  
**Título:** Definición del alcance y del flujo conceptual de QA, pruebas E2E y CI  
**Período:** Semana 1 | Alcance, actores, casos de uso, UML y trazabilidad  
**Planificación:** Prioridad propuesta: Alta | Estimación: 8 horas | Fecha límite: Completado
**Propósito:** Establecer el modelo conceptual que orienta la ejecución E2E, la validación multitenant/RBAC, la evaluación de Quality Gates y la publicación de evidencia por versión.

**Alcance:**
- Definición de alcance, actores y casos de uso en [alcance-actores-casosUsos.md](alcance-actores-casosUsos.md).
- Flujo de actividad con decisión `PASA/FALLA`, secuencia de validación y matriz de trazabilidad en [actividad-secuencia-trazabilidad.md](actividad-secuencia-trazabilidad.md).
- Tres diagramas UML nuevos en formato SVG: [diagramas/caso-uso.svg](diagramas/caso-uso.svg), [diagramas/actividad.svg](diagramas/actividad.svg) y [diagramas/secuencia.svg](diagramas/secuencia.svg).
- Cuatro diagramas históricos conservados, según el estado de la entrega original.
- Evidencia conceptual de diseño de QA y CI para la Semana 1.

### Fuera del alcance
- Implementación final de suites Playwright/Cypress.
- Workflows definitivos de GitHub Actions e infraestructura real.

### Criterios propuestos de aceptación
| ID    | Criterio propuesto de aceptación                                                                                                                          |
|-------|-----------------------------------------------------------------------------------------------------------------------------------------------------------|
| CA-01 | El alcance, los actores y los casos de uso describen la ejecución E2E, multitenancy, RBAC y Quality Gates.                                                |
| CA-02 | El flujo de actividad representa explícitamente las decisiones `PASA/FALLA`.                                                                              |
| CA-03 | La secuencia y la matriz de trazabilidad relacionan requisitos, validaciones y evidencias.                                                                |
| CA-04 | Los tres diagramas SVG nuevos están disponibles en sus rutas reales y se conserva la referencia a los cuatro diagramas históricos de la entrega original. |

### Desglose sugerido
- T-01 Definir alcance, actores y casos de uso.
- T-02 Modelar el flujo de actividad `PASA/FALLA`.
- T-03 Documentar la secuencia de validación y la trazabilidad.
- T-04 Elaborar y conservar los diagramas UML.

### Condiciones para cerrar la entrega
La documentación conceptual está completada, los archivos Markdown y SVG son accesibles desde este README, la trazabilidad está revisada y se deja explícito que no se implementan todavía suites E2E, workflows ni infraestructura productiva.


