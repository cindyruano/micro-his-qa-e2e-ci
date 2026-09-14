# 03 - Casos de Uso QA

## Propósito

Estos casos convierten el dominio del HIS en recorridos verificables por QA. El [diagrama de casos de uso](./diagramas/imagenes/caso-uso.svg) muestra los actores, el runner CI/CD y las validaciones comunes de tenant y permiso.

`CU-01` puede probarse contra las rutas actuales. CU-02 a CU-05 son objetivos funcionales: sus modelos y migraciones existen, pero sus controladores y rutas aún deben implementarse antes de ejecutar E2E.

## CU-01: Autenticar usuario y gestionar sesión

| Elemento       | Definición                                                                                                      |
|----------------|-----------------------------------------------------------------------------------------------------------------|
| Actor          | Usuario operativo o Admin                                                                                       |
| Precondiciones | Tenant activo identificado con X-Tenant-ID; usuario registrado para login.                                      |
| Superficie     | TenantMiddleware, AuthController, JWT, routes/api.php.                                                          |
| Rutas          | POST /api/v1/auth/register, POST /api/v1/auth/login, GET /api/v1/auth/me, POST /api/v1/auth/refresh, POST /api/v1/auth/logout. |
| QA             | Unitarias de validación/claims; integración HTTP; E2E de autenticación.                                         |
| Estado         | Implementado y prioritario.                                                                                     |

### Flujo

1. El cliente envía X-Tenant-ID y las credenciales a POST /api/auth/login.
2. TenantMiddleware resuelve el tenant y lo guarda en la solicitud.
3. AuthController busca el usuario por tenant_id y correo, valida la contraseña y emite un JWT.
4. El cliente consulta GET /api/auth/me con Authorization: Bearer.
5. El cliente puede renovar el token o cerrar sesión.

### Excepciones QA

- Sin cabecera de tenant: `400`
- Tenant inexistente: `404`
- Campos inválidos o credenciales incorrectas: error de validación sin token
- Token ausente, inválido o expirado: `401`
- Tenant del refresh incompatible con el usuario del token: `403`

## CU-02: Registrar paciente y admisión

| Elemento       | Definición                                                                     |
|----------------|--------------------------------------------------------------------------------|
| Actor          | Recepcionista; Admin según permiso                                             |
| Precondiciones | Sesión válida, tenant activo y permiso de pacientes/admisiones.                |
| Superficie     | Patient, Admission, Bed, Ward, Doctor y migraciones de admisión.               |
| QA             | Validación de duplicados, integración transaccional, aislamiento y E2E futuro. |
| Estado         | Pendiente de controlador y rutas.                                              |

### Flujo esperado

Buscar o registrar paciente, seleccionar médico y recurso, crear la admisión y confirmar la transacción. QA debe comprobar duplicados, datos inválidos, cama no disponible, tenant ajeno y rollback sin registros parciales.

## CU-03: Consultar y actualizar expediente clínico

| Elemento       | Definición                                                           |
|----------------|----------------------------------------------------------------------|
| Actor          | Médico; Enfermera según permiso                                      |
| Precondiciones | Sesión válida, paciente del tenant y expediente disponible.          |
| Superficie     | MedicalRecord, SoapNote, Allergy, VitalSign, Prescription.           |
| QA             | Autorización, historial, validación y aislamiento de datos clínicos. |
| Estado         | Pendiente de controlador y rutas.                                    |

### Flujo esperado

Localizar paciente, consultar expediente y alergias, registrar nota SOAP o signos vitales y conservar autor, fecha e historial previo. QA debe comprobar paciente inexistente, tenant ajeno, campos obligatorios, sesión expirada y escritura sin permiso (403).

## CU-04: Gestionar orden y resultado de laboratorio

| Elemento       | Definición                                               |
|----------------|----------------------------------------------------------|
| Actor          | TecnicoLab; Médico para ordenar o validar                |
| Precondiciones | Orden asociada a paciente, expediente y tenant.          |
| Superficie     | LabOrder, LabResult, Sample, LabTest y alertas críticas. |
| QA             | Estados, muestras, rangos, trazabilidad y alertas.       |
| Estado         | Pendiente de controlador y rutas.                        |

### Flujo esperado

Consultar orden, recibir o descartar muestra, registrar resultado, calcular valores anormales/críticos y validar sin saltar estados. QA debe comprobar orden cerrada, muestra insuficiente, valor inválido, permiso insuficiente y tenant ajeno.

## CU-05: Autorizar acceso por rol y tenant

| Elemento       | Definición                                                                        |
|----------------|-----------------------------------------------------------------------------------|
| Actor          | Admin                                                                             |
| Precondiciones | Administrador autenticado dentro del tenant objetivo.                             |
| Superficie     | User, Tenant, Spatie Permission y middleware role/permission.                     |
| QA             | Asignación, revocación, policies, cache de permisos y prevención de escalamiento. |
| Estado         | Roles sembrados; gestión y rutas pendientes.                                      |

### Flujo esperado

Consultar usuario del tenant, asignar o revocar un rol válido y verificar el efecto en la siguiente petición. QA debe comprobar rol inexistente, usuario de otro tenant, administrador sin permiso, último administrador y acceso cross-tenant.

## Cobertura mínima QA

Cada caso habilitado requiere un happy path y al menos tres excepciones. Un caso sin controlador y ruta funcional se mantiene como `Pendiente` y no cuenta como cobertura E2E ejecutada.

## Referencias

- [Diagrama de casos de uso (SVG)](./diagramas/imagenes/caso-uso.svg)
- [Fuente PlantUML](./diagramas/caso-uso.puml)
- [Diagrama de secuencia del CU-01 (SVG)](./diagramas/imagenes/secuencia.svg)
- [Fuente PlantUML de secuencia](./diagramas/secuencia.puml)
- [Matriz de trazabilidad](./04-matriz-trazabilidad.md)
