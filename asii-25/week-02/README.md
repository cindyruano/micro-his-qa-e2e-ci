# Semana 2 - Requisitos, diseño y principios SOLID

### Resumen
Se definen los requisitos y el diseño técnico del flujo de planificación de pruebas, ejecución E2E, conservación de evidencia y aplicación de Quality Gates mediante DIP y principios SOLID.

**ID y tipo:** PB-002 | Diseño  
**Responsable:** CINDY MAYTTÉ RUANO CALDERÓN 
**Actividad:** Gestión de ejecuciones automatizadas y calidad de versiones  
**Módulo:** QA, pruebas E2E, CI y guía de despliegue final  
**Rol / dominio:** QA / Aseguramiento de Calidad y CI/CD  
**Título:** Diseño desacoplado del núcleo de QA con requisitos verificables  
**Período:** Semana 2 | RF/RNF, criterios de aceptación, SOLID y DIP  
**Planificación:** Prioridad propuesta: Alta | Estimación: 10 horas | Fecha límite: Completado
**Propósito:** Establecer responsabilidades y dependencias comprobables para que el core de QA sea independiente de proveedores de CI, almacenamiento y servicios externos.

**Alcance:**
- RF-01 a RF-06 y RNF-01 a RNF-04 para rendimiento, aislamiento, seguridad y mantenibilidad.
- Escenarios Gherkin para aprobación, bloqueo, cobertura y multitenancy.
- Diseño antes/después y refactorización DIP en PHP/Laravel en [01-requirements-and-design.md](01-requirements-and-design.md) y [02-solid-dip-refactoring.md](02-solid-dip-refactoring.md).
- Comparativa de acoplamiento, cohesión, testabilidad y mantenibilidad.
- Uso de dobles controlados para probar sin GitHub, bases de datos, almacenamiento o HTTP reales; posibilidad de cambiar GitHub Actions por GitLab CI o almacenamiento local por S3 sin alterar el núcleo.

### Fuera del alcance
- Implementación de suites E2E, workflows definitivos e infraestructura productiva.

### Criterios propuestos de aceptación
| ID    | Criterio propuesto de aceptación                                                           |
|-------|--------------------------------------------------------------------------------------------|
| CA-01 | RF-01 a RF-06 y RNF-01 a RNF-04 están definidos y son verificables.                        |
| CA-02 | Los escenarios Gherkin cubren aprobación, bloqueo, cobertura y multitenancy.               |
| CA-03 | La propuesta antes/después evidencia la aplicación de DIP y responsabilidades SOLID.       |
| CA-04 | La comparación demuestra mejoras de acoplamiento, cohesión, testabilidad y mantenibilidad. |

### Desglose sugerido
- T-01 Definir RF, RNF y criterios de aceptación.
- T-02 Documentar escenarios Gherkin.
- T-03 Modelar el diseño antes/después.
- T-04 Justificar DIP y comparar las propiedades técnicas.

### Condiciones para cerrar la entrega
Los requisitos, escenarios y refactorización están documentados y revisados en los dos archivos enlazados. La evidencia se considera conceptual y de diseño; no se declara implementada la infraestructura productiva.
