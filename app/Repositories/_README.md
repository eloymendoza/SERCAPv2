# 🗄️ Repositories (Capa de Acceso a Datos)

## Propósito

Esta carpeta contiene los **Repositorios** del sistema. Un Repository encapsula toda la lógica de **acceso a datos** (consultas Eloquent), abstrayendo al Service de los detalles de persistencia.

## Responsabilidades

- Ejecutar consultas a la base de datos (CRUD y queries complejas).
- Abstraer el uso directo de Eloquent fuera de los Services.
- Facilitar el cambio de fuente de datos sin afectar la lógica de negocio.
- Implementar filtros, paginación y ordenamiento.

## Convenciones de Nomenclatura

- **Sufijo**: `Repository` → Ejemplo: `UserRepository.php`, `ReportRepository.php`
- **Namespace**: `App\Repositories`
- Opcionalmente se puede crear una interfaz en `App\Contracts`.

## Relación con otras Capas

```
Service → Repository → Model (Eloquent)
```

## Ejemplo

```php
<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    public function __construct(
        private readonly User $model
    ) {}

    public function findById(int $id): ?User
    {
        return $this->model->find($id);
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function getActive(): Collection
    {
        return $this->model->where('active', true)->get();
    }
}
```
