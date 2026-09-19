# Semana 11 - Prototipo navegable móvil/web

### Resumen
Se cierra el ciclo de prototipado con una experiencia Desktop/Móvil navegable para planificar, ejecutar, verificar SHA-256, evaluar Quality Gates y aprobar o bloquear despliegues.

**ID y tipo:** PB-011 | Implementación  
**Responsable:** CINDY MAYTTÉ RUANO CALDERÓN 
**Actividad:** Gestión de ejecuciones automatizadas y calidad de versiones  
**Módulo:** ASII-25 — QA, pruebas E2E, CI y guía de despliegue final  
**Rol / dominio:** QA / Aseguramiento de Calidad y CI/CD  
**Título:** Prototipo navegable del flujo QA con camino feliz y error crítico  
**Período:** Semana 11 | HTML5, CSS3, JavaScript, navegación, estados y WCAG  
**Planificación:** Prioridad propuesta: Alta | Estimación: 14 horas | Fecha límite: Completado
**Propósito:** Construir un prototipo navegable que mantenga consistencia de roles, estados, seguridad de tenant y accesibilidad WCAG 2.1 AA en desktop y móvil.

**Alcance:**
- Mapa y especificación en [01-navigation-map-and-prototype-spec.md](01-navigation-map-and-prototype-spec.md).
- Escenarios feliz y error crítico en [02-happy-path-and-critical-error-scenarios.md](02-happy-path-and-critical-error-scenarios.md).
- Prototipo HTML5/CSS3/JavaScript: [prototype/index.html](prototype/index.html), [prototype/styles.css](prototype/styles.css) y [prototype/app.js](prototype/app.js).
- Camino feliz con despliegue aprobado y error crítico con `FAIL`, `Hash mismatch`, bloqueo y retry idempotente.
- Roles `QA Lead`, `Developer` y `CI Runner`, estados, reglas WCAG y [diagrams/interactive-prototype-navigation-map.svg](diagrams/interactive-prototype-navigation-map.svg).
- Ejecución directa en navegador o mediante `php -S localhost:8080 -t docs/asii-25/week-11/prototype`.

### Fuera del alcance
- Backend real, secretos, API productiva, runner E2E y pipeline CI productivos.
- Las llamadas, hashes y estados son simulados para defender la interacción.

### Criterios propuestos de aceptación
| ID    | Criterio propuesto de aceptación                                                                         |
|-------|----------------------------------------------------------------------------------------------------------|
| CA-01 | El mapa y el prototipo permiten navegar el flujo principal en desktop y móvil.                           |
| CA-02 | El camino feliz llega a `Despliegue aprobado` y conserva roles, estados y verificación SHA-256 simulada. |
| CA-03 | El error crítico muestra `FAIL`, `Hash mismatch`, bloqueo y `Reintentar` de forma idempotente.           |
| CA-04 | Los archivos HTML, CSS, JavaScript y SVG existen, y la vista puede comprobarse en desktop y 320–430 px.  |

### Desglose sugerido
- T-01 Definir mapa de navegación y especificación.
- T-02 Construir las vistas HTML5/CSS3/JavaScript.
- T-03 Simular camino feliz y error crítico.
- T-04 Revisar roles, estados, WCAG y comportamiento responsive.

### Condiciones para cerrar la entrega
El mapa, los escenarios, los tres archivos del prototipo y el SVG están disponibles. Se verifican ambos recorridos y el redimensionamiento a desktop y 320–430 px, dejando documentado que la interacción es simulada.
