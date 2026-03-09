# 📜 Contracts (Interfaces y Contratos)

## Propósito

Esta carpeta contiene las **Interfaces** (contratos) de la aplicación. Un Contract define el **comportamiento esperado** de una clase sin especificar cómo se implementa, habilitando la inversión de dependencias (SOLID - D).

## Responsabilidades

- Definir contratos que los Repositories, Services u otras clases deben cumplir.
- Permitir intercambiar implementaciones (ej: cambiar de Eloquent a API externa).
- Facilitar el testing con mocks/stubs.

## Convenciones de Nomenclatura

- **Prefijo/Sufijo descriptivo**: `UserRepositoryInterface.php`, `PaymentGatewayContract.php`
- **Namespace**: `App\Contracts`
- Registrar bindings en `AppServiceProvider`.

## Relación con otras Capas

```
Contract (Interface) ← implementada por → Repository / Service
Service depende de → Contract (no de la implementación concreta)
AppServiceProvider → bind(Contract, Implementación)
```

## Ejemplo

```php
<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    public function create(array $data): User;
    public function getActive(): Collection;
}
```

### Registro en AppServiceProvider

```php
// app/Providers/AppServiceProvider.php
public function register(): void
{
    $this->app->bind(
        \App\Contracts\UserRepositoryInterface::class,
        \App\Repositories\UserRepository::class
    );
}
```
