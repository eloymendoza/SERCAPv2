# 🛡️ Policies (Políticas de Autorización)

## Propósito

Esta carpeta contiene las **Policies** de autorización. Una Policy centraliza la lógica de **permisos** sobre un modelo, determinando si un usuario puede realizar cierta acción (ver, crear, editar, eliminar).

## Responsabilidades

- Autorizar acciones del usuario sobre recursos específicos.
- Centralizar reglas de acceso por modelo/entidad.
- Integrarse con Gates y middleware de autorización de Laravel.

## Convenciones de Nomenclatura

- **Sufijo**: `Policy` → Ejemplo: `UserPolicy.php`, `DocumentPolicy.php`
- **Namespace**: `App\Policies`
- Laravel auto-descubre policies si siguen la convención `ModelPolicy`.

## Relación con otras Capas

```
Controller → $this->authorize('action', $model) → Policy
Blade → @can('action', $model) → Policy
```

## Ejemplo

```php
<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function view(User $user, Document $document): bool
    {
        return $user->id === $document->user_id
            || $user->hasRole('admin');
    }

    public function update(User $user, Document $document): bool
    {
        return $user->id === $document->user_id;
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->hasRole('admin');
    }
}
```
