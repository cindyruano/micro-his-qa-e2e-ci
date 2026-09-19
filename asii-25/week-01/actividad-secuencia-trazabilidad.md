# ASII-25 — Actividad, secuencia y trazabilidad

## 1. Propósito

Definir la secuencia de validación de calidad, la actividad de pruebas E2E en CI y la matriz que conecta cada caso de uso con sus actividades, mensajes y resultados verificables.

## 2. Diagrama UML de actividad

![Diagrama UML de actividad](diagrams/actividad.svg)

Flujo resumido: el PR dispara el pipeline; CI prepara el entorno y ejecuta las suites; el runner valida la API HIS, el tenant y RBAC; se calculan métricas; el Quality Gate decide **Pasa** o **Falla**. Si pasa, se publican evidencias y se habilita el PR. Si falla, se publican fallos y se bloquea el merge.

## 3. Participantes de secuencia

- `Developer`: propone cambios y corrige fallos.
- `GitHub / PR`: recibe el cambio y muestra el estado de las verificaciones.
- `CI Pipeline`: prepara, coordina y reporta la ejecución.
- `E2E Runner`: ejecuta las suites y captura evidencias.
- `HIS API / Tenant`: responde bajo JWT, `X-Tenant-ID` y RBAC.
- `Quality Gate`: compara métricas contra umbrales.
- `QA Lead`: revisa resultados y autoriza la versión cuando corresponde.

## 4. Diagrama UML de secuencia

![Diagrama UML de secuencia](diagrams/secuencia.svg)

## 5. Matriz de trazabilidad

| Caso de uso                             | Actor            | Actividades relacionadas                                   | Mensajes de secuencia                                          | Resultado trazable                       |
|-----------------------------------------|------------------|------------------------------------------------------------|----------------------------------------------------------------|------------------------------------------|
| UC-01 Registrar ejecución de calidad    | QA Lead          | Preparar versión, entorno y datos                          | `SEQ-01` registrar commit y entorno                            | Ejecución identificada y auditable       |
| UC-02 Ejecutar suite E2E crítica        | CI Pipeline      | Instalar dependencias, ejecutar suite y guardar evidencias | `SEQ-02` disparar runner, `SEQ-03` devolver resultados         | Resultados reproducibles por suite       |
| UC-03 Validar autenticación y sesión    | HIS API / Tenant | Validar JWT, login, refresh y expiración                   | `SEQ-04` solicitar autenticación, `SEQ-05` responder `200/401` | Sesión válida o rechazo esperado         |
| UC-04 Verificar aislamiento multitenant | HIS API / Tenant | Enviar tenant válido y ajeno                               | `SEQ-06` consultar con `X-Tenant-ID`, `SEQ-07` rechazar cruce  | Sin acceso cross-tenant                  |
| UC-05 Verificar autorización RBAC       | HIS API / Tenant | Ejecutar acciones permitidas y denegadas                   | `SEQ-08` solicitar recurso, `SEQ-09` responder `200/403`       | Permisos respetados                      |
| UC-06 Calcular métricas de calidad      | CI Pipeline      | Consolidar cobertura, fallos y duración                    | `SEQ-10` publicar métricas                                     | Reporte cuantificable                    |
| UC-07 Evaluar Quality Gate              | Quality Gate     | Comparar resultados con umbrales                           | `SEQ-11` devolver `PASA/FALLA`                                 | Decisión de calidad registrada           |
| UC-08 Bloquear o aprobar Pull Request   | GitHub / PR      | Aplicar estado requerido del pipeline                      | `SEQ-12` actualizar check del PR                               | Merge bloqueado o habilitado             |
| UC-09 Auditar ejecución y artefactos    | QA Lead          | Revisar logs, evidencias y metadatos                       | `SEQ-13` consultar artefactos                                  | Auditoría sin secretos ni datos clínicos |
| UC-10 Reportar cobertura y fallos       | CI Pipeline      | Generar resumen y enlazar evidencias                       | `SEQ-14` publicar reporte                                      | Hallazgos accionables para corrección    |

## 6. Criterios de cierre

- El flujo principal y las excepciones críticas tienen trazabilidad.
- El Quality Gate distingue aprobación y bloqueo.
- Las respuestas `200/201`, `401`, `403`, `404` y `422` se verifican cuando aplican.
- Los artefactos son reproducibles, aislados y libres de secretos reales.
