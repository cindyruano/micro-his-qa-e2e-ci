# Semana 3 - Diseño arquitectónico C4 y separación por capas

### Resumen
Se inicia el diseño técnico del Micro-HIS QA en PHP 8.2+ vanilla, separando Presentation, Application, Domain y Persistence y definiendo pruebas representativas.

**ID y tipo:** PB-003 | Arquitectura  
**Responsable:** CINDY MAYTTÉ RUANO CALDERÓN 
**Actividad:** Gestión de ejecuciones automatizadas y calidad de versiones  
**Módulo:** ASII-25 — QA, pruebas E2E, CI y guía de despliegue final  
**Rol / dominio:** QA / Aseguramiento de Calidad y CI/CD  
**Título:** Arquitectura C4 y persistencia PDO para el módulo de QA  
**Período:** Semana 3 | C4/UML, PHP 8.2+, capas, PDO y pruebas  
**Planificación:** Prioridad propuesta: Alta | Estimación: 12 horas | Fecha límite: Completado
**Propósito:** Diseñar una base arquitectónica sin framework que permita planificar pruebas, ejecutar E2E, conservar evidencia y aplicar Quality Gates con dependencias controladas.

**Alcance:**
- Vista C4 de contenedores y componentes en [01-architectural-design-c4.md](01-architectural-design-c4.md) y [diagrams/c4-architecture-diagram.svg](diagrams/c4-architecture-diagram.svg).
- Separación Presentation, Application, Domain y Persistence, con flujo de dependencias.
- Ejemplos PHP 8.2 con tipos estrictos y persistencia PDO con sentencias preparadas.
- Camino feliz, rechazo por métricas y error de almacenamiento documentados en [02-layer-implementation-and-tests.md](02-layer-implementation-and-tests.md).
- Comandos previstos: `composer install`, `php -v`, `vendor/bin/phpunit tests` y `php tests/run.php`.

### Fuera del alcance
- Despliegue del runner E2E y de infraestructura productiva.
- La ejecución efectiva dependerá de los archivos de prueba implementados en la siguiente fase.

### Criterios propuestos de aceptación
| ID    | Criterio propuesto de aceptación                                                                                 |
|-------|------------------------------------------------------------------------------------------------------------------|
| CA-01 | La vista C4/UML representa contenedores, componentes y dependencias del módulo.                                  |
| CA-02 | Las cuatro capas tienen responsabilidades separadas y ejemplos PHP 8.2 con tipos estrictos.                      |
| CA-03 | La persistencia utiliza PDO y sentencias preparadas, y el diagrama SVG está enlazado correctamente.              |
| CA-04 | Se documentan el camino feliz, la regla de dominio y el error de persistencia, junto con los comandos de prueba. |

### Desglose sugerido
- T-01 Modelar la vista C4/UML.
- T-02 Separar Presentation, Application, Domain y Persistence.
- T-03 Documentar PDO y sentencias preparadas.
- T-04 Definir escenarios y comandos de prueba.

### Condiciones para cerrar la entrega
Los dos documentos y el SVG están disponibles, las dependencias de capas son trazables y los comandos de PHPUnit/prueba nativa quedan documentados. La evidencia presenta diseño y ejemplos representativos, sin declarar infraestructura productiva desplegada.
