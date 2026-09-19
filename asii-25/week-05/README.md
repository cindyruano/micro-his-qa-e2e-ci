# Semana 5 - API REST, cliente-servidor y microservicios

### Resumen
Se evoluciona el Micro-HIS QA a un diseño cliente-servidor con contrato REST OpenAPI 3.0, evaluando una frontera de microservicio y el flujo Git/GitHub asociado.

**ID y tipo:** PB-005 | Arquitectura  
**Responsable:** CINDY MAYTTÉ RUANO CALDERÓN 
**Actividad:** Gestión de ejecuciones automatizadas y calidad de versiones  
**Módulo:** ASII-25 — QA, pruebas E2E, CI y guía de despliegue final  
**Rol / dominio:** QA / Aseguramiento de Calidad y CI/CD  
**Título:** Contrato API y evaluación razonada de una frontera de microservicio QA  
**Período:** Semana 5 | REST, OpenAPI, seguridad, resiliencia, observabilidad y Git  
**Planificación:** Prioridad propuesta: Alta | Estimación: 14 horas | Fecha límite: Completado
**Propósito:** Especificar la comunicación y los límites técnicos de ejecuciones E2E, Quality Gates y evidencias sin crear infraestructura productiva ni introducir microservicios sin métricas.

**Alcance:**
- Contrato OpenAPI 3.0, esquemas JSON, cabeceras JWT, `X-Tenant-ID`, `Content-Type`, correlation ID y respuestas HTTP en [01-api-contract-and-microservices-architecture.md](01-api-contract-and-microservices-architecture.md).
- Evaluación de propiedad de datos, comunicación REST síncrona, cola/webhook asíncrona, seguridad, resiliencia y observabilidad.
- Flujo Issue, branch, Worktree, PR y Quality Gate en [02-git-issue-branch-worktree-pr-plan.md](02-git-issue-branch-worktree-pr-plan.md).
- Diagrama cliente-servidor/microservicio en [diagrams/client-server-microservice-architecture.svg](diagrams/client-server-microservice-architecture.svg).
- Decisión contra la sobreingeniería sin métricas de carga.

### Fuera del alcance
- Infraestructura productiva.
- División prematura del HIS en múltiples servicios.

### Criterios propuestos de aceptación
| ID    | Criterio propuesto de aceptación                                                                                           |
|-------|----------------------------------------------------------------------------------------------------------------------------|
| CA-01 | El contrato OpenAPI 3.0 define esquemas, cabeceras y respuestas HTTP para el flujo QA.                                     |
| CA-02 | La frontera de microservicio justifica propiedad de datos, comunicación, seguridad, resiliencia y observabilidad.          |
| CA-03 | El flujo Issue, branch, Worktree, PR y Quality Gate está documentado y el diagrama coincide con la arquitectura.           |
| CA-04 | La decisión de extracción queda condicionada a carga, latencia, tamaño de evidencias, frecuencia y escalado independiente. |

### Desglose sugerido
- T-01 Definir contrato OpenAPI y esquemas JSON.
- T-02 Analizar frontera, comunicación y propiedad de datos.
- T-03 Documentar seguridad, resiliencia y observabilidad.
- T-04 Preparar el plan Git/GitHub y el diagrama.

### Condiciones para cerrar la entrega
Los dos documentos, el diagrama y el plan de trabajo están revisados. La entrega se considera diseño/prototipo razonado y deja explícito que la extracción del servicio QA depende de métricas medibles.
