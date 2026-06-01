# 📦 Services (Capa de Lógica de Negocio)

## Propósito

Esta carpeta contiene los **Servicios** del sistema. Un Service encapsula la **lógica de negocio** de la aplicación, actuando como intermediario entre los Controllers y los Repositories.

## Responsabilidades

- Orquestar operaciones complejas que involucren múltiples modelos o repositorios.
- Ejecutar validaciones de negocio (no de formulario).
- Manejar transacciones de base de datos.
- Coordinar el uso de DTOs, Mappers y Repositories.

## Convenciones de Nomenclatura

- **Sufijo**: `Service` → Ejemplo: `UserService.php`, `ReportService.php`
- **Namespace**: `App\Services`
- Un service por modelo/entidad principal.

## Relación con otras Capas

```
Controller → Service → Repository → Model
                ↑
              DTO / Mapper
```


## 🟢 Cuándo usar
- Para albergar toda la lógica de negocio, reglas empresariales, llamadas a base de datos y coordinación del flujo.

## 🔴 Cuándo NO usar
- No les inyectes dependencias de presentación (`Request`, `Response`) ni los uses para devolver respuestas JSON directamente.

## Ejemplo

```php
<?php

namespace App\Services;

use App\DTOs\UserDTO;
use App\Repositories\UserRepository;

class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

    public function store(UserDTO $dto): User
    {
        return $this->userRepository->create($dto->toArray());
    }

    public function findById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }
}
```
