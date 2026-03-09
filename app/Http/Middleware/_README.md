# 🚧 Middleware (Filtros de Request/Response)

## Propósito

Esta carpeta contiene los **Middleware** personalizados. Un Middleware inspecciona y/o modifica las peticiones HTTP **antes** de que lleguen al Controller, o las respuestas **después** de que salen.

## Responsabilidades

- Verificar autenticación, roles y permisos.
- Registrar logs de actividad.
- Manejar CORS, rate limiting y headers de seguridad.
- Transformar o sanitizar datos de entrada.

## Convenciones de Nomenclatura

- **Descriptivo**: `CheckRole.php`, `LogActivity.php`, `ForceHttps.php`
- **Namespace**: `App\Http\Middleware`
- Registrar en `bootstrap/app.php` o en las rutas directamente.

## Relación con otras Capas

```
Request → Middleware (filtro) → Controller
Controller → Middleware (post-filtro) → Response
```

## Ejemplo

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! $request->user()?->hasRole($role)) {
            abort(403, 'No tienes permisos para acceder a este recurso.');
        }

        return $next($request);
    }
}
```
