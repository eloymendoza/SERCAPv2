<?php

namespace App\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeDomainCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'make:domain {name : El nombre del nuevo Dominio (ej. Facturacion)}';

    /**
     * @var string
     */
    protected $description = 'Crea la estructura de carpetas estandarizada para un nuevo Bounded Context';

    /**
     * Ejecuta el comando de consola.
     */
    public function handle()
    {
        $name = ucfirst($this->argument('name'));
        $domainPath = app_path("Domain/{$name}");

        if (File::exists($domainPath)) {
            $this->error("El dominio '{$name}' ya existe.");
            return Command::FAILURE;
        }

        $folders = [
            'Actions',
            'DTOs',
            'Enums',
            'Events',
            'Exceptions',
            'Jobs',
            'Listeners',
            'Mail',
            'Mappers',
            'Models',
            'Notifications',
            'Policies',
            'Services',
            'Traits'
        ];

        // Crear directorio raíz del dominio
        File::makeDirectory($domainPath, 0755, true);

        // Crear subdirectorios de artefactos
        foreach ($folders as $folder) {
            $path = "{$domainPath}/{$folder}";
            File::makeDirectory($path, 0755, true);
            File::put("{$path}/.gitkeep", "");
        }

        $this->info("Estructura de dominio '{$name}' creada exitosamente.");

        $apiPath = app_path("App/Api/{$name}");
        $apiFolders = [
            'Controllers',
            'Requests',
            'Resources'
        ];

        File::makeDirectory($apiPath, 0755, true);

        foreach ($apiFolders as $folder) {
            $path = "{$apiPath}/{$folder}";
            File::makeDirectory($path, 0755, true);
            File::put("{$path}/.gitkeep", "");
        }

        $this->info("Estructura de API para '{$name}' creada exitosamente.");
        return Command::SUCCESS;
    }
}
