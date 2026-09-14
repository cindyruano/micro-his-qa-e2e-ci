# Declaracion de IA

La entrega fue construida como un micro-monolito PHP vanilla independiente del Laravel del repositorio principal. Se reutilizo el dominio documental de la Semana 2: planificación, ejecución E2E, evidencia y quality gate.

Decisiones principales:

- separar las cuatro capas solicitadas;
- usar interfaces como puertos para inversión de dependencias;
- mantener PDO únicamente en Persistence;
- probar con SQLite en memoria y dobles deterministas;
- conservar evidencia sin secretos ni datos clínicos.

La ejecución E2E real queda representada por `E2ERunner`; su doble permite verificar el contrato sin acoplar el ejemplo a un navegador o framework.
