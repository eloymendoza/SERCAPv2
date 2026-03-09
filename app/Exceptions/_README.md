# 🚨 Exceptions (Excepciones Personalizadas)

## Propósito

Esta carpeta contiene las **Excepciones personalizadas** del dominio. Permiten manejar errores de negocio de forma **tipada y descriptiva**, diferenciándolos de errores genéricos del framework.

## Responsabilidades

- Representar errores específicos del dominio de negocio.
- Proveer mensajes de error claros y códigos HTTP apropiados.
- Permitir un manejo centralizado de errores en el `Handler`.

## Convenciones de Nomenclatura

- **Sufijo**: `Exception` → Ejemplo: `UserNotFoundException.php`, `InsufficientPermissionsException.php`
- **Namespace**: `App\Exceptions`

## Relación con otras Capas

```
Service lanza → Exception
Controller captura → Exception (o Handler global)
```

## Ejemplo

```php
<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ResourceNotFoundException extends Exception
{
    public function __construct(
        string $resource = 'Recurso',
        int $id = 0
    ) {
        parent::__construct("{$resource} con ID {$id} no encontrado.");
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'error'   => true,
            'message' => $this->getMessage(),
        ], 404);
    }
}
```
