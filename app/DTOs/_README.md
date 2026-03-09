# 📋 DTOs (Data Transfer Objects)

## Propósito

Esta carpeta contiene los **DTOs** (Data Transfer Objects). Un DTO es un objeto **inmutable** cuya única responsabilidad es transportar datos entre capas de la aplicación (Controller → Service → Repository).

## Responsabilidades

- Definir una estructura tipada e inmutable para los datos de entrada.
- Eliminar la dependencia directa de `Request` en los Services.
- Servir como contrato de datos entre capas.

## Convenciones de Nomenclatura

- **Sufijo**: `DTO` → Ejemplo: `UserDTO.php`, `CreateReportDTO.php`
- **Namespace**: `App\DTOs`
- Usar `readonly` properties de PHP 8.2+.
- Incluir un método estático `fromRequest()` para crear el DTO desde un FormRequest.

## Relación con otras Capas

```
FormRequest → DTO::fromRequest() → Service → Mapper::toArray(DTO)
```

## Ejemplo

```php
<?php

namespace App\DTOs;

use App\Http\Requests\StoreUserRequest;

class UserDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $phone = null,
    ) {}

    public static function fromRequest(StoreUserRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            phone: $request->validated('phone'),
        );
    }

    public function toArray(): array
    {
        return [
            'name'  => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
        ];
    }
}
```
