# Arquitectura del Proyecto: Monolito Modular Pragmático

Este proyecto sigue una arquitectura de **Monolito Modular** (Domain-Driven Laravel). El objetivo principal es separar estrictamente la lógica de negocio (Dominio) de los protocolos de entrega (HTTP/Console) y de las integraciones técnicas externas (Infraestructura).

---

## 🏗️ Estructura de Capas Principales

### 1. `app/Domain/` (Núcleo de Negocio)
Contiene toda la lógica de negocio agrupada por **Límites de Contexto** (ej. Requisiciones, Autenticacion). Cada dominio es autónomo e independiente.
- **Regla de Oro:** Un dominio NO debe alterar datos ni invocar lógica de otro dominio directamente. La mutación cruzada debe ocurrir mediante DTOs, interfaces públicas o Eventos.

### 2. `app/App/` (Capa de Presentación / Entrega)
Contiene los controladores, rutas (conceptualmente), middlewares, form requests y resources de la API HTTP.
- **Api/**: Controladores, Middlewares, Form Requests y Resources HTTP.
- **Console/**: Comandos de consola de Artisan.
- **Regla de Oro:** Esta capa NO contiene lógica de negocio (ni un solo `if` validando reglas de empresa). Su trabajo es: Recibir HTTP -> Validar Entrada -> Llamar al Dominio -> Retornar HTTP JSON.

### 3. `app/Infrastructure/` (Integraciones Técnicas)
Contiene las implementaciones específicas de infraestructura que no son el núcleo de nuestro sistema (ej. Clientes de consumo de API como Django, wrappers de AWS S3, etc.).

---

## 🌳 Árbol Representativo del Proyecto

```text
app/
├── App/                     # Capa de Presentación (HTTP/Console)
│   ├── Api/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   ├── Requests/
│   │   └── Resources/
│   └── Console/
├── Domain/                  # Núcleo de Negocio (Límites de Contexto)
│   ├── Autenticacion/       # Módulo de ejemplo
│   │   ├── Actions/
│   │   ├── DTOs/
│   │   ├── Exceptions/
│   │   ├── Mappers/
│   │   ├── Models/
│   │   └── Services/
│   └── Requisiciones/       # Módulo de ejemplo
│       ├── Actions/
│       ├── DTOs/
│       └── ...
├── Infrastructure/          # Integraciones Técnicas Externas
│   └── Clients/
│       └── DjangoAuthClient.php
├── Exceptions/              # Excepciones Globales/Técnicas
├── Logging/                 # Configuración de Logs Transversales
├── Providers/               # Proveedores del Framework
└── Traits/                  # Traits Globales
```

---

## 🛠️ Creación de Nuevos Dominios

Para mantener la consistencia arquitectónica, existe un comando personalizado de Artisan que genera toda la estructura base de un nuevo Dominio. Nunca crees las carpetas manualmente.

Ejecuta en tu terminal:
```bash
php artisan make:domain NombreDelDominio
```
*(Ej. `php artisan make:domain Finanzas`)*

Esto generará automáticamente la carpeta `app/Domain/Finanzas` con sus 14 subdirectorios estandarizados (`Actions`, `DTOs`, `Models`, etc.) y sus respectivos archivos `.gitkeep` para garantizar que Git los rastree.

---

## 📁 Glosario y Reglas de Artefactos de Dominio

### Models (`Models/`)
Representan tablas de base de datos y sus relaciones (Active Record).
- **🟢 Cuándo usar:** Para consultas, relaciones (Lectura) y escritura de datos exclusivos del dominio.
- **🔴 Cuándo NO usar:** No escribas lógica de negocio, validaciones complejas o formateo de salida (Resource) dentro del modelo. No alterar modelos de otro dominio.

### DTOs (`DTOs/` - Data Transfer Objects)
Clases inmutables tipadas que transportan datos entre capas.
- **🟢 Cuándo usar:** Para enviar/recibir cargas útiles (payloads) complejas entre Controladores y Servicios, o entre dos Dominios distintos.
- **🔴 Cuándo NO usar:** No deben tener comportamiento, lógica, constructores complejos ni métodos de guardado a base de datos. Solo propiedades tipadas puras.

### Mappers (`Mappers/`)
Clases responsables de transformar estructuras de datos.
- **🟢 Cuándo usar:** Para transformar Arrays asociativos (ej. datos de una API externa) a DTOs, o Modelos de Eloquent a DTOs.
- **🔴 Cuándo NO usar:** No deben hacer consultas a base de datos ni invocar lógicas externas. Su función es 100% transformación pura de entrada por salida.

### Services (`Services/`)
Orquestadores de reglas de negocio pesadas.
- **🟢 Cuándo usar:** Cuando un proceso requiere transacciones, múltiples llamadas a otros servicios, validación de reglas de negocio y guardado concurrente (ej. `ProcesarCandidatoService`).
- **🔴 Cuándo NO usar:** Si la lógica solo actualiza un estado, usa un `Action`. Evita que un Servicio se convierta en un "Dios" con miles de líneas.

### Actions (`Actions/`)
Clases con un solo método público (`execute()`) o mágico (`__invoke()`).
- **🟢 Cuándo usar:** Para encapsular una regla de negocio unitaria y específica (ej. `AprobarRequisicionAction`, `CalcularSalarioAction`). Fomenta la reutilización extrema.
- **🔴 Cuándo NO usar:** Para orquestar flujos enormes que abarcan múltiples acciones secundarias (para eso está el `Service`).

### Enums (`Enums/`)
Enumeraciones nativas (Backed Enums de PHP 8.1+).
- **🟢 Cuándo usar:** Para estados, categorías, roles, tipos fijos en la base de datos (ej. `VacanteEstado::ACTIVA`).
- **🔴 Cuándo NO usar:** Si los valores pueden ser editados o creados por un administrador en un panel (en ese caso es una tabla de catálogo en BD).

### Exceptions (`Exceptions/`)
Excepciones tipadas y nominales.
- **🟢 Cuándo usar:** Para controlar violaciones a las reglas del negocio de ese dominio (ej. `RequisicionSinPresupuestoException`).
- **🔴 Cuándo NO usar:** No las uses para errores técnicos genéricos (ej. fallo de conexión a BD) ni para validaciones simples de formularios (eso se maneja en los `Requests`).

### Events & Listeners (`Events/`, `Listeners/`)
Clases de eventos internos de Laravel.
- **🟢 Cuándo usar:** Para reaccionar a sucesos sin bloquear ni acoplar el flujo principal (ej. Disparar `RequisicionAprobadaEvent` para que otro dominio envíe correos).
- **🔴 Cuándo NO usar:** Para flujos lineales obligatorios y críticos que deben ser transaccionales (si falla el listener, la transacción original ya se cometió).

### Jobs (`Jobs/`)
Procesos asíncronos encolables.
- **🟢 Cuándo usar:** Tareas lentas que el usuario no necesita esperar (ej. Generación de reportes PDF pesados, comunicación masiva de correo).
- **🔴 Cuándo NO usar:** Lógica crítica inmediata donde el cliente HTTP dependa directamente de la respuesta final del Job.

### Mail & Notifications (`Mail/`, `Notifications/`)
Clases de envío de mensajes (Correo, Slack, SMS).
- **🟢 Cuándo usar:** Correos electrónicos específicos de este dominio.
- **🔴 Cuándo NO usar:** Para inyectar lógica de negocio compleja; solo deben recibir datos limpios (DTOs o Modelos) e hidratar las vistas (Blade).

### Policies (`Policies/`)
Lógica de autorización vinculada a un modelo específico.
- **🟢 Cuándo usar:** Para decidir si un usuario en sesión tiene permisos para ver, editar o eliminar un registro en particular (ej. `RequisicionPolicy`).
- **🔴 Cuándo NO usar:** Para validar que los datos enviados en un formulario sean correctos (eso es de los `Requests`), ni para lógica de negocio genérica.

### Traits (`Traits/`)
Funciones reusables vía herencia horizontal (Mixins).
- **🟢 Cuándo usar:** Lógica repetitiva muy atada a componentes del framework que comparten varios modelos de un mismo dominio (ej. `HasEstadoAutorizacion`).
- **🔴 Cuándo NO usar:** Como un sustituto de herencia o para evadir la Inyección de Dependencias. Un Trait con 500 líneas es un Servicio disfrazado y un "code smell".