# Semana 8 - Diseño de experiencia de usuario

### Resumen
Se diseña la experiencia UX del Micro-HIS QA para planificar suites, ejecutar E2E, revisar evidencias y aplicar Quality Gates por rol autorizado.

**ID y tipo:** PB-008 | Diseño  
**Responsable:** CINDY MAYTTÉ RUANO CALDERÓN 
**Actividad:** Gestión de ejecuciones automatizadas y calidad de versiones  
**Módulo:** ASII-25 — QA, pruebas E2E, CI y guía de despliegue final  
**Rol / dominio:** QA / Aseguramiento de Calidad y CI/CD  
**Título:** Flujo UX, wireframes y estados de interacción del módulo QA  
**Período:** Semana 8 | User Flow, wireframes, roles, accesibilidad y protección de datos  
**Planificación:** Prioridad propuesta: Alta | Estimación: 12 horas | Fecha límite: Completado
**Propósito:** Definir un flujo usable y seguro para `QA Lead`, `Developer` y `CI Runner`, cubriendo estados, errores recuperables, ayuda contextual y protección de datos.

**Alcance:**
- User Flow por rol con inicio, decisiones, confirmaciones y ramificaciones en [01-user-flow-by-role.md](01-user-flow-by-role.md).
- Matriz Empty, Loading, Success, Failed, Retryable y Token Expired.
- Cinco wireframes anotados, validaciones, mensajes, retries y timeouts en [02-wireframes-and-interaction-rules.md](02-wireframes-and-interaction-rules.md).
- Protección de JWT, `tenant_id` y SHA-256; ayuda contextual, accesibilidad y feedback.
- Diagrama consolidado en [diagrams/ux-user-flow-and-wireframes.svg](diagrams/ux-user-flow-and-wireframes.svg).

### Fuera del alcance
- Implementación final de frontend.
- Modificación de reglas de dominio, contrato API o aislamiento de datos.

### Criterios propuestos de aceptación
| ID    | Criterio propuesto de aceptación                                                                        |
|-------|---------------------------------------------------------------------------------------------------------|
| CA-01 | El flujo UX distingue los roles `QA Lead`, `Developer` y `CI Runner`.                                   |
| CA-02 | La matriz cubre estados vacío, carga, éxito, fallo, reintento y token expirado.                         |
| CA-03 | Los cinco wireframes incluyen mensajes, validaciones, retries, timeouts y ayuda contextual.             |
| CA-04 | JWT, `tenant_id`, SHA-256, accesibilidad y feedback quedan documentados y representados en el diagrama. |

### Desglose sugerido
- T-01 Diseñar el flujo por rol.
- T-02 Definir estados y transiciones.
- T-03 Elaborar wireframes y reglas de interacción.
- T-04 Revisar seguridad, accesibilidad y feedback.

### Condiciones para cerrar la entrega
El user flow, los cinco wireframes, la matriz de estados y el SVG están disponibles. La evidencia especifica comportamiento y contratos de interacción sin presentarse como frontend final.
