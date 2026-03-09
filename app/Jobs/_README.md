# 📬 Jobs (Trabajos en Cola / Queue Jobs)

## Propósito

Esta carpeta contiene los **Jobs** (trabajos en cola). Un Job encapsula una tarea que se ejecuta en **segundo plano** de forma asíncrona, permitiendo que el servidor responda inmediatamente al usuario mientras el trabajo pesado se procesa en la cola.

## Responsabilidades

- Ejecutar operaciones pesadas o lentas fuera del ciclo de request (envío de emails masivos, generación de reportes, procesamiento de archivos, etc.).
- Reintentar automáticamente en caso de fallo.
- Ejecutarse en workers dedicados (`php artisan queue:work`).
- Encadenar jobs para flujos de trabajo complejos (Job Chaining).

## Convenciones de Nomenclatura

- **Formato verbal**: `ProcessReport.php`, `SendBulkEmails.php`, `ImportCsvData.php`
- **Namespace**: `App\Jobs`
- Implementar la interfaz `ShouldQueue` para que se ejecute en cola.

## Relación con otras Capas

```
Controller / Service / Listener → dispatch(Job) → Queue Worker ejecuta el Job
Job → Service → Repository (si necesita acceder a datos)
```

## Ejemplo

```php
<?php

namespace App\Jobs;

use App\Models\Report;
use App\Services\ReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número de reintentos antes de marcar como fallido.
     */
    public int $tries = 3;

    /**
     * Tiempo máximo de ejecución en segundos.
     */
    public int $timeout = 120;

    public function __construct(
        public readonly Report $report
    ) {}

    public function handle(ReportService $reportService): void
    {
        $reportService->generate($this->report);
    }

    /**
     * Manejar un fallo del job.
     */
    public function failed(\Throwable $exception): void
    {
        // Notificar al admin, registrar log, etc.
        logger()->error("Fallo al procesar reporte #{$this->report->id}: {$exception->getMessage()}");
    }
}
```

## Despachar un Job

```php
// Desde un Controller o Service:
ProcessReport::dispatch($report);

// Con retraso:
ProcessReport::dispatch($report)->delay(now()->addMinutes(5));

// En una cola específica:
ProcessReport::dispatch($report)->onQueue('reports');
```
