# 📤 Resources (API Resources - Formato de Respuesta)

## Propósito

Esta carpeta contiene los **API Resources**. Un Resource transforma modelos Eloquent en respuestas JSON **estructuradas y consistentes**, controlando exactamente qué datos se exponen.

## Responsabilidades

- Formatear la salida JSON de los endpoints API.
- Ocultar campos sensibles (passwords, tokens internos).
- Incluir relaciones, metadatos y links HATEOAS.
- Manejar colecciones con paginación.

## Convenciones de Nomenclatura

- **Sufijo**: `Resource` → Ejemplo: `UserResource.php`, `DocumentResource.php`
- **Colecciones**: `UserCollection.php` (opcional, para personalizar paginación).
- **Namespace**: `App\Http\Resources`

## Relación con otras Capas

```
Controller retorna → Resource::make($model) → JSON Response
```


## 🟢 Cuándo usar
- Para transformar modelos Eloquent o DTOs en estructuras JSON predecibles para el frontend.

## 🔴 Cuándo NO usar
- No ocultes lógica de negocio aquí; solo formatea (fechas, booleanos, anidaciones) para la presentación.

## Ejemplo

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'status'     => $this->status?->label(),
            'created_at' => $this->created_at->toIso8601String(),
            'documents'  => DocumentResource::collection(
                $this->whenLoaded('documents')
            ),
        ];
    }
}
```
