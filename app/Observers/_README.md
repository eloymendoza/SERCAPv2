# 👁️ Observers (Observadores de Modelo)

## Propósito

Esta carpeta contiene los **Observers**. Un Observer escucha los **eventos del ciclo de vida** de un modelo Eloquent (creating, created, updating, deleted, etc.) y ejecuta lógica adicional de forma desacoplada.

## Responsabilidades

- Ejecutar acciones automáticas al crear, actualizar o eliminar registros.
- Mantener limpio el modelo al extraer lógica de eventos.
- Registrar auditoría, enviar notificaciones, actualizar campos calculados, etc.

## Convenciones de Nomenclatura

- **Sufijo**: `Observer` → Ejemplo: `UserObserver.php`, `DocumentObserver.php`
- **Namespace**: `App\Observers`
- Registrar en `AppServiceProvider` o usar el atributo `#[ObservedBy]`.

## Relación con otras Capas

```
Model (evento) → Observer → (acciones secundarias)
```

## Ejemplo

```php
<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Str;

class UserObserver
{
    public function creating(User $user): void
    {
        $user->uuid = Str::uuid();
    }

    public function deleted(User $user): void
    {
        // Limpiar recursos asociados
        $user->documents()->delete();
    }
}
```
