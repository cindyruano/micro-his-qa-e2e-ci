# Semana 4 - Arquitectura MVC y patrón Repositorio

### Resumen
Se organiza la entrada MVC y se define un contrato Repository con adaptadores PDO/SQLite e InMemory para desacoplar los casos de uso de la persistencia.

**ID y tipo:** PB-004 | Arquitectura  
**Responsable:** CINDY MAYTTÉ RUANO CALDERÓN 
**Actividad:** Gestión de ejecuciones automatizadas y calidad de versiones  
**Módulo:** ASII-25 — QA, pruebas E2E, CI y guía de despliegue final  
**Rol / dominio:** QA / Aseguramiento de Calidad y CI/CD  
**Título:** Repositorio intercambiable e integración con datos compartidos  
**Período:** Semana 4 | MVC, Repository, PDO, SQLite, pruebas y multitenancy  
**Planificación:** Prioridad propuesta: Alta | Estimación: 12 horas | Fecha límite: Completado
**Propósito:** Definir capas y objetos reutilizables para que el controlador no contenga SQL, reglas de negocio ni persistencia directa, manteniendo un contrato estable.

**Alcance:**
- Responsabilidades MVC y `TestExecutionRepositoryInterface` en [01-mvc-and-repository-pattern.md](01-mvc-and-repository-pattern.md).
- Adaptadores `PdoTestExecutionRepository` con SQLite/PDO e `InMemoryTestExecutionRepository`.
- Integración, concurrencia, transacciones PDO, auditoría y aislamiento por `X-Tenant-ID` en [02-shared-repository-integration.md](02-shared-repository-integration.md) y [diagrams/shared-repository-architecture.svg](diagrams/shared-repository-architecture.svg).
- Ejemplos de composición del caso de uso con ambos adaptadores.

### Fuera del alcance
- Migraciones, bootstrap productivo de dependencias, runner E2E y pruebas ejecutables finales.

### Criterios propuestos de aceptación
| ID    | Criterio propuesto de aceptación                                                                             |
|-------|--------------------------------------------------------------------------------------------------------------|
| CA-01 | Controller, Model/Domain y View tienen responsabilidades MVC documentadas.                                   |
| CA-02 | El controlador y el caso de uso no dependen de SQL ni de un adaptador concreto.                              |
| CA-03 | PDO/SQLite e InMemory cumplen la misma `TestExecutionRepositoryInterface`.                                   |
| CA-04 | La integración compartida cubre concurrencia, transacciones, auditoría y `X-Tenant-ID` y su diagrama existe. |

### Desglose sugerido
- T-01 Definir responsabilidades MVC.
- T-02 Establecer la interfaz Repository.
- T-03 Documentar adaptadores PDO/SQLite e InMemory.
- T-04 Analizar repositorio compartido y aislamiento por tenant.

### Condiciones para cerrar la entrega
Los contratos, adaptadores, ejemplos PHP y diagrama están revisados. Se verifica que ambos adaptadores puedan sustituirse sin modificar controlador ni caso de uso; la evidencia sigue siendo conceptual y de diseño.
