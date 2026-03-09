# 👂 Listeners (Manejadores de Eventos)

## Propósito

Esta carpeta contiene los **Listeners**. Un Listener **reacciona** a un Event ejecutando una acción específica, como enviar notificaciones, registrar logs o actualizar datos.

## Responsabilidades

- Manejar un evento específico ejecutando la lógica correspondiente.
- Mantener la lógica reactiva separada de la lógica principal del Service.
- Opcionalmente ejecutarse en cola (queue) para operaciones pesadas.

## Convenciones de Nomenclatura

- **Formato verbal**: `SendWelcomeEmail.php`, `LogUserActivity.php`, `NotifyAdmins.php`
- **Namespace**: `App\Listeners`
- Implementar el método `handle(Event $event)`.

## Relación con otras Capas

```
Event → Listener → (envía Notification, llama Action, etc.)
```

## Ejemplo

```php
<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Notifications\WelcomeNotification;

class SendWelcomeEmail
{
    public function handle(UserRegistered $event): void
    {
        $event->user->notify(new WelcomeNotification());
    }
}
```
