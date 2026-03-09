# 🏛️ Models (Modelos Eloquent)

## Propósito

Esta carpeta contiene los **Modelos Eloquent** que representan las tablas de la base de datos. Un Model define la estructura, relaciones, casts, scopes y atributos accesibles de una entidad.

## Responsabilidades

- Representar una tabla de la base de datos.
- Definir relaciones (`hasMany`, `belongsTo`, `morphTo`, etc.).
- Definir casts, accessors y mutators.
- Definir scopes locales para consultas frecuentes.
- **NO** debe contener lógica de negocio compleja (eso va en Services).

## Convenciones de Nomenclatura

- **Singular en inglés**: `User.php`, `Document.php`, `Report.php`
- **Namespace**: `App\Models`
- La tabla asociada se infiere del nombre del modelo (plural snake_case).

## Relación con otras Capas

```
Repository → Model (Eloquent) → Base de Datos
Observer ← Model (eventos del ciclo de vida)
Policy ← Model (autorización)
```

## Ejemplo

```php
<?php

namespace App\Models;

use App\Enums\UserStatus;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    use SoftDeletes, HasSlug;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'status',
        'user_id',
    ];

    protected $casts = [
        'status' => UserStatus::class,
    ];

    // ─── Relaciones ─────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ─────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('status', UserStatus::Active);
    }
}
```
