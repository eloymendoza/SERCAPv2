# 🎮 Controllers (Controladores HTTP)

## Propósito

Esta carpeta contiene los **Controladores** de la aplicación. Un Controller recibe las peticiones HTTP, **delega** la lógica al Service correspondiente y retorna la respuesta adecuada.

## Responsabilidades

- Recibir y validar requests (delegando a FormRequests).
- Llamar al Service correspondiente.
- Retornar vistas (Blade) o respuestas JSON (API Resources).
- Un Controller **NO** debe contener lógica de negocio ni consultas directas a la BD.

## Convenciones de Nomenclatura

- **Sufijo**: `Controller` → Ejemplo: `UserController.php`, `ReportController.php`
- **Namespace**: `App\Http\Controllers`
- Usar **Resource Controllers** cuando aplique (`index`, `create`, `store`, `show`, `edit`, `update`, `destroy`).

## Relación con otras Capas

```
Route → Middleware → FormRequest → Controller → Service → Response
```

## Ejemplo

```php
<?php

namespace App\Http\Controllers;

use App\DTOs\UserDTO;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function store(StoreUserRequest $request): UserResource
    {
        $dto = UserDTO::fromRequest($request);
        $user = $this->userService->store($dto);

        return new UserResource($user);
    }

    public function show(int $id): UserResource
    {
        $user = $this->userService->findById($id);

        return new UserResource($user);
    }
}
```
