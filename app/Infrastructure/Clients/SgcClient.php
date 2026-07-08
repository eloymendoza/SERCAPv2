<?php

namespace App\Infrastructure\Clients;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\PendingRequest;
use Exception;

/**
 * Cliente HTTP Wrapper para aislar la comunicación con el SGC (Condor).
 */
class SgcClient
{
    private readonly string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = (string) config('services.sgc.base_url', 'https://iaidev.grupo-iai.com.mx:335/api/v1/corporativo');
    }

    protected function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout(30)
            ->withoutVerifying() // Se omite validación SSL temporalmente por tratarse de un entorno dev/auto-firmado
            ->withHeaders([
                'Accept' => 'application/json',
            ]);
    }

    /**
     * Realiza una petición GET al endpoint de perfiles de puesto.
     *
     * @return array
     * @throws Exception
     */
    public function getPerfilesPuesto(): array
    {
        try {
            Log::channel('puestos')->info("Peticion a SGC [GET /documentos/perfiles-puesto/]");
            
            $response = $this->client()->get('/documentos/perfiles-puesto/');
            
            if (!$response->successful()) {
                throw new Exception(
                    "Error de comunicación con API SGC [perfiles-puesto]. Código: {$response->status()}",
                    $response->status()
                );
            }
            
            return $response->json() ?? [];
        } catch (Exception $e) {
            Log::channel('puestos')->error("Falla crítica conectando con SGC: " . $e->getMessage());
            throw new Exception("No se pudo obtener la lista de perfiles del SGC: " . $e->getMessage());
        }
    }
}