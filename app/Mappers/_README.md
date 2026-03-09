# 🔄 Mappers (Capa de Transformación)

## Propósito

Esta carpeta contiene los **Mappers**. Un Mapper se encarga de **transformar** datos entre diferentes formatos: DTO → array para Eloquent, Model → DTO para respuestas, etc.

## Responsabilidades

- Convertir un DTO en un array compatible con los campos del modelo Eloquent.
- Convertir un Model Eloquent en un DTO cuando sea necesario.
- Centralizar la lógica de mapeo para evitar duplicación en Services.

## Convenciones de Nomenclatura

- **Sufijo**: `Mapper` → Ejemplo: `UserMapper.php`, `ReportMapper.php`
- **Namespace**: `App\Mappers`
- Usar métodos estáticos (`toArray`, `toDTO`) para simplicidad.

## Relación con otras Capas

```
Service usa Mapper::toArray(DTO) → pasa array al Repository
Repository retorna Model → Mapper::toDTO(Model) → Service retorna DTO
```

## Ejemplo

```php
<?php

namespace App\Mappers;

use App\DTOs\UserDTO;
use App\Models\User;

class UserMapper
{
    public static function toArray(UserDTO $dto): array
    {
        return [
            'nombre'    => $dto->name,
            'correo'    => $dto->email,
            'telefono'  => $dto->phone,
        ];
    }

    public static function toDTO(User $user): UserDTO
    {
        return new UserDTO(
            name: $user->nombre,
            email: $user->correo,
            phone: $user->telefono,
        );
    }
}
```
