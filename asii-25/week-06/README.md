# Semana 6 - Primera evaluación parcial y defensa arquitectónica

### Resumen
Se defiende la arquitectura del Micro-HIS QA conectando actores, UML, RF/RNF, SOLID, capas, Repository y contrato API REST, con un cambio práctico de concurrencia y evidencias hashadas.

**ID y tipo:** PB-006 | Pruebas  
**Responsable:** CINDY MAYTTÉ RUANO CALDERÓN 
**Actividad:** Gestión de ejecuciones automatizadas y calidad de versiones  
**Módulo:** ASII-25 — QA, pruebas E2E, CI y guía de despliegue final  
**Rol / dominio:** QA / Aseguramiento de Calidad y CI/CD  
**Título:** Defensa trazable de arquitectura y respuesta a un cambio práctico  
**Período:** Semana 6 | Evaluación, trazabilidad, concurrencia, hashing y CI/CD  
**Planificación:** Prioridad propuesta: Alta | Estimación: 10 horas | Fecha límite: Completado
**Propósito:** Demostrar que las decisiones arquitectónicas soportan ejecuciones E2E concurrentes por tenant y evidencias comprimidas con SHA-256 sin superar un p95 de 2 segundos en el Quality Gate.

**Alcance:**
- Presentación formal de 8 diapositivas con notas en [01-architecture-defense-presentation.md](01-architecture-defense-presentation.md).
- Matriz requisito → decisión → principio → evidencia en [02-decision-to-evidence-matrix.md](02-decision-to-evidence-matrix.md).
- Registro de concurrencia, artefactos, p95 y despliegue en [03-practical-change-impact-record.md](03-practical-change-impact-record.md).
- Mapa arquitectónico trazable en [diagrams/traceable-architecture-map.svg](diagrams/traceable-architecture-map.svg).
- Evaluación síncrona del Quality Gate y derivación del empaquetado pesado a un worker para proteger p95, aislar tenants y conservar decisiones reproducibles por commit, configuración, métricas y hash.

### Fuera del alcance
- Despliegue del worker, infraestructura productiva y pruebas de carga reales.

### Criterios propuestos de aceptación
| ID    | Criterio propuesto de aceptación                                                                             |
|-------|--------------------------------------------------------------------------------------------------------------|
| CA-01 | La presentación conecta actores, UML, RF/RNF, SOLID, capas, Repository y API REST.                           |
| CA-02 | La matriz relaciona cada requisito con decisión, principio y evidencia.                                      |
| CA-03 | El cambio práctico cubre concurrencia por tenant, SHA-256, p95, CI/CD y despliegue reproducible.             |
| CA-04 | Los cuatro artefactos están revisados y la defensa distingue diseño académico de infraestructura desplegada. |

### Desglose sugerido
- T-01 Preparar la presentación y notas.
- T-02 Construir la matriz de trazabilidad.
- T-03 Registrar el impacto del cambio práctico.
- T-04 Integrar el mapa y defender las decisiones.

### Condiciones para cerrar la entrega
La presentación tiene ocho diapositivas, la matriz y el registro son trazables, el mapa SVG está disponible y la defensa explica el worker, el aislamiento por tenant y el límite p95 sin afirmar despliegues no realizados.
