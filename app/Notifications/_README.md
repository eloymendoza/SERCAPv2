# 🔔 Notifications (Notificaciones)

## Propósito

Esta carpeta contiene las **Notificaciones** del sistema. Una Notification envía mensajes a los usuarios a través de uno o múltiples canales: email, SMS, Slack, base de datos, etc.

## Responsabilidades

- Definir el contenido y formato de cada notificación.
- Especificar los canales de envío (`mail`, `database`, `slack`, etc.).
- Construir mensajes de email con `MailMessage` o vistas personalizadas.

## Convenciones de Nomenclatura

- **Sufijo**: `Notification` → Ejemplo: `WelcomeNotification.php`, `OrderShippedNotification.php`
- **Namespace**: `App\Notifications`

## Relación con otras Capas

```
Listener / Service → Notification → Canal(es) de envío
```


## 🟢 Cuándo usar
- Para alertar a los usuarios a través de canales específicos (Mail, Database, SMS, Slack).

## 🔴 Cuándo NO usar
- No uses Notifications si el sistema en sí debe reaccionar de forma interna (para eso usa Events/Listeners).

## Ejemplo

```php
<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WelcomeNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('¡Bienvenido a SERCAP!')
            ->greeting("Hola, {$notifiable->name}")
            ->line('Tu cuenta ha sido creada exitosamente.')
            ->action('Iniciar Sesión', url('/login'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Bienvenido al sistema SERCAP.',
        ];
    }
}
```
