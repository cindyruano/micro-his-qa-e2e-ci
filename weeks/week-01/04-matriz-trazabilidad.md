# 04 - Matriz de Trazabilidad QA

## Propósito

Esta matriz relaciona los casos de uso del HIS con el actor del [diagrama de casos de uso](./diagramas/imagenes/caso-uso.svg), la superficie Laravel, las pruebas del módulo QA, la evidencia esperada y el estado real del repositorio.

| ID    | Caso de uso                                | Actor              | Pruebas QA                                                                                         |
|-------|--------------------------------------------|--------------------|----------------------------------------------------------------------------------------------------|
| CU-01 | Autenticar usuario y gestionar sesión      | Usuario / Admin    | Unitarias: validación y claims. Integración: register/login/me/refresh/logout. E2E: flujo de login |
| CU-02 | Registrar paciente y admisión              | Recepción / Admin  | Unitarias: duplicados y estados. Integración: transacción/aislamiento. E2E cuando exista API/UI    |
| CU-03 | Consultar y actualizar expediente clínico  | Médico / Enfermera | Unitarias: policies/validación. Integración: historial/tenant. E2E cuando exista API/UI            |
| CU-04 | Gestionar orden y resultado de laboratorio | Técnico / Médico   | Unitarias: rangos/estados. Integración: muestra/resultado/alerta. E2E cuando exista API/UI         |
| CU-05 | Autorizar acceso por rol y tenant          | Admin              | Unitarias: policies/roles. Integración: asignación/revocación/tenant. E2E cuando exista API/UI     |


| ID    | Superficie del proyecto                                                    | Criterio de aceptación                                                                  |
|-------|----------------------------------------------------------------------------|-----------------------------------------------------------------------------------------|
| CU-01 | routes/api.php, AuthController, TenantMiddleware, JWT, User                | Respuesta 2xx con JWT válido; manejo de errores sin fuga de datos.                      |
| CU-02 | Patient, Admission, Bed, Ward, Doctor; rutas por crear                     | Alta sin duplicado, admisión consistente, manejo de cama no disponible y rollback       |
| CU-03 | MedicalRecord, SoapNote, Allergy, VitalSign, Prescription; rutas por crear | Acceso autorizado, autor y fecha persistidos, historial intacto y 403 sin permiso       |
| CU-04 | LabOrder, LabResult, Sample, LabTest, alertas; rutas por crear             | Transiciones válidas, resultado trazable, valores críticos identificados y acceso ajeno |
| CU-05 | User, Tenant, Spatie Permission, middleware role/permission                | Bloqueo de escalamiento de privilegios y aislamiento estricto por tenant.               |

## Trazabilidad de diagramas

| Diagrama         | Relación con QA                              | Fuente                                                     | Renderizado            |
|------------------|----------------------------------------------|------------------------------------------------------------|------------------------|
| Actores          | Define quién ejecuta o valida cada recorrido | [actores.puml](./diagramas/actores.puml)                   | [actores.svg]          |
| Alcance-contexto | Delimita HIS, pruebas, CI/CD y dependencias  | [alcance-contexto.puml](./diagramas/alcance-contexto.puml) | [alcance-contexto.svg] |
| Casos de uso     | Relaciona CU-01 a CU-05 con actores-permisos | [caso-uso.puml](./diagramas/caso-uso.puml)                 | [caso-uso.svg]         |
| Secuencia        | Detalla el flujo verificable de CU-01        | [secuencia.puml](./diagramas/secuencia.puml)               | [secuencia.svg]        |

## Reglas del pipeline

- Ejecutar calidad estática, unitarias, integración, contrato API y E2E.
- Usar datos deterministas, base aislada y tenant de prueba independiente.
- Verificar 400, 401, 403, 404, 422 y 2xx según el escenario.
- Bloquear el merge ante fallos críticos, migraciones rotas o acceso cross-tenant.
- Publicar JUnit, cobertura y evidencias sin JWT, contraseñas ni datos clínicos reales.
- No contar como E2E ejecutado un caso que todavía no tenga ruta funcional.
