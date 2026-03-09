# ⚡ Actions (Acciones de Propósito Único)

## Propósito

Esta carpeta contiene las **Actions** (Single Action Classes). Una Action es una clase con **una sola responsabilidad**: ejecutar una operación específica y reutilizable.

## Responsabilidades

- Encapsular una acción concreta que pueda ser reutilizada en múltiples contextos.
- Reducir la complejidad de los Services extrayendo operaciones atómicas.
- Facilitar la composición de lógica compleja a partir de piezas simples.

## Convenciones de Nomenclatura

- **Formato verbal**: `VerboSustantivo` → Ejemplo: `SendWelcomeEmail.php`, `GenerateReport.php`
- **Namespace**: `App\Actions`
- Implementar un método público `execute()` o `__invoke()`.

## Relación con otras Capas

```
Service → Action (operación atómica)
Controller → Action (operaciones simples que no requieren Service)
```

## Ejemplo

```php
<?php

namespace App\Actions;

use App\Models\User;
use App\Notifications\WelcomeNotification;

class SendWelcomeEmail
{
    public function execute(User $user): void
    {
        $user->notify(new WelcomeNotification());
    }
}
```
