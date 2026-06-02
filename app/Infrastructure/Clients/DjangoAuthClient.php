<?php

namespace App\Infrastructure\Clients;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

/**
 * Cliente para la API de Autenticación de Django.
 */
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
     * Autentica un usuario contra la API Auth.
     * @param string $username
     * @param string $password
     * @return Response
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
     * Invalida el token de un usuario en la API Auth.
     * @param string $username
     * @return Response
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
     * Valida la vigencia del token en la API Auth.
     * @param string $username
     * @param string $token
     * @return Response
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
     * Realiza una petición POST a la API Auth.
     * @param string $url
     * @param array $payload
     * @return Response
     */
    private function post(string $url, array $payload): Response
    {
        return Http::timeout(30)
            ->withOptions(['verify' => app()->environment('local')])
            ->post($url, $payload);
    }
}
