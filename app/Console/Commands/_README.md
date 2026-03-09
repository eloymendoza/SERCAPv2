# ⚙️ Console Commands (Comandos Artisan)

## Propósito

Esta carpeta contiene los **Comandos Artisan** personalizados. Un Command permite ejecutar tareas desde la **línea de comandos** (`php artisan ...`), ideales para procesos programados, migraciones de datos, mantenimiento y automatización.

## Responsabilidades

- Ejecutar procesos batch o masivos (importaciones, exportaciones).
- Automatizar tareas de mantenimiento (limpieza, sincronización).
- Programar tareas recurrentes en el **Scheduler** de Laravel.
- Proveer comandos de utilidad para el equipo de desarrollo.

## Convenciones de Nomenclatura

- **Formato verbal + Sufijo Command**: `SyncUsersCommand.php`, `GenerateReportCommand.php`, `PurgeExpiredTokensCommand.php`
- **Namespace**: `App\Console\Commands`
- La firma del comando (signature) debe usar kebab-case: `app:sync-users`.

## Relación con otras Capas

```
Terminal / Scheduler → Command → Service → Repository
```

## Ejemplo

```php
<?php

namespace App\Console\Commands;

use App\Services\UserService;
use Illuminate\Console\Command;

class SyncUsersCommand extends Command
{
    protected $signature = 'app:sync-users
                            {--force : Forzar la sincronización completa}';

    protected $description = 'Sincroniza los usuarios desde el sistema externo';

    public function __construct(
        private readonly UserService $userService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Iniciando sincronización de usuarios...');

        $count = $this->userService->syncFromExternalSource(
            force: $this->option('force')
        );

        $this->info("✅ {$count} usuarios sincronizados correctamente.");

        return Command::SUCCESS;
    }
}
```

## Registro en el Scheduler

```php
// routes/console.php
use Illuminate\Support\Facades\Schedule;

Schedule::command('app:sync-users')->dailyAt('02:00');
```
