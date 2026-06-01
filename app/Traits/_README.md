# 🧩 Traits (Comportamientos Reutilizables)

## Propósito

Esta carpeta contiene los **Traits** de la aplicación. Un Trait encapsula **comportamiento reutilizable** que puede ser compartido entre múltiples clases (Models, Controllers, Services, etc.).

## Responsabilidades

- Compartir funcionalidad común entre clases que no comparten herencia.
- Evitar duplicación de código en Models (filtros, scopes, auditoría).
- Proveer métodos utilitarios transversales.

## Convenciones de Nomenclatura

- **Prefijo descriptivo**: `HasSlug.php`, `Auditable.php`, `Filterable.php`
- **Namespace**: `App\Traits`

## Relación con otras Capas

```
Model      ← use Trait
Controller ← use Trait
Service    ← use Trait
```


## 🟢 Cuándo usar
- Para reutilizar código genérico transversal que no depende de la herencia (ej. `HandlesProcess`, `HasUuids`).

## 🔴 Cuándo NO usar
- No los uses como 'basureros' de funciones que deberían pertenecer a una clase abstracta o a un servicio específico.

## Ejemplo

```php
<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            $model->slug = Str::slug($model->{$model->slugSource});
        });
    }

    public function getSlugSourceAttribute(): string
    {
        return $this->slugSource ?? 'name';
    }
}
```
