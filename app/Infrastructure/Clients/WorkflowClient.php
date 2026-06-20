<?php

namespace App\Infrastructure\Clients;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\PendingRequest;
use App\Domain\Workflows\Exceptions\WorkflowCommunicationException;

/**
 * Cliente HTTP Wrapper para aislar la comunicación con el motor de Workflows.
 */
class WorkflowClient
{
    private readonly string $baseUrl;
    private readonly string $token;

    public function __construct()
    {
        $this->baseUrl = (string) config('services.workflow.base_url');
        $this->token = (string) config('services.workflow.token');
    }

    protected function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout(30)
            ->withHeaders([
                'Authorization' => 'Token ' . $this->token,
                'Accept' => 'application/json',
            ]);
    }

    /**
     * Realiza una petición POST al proveedor externo.
     *
     * @throws WorkflowCommunicationException
     */
    public function post(string $endpoint, array $payload): array
    {
        try {
            Log::channel('workflow')->info("Peticion a Workflow [{$endpoint}]", ['payload' => $payload]);
            
            $response = $this->client()->post($endpoint, $payload);
            
            Log::channel('workflow')->info("Respuesta de Workflow [{$endpoint}]", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if (!$response->successful()) {
                $errorBody = $response->body();
                throw new WorkflowCommunicationException(
                    message: "Error de comunicación con API Workflow [{$endpoint}]. Respuesta: {$errorBody}",
                    statusCode: $response->status(),
                    errorCode: 'WORKFLOW_API_ERROR'
                );
            }
            
            return $response->json() ?? [];
        } catch (\Exception $e) {
            if ($e instanceof WorkflowCommunicationException) {
                throw $e;
            }

            throw new WorkflowCommunicationException(
                message: "Falla crítica de red conectando con Workflows: " . $e->getMessage(),
                statusCode: 500,
                errorCode: 'WORKFLOW_NETWORK_ERROR'
            );
        }
    }
}