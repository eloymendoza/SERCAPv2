# 📡 Events (Eventos del Sistema)

## Propósito

Esta carpeta contiene los **Eventos** del sistema. Un Event representa algo que **ya ocurrió** en la aplicación y permite notificar a otros componentes de forma desacoplada.

## Responsabilidades

- Representar hechos del dominio (ej: "Un usuario se registró").
- Transportar datos relevantes del evento a los Listeners.
- Habilitar comunicación desacoplada entre módulos.

## Convenciones de Nomenclatura

- **Tiempo pasado**: `UserRegistered.php`, `OrderPlaced.php`, `DocumentUploaded.php`
- **Namespace**: `App\Events`
- Emparejados con uno o más Listeners en `App\Listeners`.

## Relación con otras Capas

```
Service/Controller → dispara Event → Listener(s) lo manejan
```

## Ejemplo

```php
<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserRegistered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User $user
    ) {}
}
```
