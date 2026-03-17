<?php

namespace App\Clients;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class DjangoAuthClient
{
    private readonly string $baseUrl;
    private readonly string $secretKey;
    private readonly int $systemId;

    public function __construct()
    {
        $this->baseUrl   = (string) config('services.django_auth.base_url');
        $this->secretKey = (string) config('services.django_auth.secret_key');
        $this->systemId  = (int)    config('services.django_auth.system_id');
    }

    /**
     * Autentica al usuario contra la API de Django.
     */
    public function authenticate(string $username, string $password): Response
    {
        $payload = [
            'token'     => base64_encode($this->secretKey),
            'user'      => $username,
            'password'  => $password, // Ya viene en Base64 desde el Mapper
            'idSistema' => $this->systemId,
            'timeExp'   => 1,
            'saveTk'    => 1
        ];

        return $this->post($this->baseUrl, $payload);
    }

    /**
     * Inactiva el token en la API de Django.
     */
    public function invalidateToken(string $username): Response
    {
        $payload = [
            'user'      => $username,
            'idSistema' => $this->systemId
        ];

        return $this->post((string) config('services.django_auth.inactivate'), $payload);
    }

    /**
     * Verifica la validez de un token en la API de Django.
     */
    public function verifyToken(string $username, string $token): Response
    {
        $payload = [
            'token'     => $token,
            'user'      => $username,
            'saveTk'    => 1,
            'idSistema' => $this->systemId
        ];

        return $this->post((string) config('services.django_auth.verify_tk'), $payload);
    }

    /**
     * Helper centralizado para peticiones POST con configuración común.
     */
    private function post(string $url, array $payload): Response
    {
        return Http::timeout(30)
            ->withOptions(['verify' => app()->environment('local')])
            ->post($url, $payload);
    }
}
