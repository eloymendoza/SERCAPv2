# 🏷️ Enums (Enumeraciones Tipadas)

## Propósito

Esta carpeta contiene los **Enums** nativos de PHP 8.1+. Un Enum define un conjunto **finito y tipado** de valores posibles para un concepto del dominio.

## Responsabilidades

- Reemplazar constantes mágicas y strings sueltos por valores tipados seguros.
- Definir estados, roles, tipos y categorías del sistema.
- Proveer métodos auxiliares para labels, colores, descripciones, etc.

## Convenciones de Nomenclatura

- **Singular**: `UserStatus.php`, `DocumentType.php`, `Priority.php`
- **Namespace**: `App\Enums`
- Usar `enum` backed con `string` o `int` según convenga.

## Relación con otras Capas

```
Model (casting) ← Enum
Blade (labels)  ← Enum
Service (lógica) ← Enum
```


## 🟢 Cuándo usar
- Para definir estados, tipos o categorías predefinidas e inmutables (ej. Estados de Solicitud, Roles).

## 🔴 Cuándo NO usar
- No los uses para datos dinámicos que el usuario pueda crear o editar en la base de datos.

## Ejemplo

```php
<?php

namespace App\Enums;

enum UserStatus: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
    case Blocked  = 'blocked';

    public function label(): string
    {
        return match ($this) {
            self::Active   => 'Activo',
            self::Inactive => 'Inactivo',
            self::Blocked  => 'Bloqueado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active   => 'green',
            self::Inactive => 'gray',
            self::Blocked  => 'red',
        };
    }
}
```
