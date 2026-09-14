# 01 - Actores del Módulo QA

## Propósito

Este documento identifica a quienes interactúan con el Sistema Hospitalario Integrado (HIS) y con el proceso de Quality Assurance (QA). El sistema bajo prueba es una aplicación Laravel multi-tenant: TenantMiddleware exige X-Tenant-ID, AuthController gestiona JWT y Spatie Laravel Permission proporciona el RBAC del guard api.

El diagrama asociado muestra la relación entre los actores, la aplicación HIS, la autenticación, los módulos clínicos y el runner de pruebas.

## Actores del sistema y de QA

| ID   | Actor                      | Tipo              | Participación en QA                                                                    | Estado   |
|------|----------------------------|-------------------|----------------------------------------------------------------------------------------|----------|
| A-01 | Usuario operativo          | Humano / primario | Ejecuta los recorridos funcionales de acuerdo con su rol clínico o administrativo.     | Vigente  |
| A-02 | Administrador del sistema  | Humano / primario | Verifica administración, autorización y aislamiento del tenant.                        | Vigente  |
| A-03 | Runner CI/CD               | Sistema / QA      | Ejecuta migraciones, seeders, pruebas PHPUnit, integración y E2E; conserva evidencias. | Vigente  |
| A-04 | Servicio de notificaciones | Sistema externo   | Dependencia prevista para alertas clínicas; se simula en pruebas.                      | Previsto |
| A-05 | Observabilidad             | Sistema externo   | Recibe logs y métricas sin exponer JWT ni datos clínicos.                              | Previsto |

## Roles operativos

Los roles están definidos en database/seeders/RoleSeeder.php:

| Rol             | Casos de uso relacionados | Acciones previstas                                                          |
|-----------------|---------------------------|-----------------------------------------------------------------------------|
| `Recepcionista` | CU-01, CU-02              | Autenticarse, registrar pacientes y gestionar admisiones.                   |
| `Médico`        | CU-01, CU-03, CU-04       | Consultar expediente, registrar notas SOAP y ordenar o validar laboratorio. |
| `Enfermera`     | CU-01, CU-03              | Consultar información autorizada y registrar signos vitales.                |
| `TecnicoLab`    | CU-01, CU-04              | Recibir muestras, registrar resultados y operar la cola de laboratorio.     |
| `Admin`         | CU-01, CU-05              | Administrar usuarios, roles y permisos dentro de su tenant.                 |

## Responsabilidades verificables

### A-01: Usuario operativo

- Envía X-Tenant-ID en las solicitudes.
- Usa POST /api/v1/auth/login, GET /api/v1/auth/me, POST /api/v1/auth/refresh y POST /api/v1/auth/logout.
- Solo accede a datos y operaciones autorizados por rol y tenant.

### A-02: Administrador

- Administra usuarios y autorización únicamente dentro del tenant autenticado.
- No puede obtener acceso cross-tenant modificando la cabecera de la petición.
- Debe recibir 403 ante operaciones sin permiso.

### A-03: Runner CI/CD

- Ejecuta con datos sintéticos y configuración aislada de testing.
- Revisa códigos HTTP, contratos JSON, persistencia, aislamiento tenant y evidencias E2E.
- Publica JUnit, cobertura, capturas y vídeos sin secretos.

## Matriz de responsabilidades QA

| Validación                  | Operativo         | Admin      | Runner CI/CD                          |
|-----------------------------|-------------------|------------|---------------------------------------|
| Login, refresh, me y logout | Ejecuta           | Ejecuta    | Automatiza                            |
| Pacientes y admisiones      | Ejecuta según rol | Supervisa  | Automatiza cuando exista API          |
| Expediente clínico          | Ejecuta según rol | Supervisa  | Automatiza cuando exista API          |
| Laboratorio                 | Ejecuta según rol | Supervisa  | Automatiza cuando exista API          |
| RBAC y aislamiento tenant   | Consume           | Administra | Verifica casos permitidos y denegados |
| Reportes y quality gates    | Consulta          | Consulta   | Publica y bloquea el merge            |

## Referencias

- [Diagrama de actores (SVG)](./diagramas/imagenes/actores.svg)
- [Fuente PlantUML](./diagramas/actores.puml)
- [Casos de uso](./03-casos-de-uso.md)
- [Matriz de trazabilidad](./04-matriz-trazabilidad.md)
