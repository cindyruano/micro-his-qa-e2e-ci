# Semana 7 - Diseño de componentes y refactorización

### Resumen
Se diseñan los componentes frontend y backend del Micro-HIS QA y se separa el controlador que mezclaba HTTP, Quality Gates, SQL y evidencias mediante DTOs, interfaces e inyección de dependencias.

**ID y tipo:** PB-007 | Diseño  
**Responsable:** CINDY MAYTTÉ RUANO CALDERÓN 
**Actividad:** Gestión de ejecuciones automatizadas y calidad de versiones  
**Módulo:** ASII-25 — QA, pruebas E2E, CI y guía de despliegue final  
**Rol / dominio:** QA / Aseguramiento de Calidad y CI/CD  
**Título:** Componentización y reducción del acoplamiento del flujo QA  
**Período:** Semana 7 | Componentes, contratos, DTOs y refactorización PHP 8.2+  
**Planificación:** Prioridad propuesta: Alta | Estimación: 12 horas | Fecha límite: Completado
**Propósito:** Separar UI, aplicación, dominio y persistencia sin ampliar el módulo clínico, mejorando mantenibilidad, testabilidad y despliegue reproducible.

**Alcance:**
- Componentes `QARunnerUI`, `EvidenceLogViewer`, `ExecutionController`, `EvaluateQualityGateUseCase`, `QualityGatePolicy` y `EvidenceStorageManager`.
- DTOs y JSON Schemas de entrada/salida.
- Diseño de componentes y contratos en [01-component-design-and-contracts.md](01-component-design-and-contracts.md).
- Comparación Antes/Después en [02-coupling-refactor-before-after.md](02-coupling-refactor-before-after.md).
- Diagrama en [diagrams/component-architecture-and-refactor.svg](diagrams/component-architecture-and-refactor.svg).

### Fuera del alcance
- Ampliación del módulo clínico.
- Conexión productiva de adaptadores, runner E2E y pipeline CI.

### Criterios propuestos de aceptación
| ID    | Criterio propuesto de aceptación                                                                          |
|-------|-----------------------------------------------------------------------------------------------------------|
| CA-01 | Los componentes frontend y backend tienen responsabilidades y contratos de entrada/salida definidos.      |
| CA-02 | La refactorización separa HTTP, Quality Gates, SQL y evidencias mediante DTOs, interfaces e inyección.    |
| CA-03 | El diagrama SVG coincide con la organización propuesta y los JSON Schemas están documentados.             |
| CA-04 | La comparación Antes/Después justifica mejoras de mantenibilidad, testabilidad y despliegue reproducible. |

### Desglose sugerido
- T-01 Identificar componentes frontend y backend.
- T-02 Definir contratos, DTOs y JSON Schemas.
- T-03 Refactorizar el punto de mayor acoplamiento.
- T-04 Comparar resultados y actualizar el diagrama.

### Condiciones para cerrar la entrega
Los dos documentos y el diagrama están enlazados, la separación de responsabilidades es revisable y se deja claro que los ejemplos son diseño técnico, no una implementación productiva conectada.
