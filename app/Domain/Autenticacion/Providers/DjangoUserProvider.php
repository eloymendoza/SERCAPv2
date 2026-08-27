<?php

namespace App\Domain\Autenticacion\Providers;

use Illuminate\Support\Facades\Cache;
use App\Domain\Autenticacion\Models\User;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Domain\Autenticacion\Mappers\UserMapper;
use App\Infrastructure\Clients\DjangoAuthClient;
use App\Domain\Autenticacion\Exceptions\AuthException;

/**
 * Implementa la resolución de usuarios mediante la API externa de Django.
 */
class DjangoUserProvider implements UserProvider
{
    public function __construct(
        private readonly DjangoAuthClient $authClient,
        private readonly UserMapper $userMapper
    ) {}

    /**
     * Recupera un usuario por su identificador único.
     *
     * @param mixed $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        $user = User::find($identifier);
        
        if ($user) {
            $this->hydrateUserContext($user);
        }
        
        return $user;
    }

    /**
     * Recupera un usuario utilizando su identificador y token de "recuérdame".
     *
     * @param mixed $identifier
     * @param string $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        $user = User::where('id', $identifier)
                    ->where('remember_token', $token)
                    ->first();
                    
        if ($user) {
            $this->hydrateUserContext($user);
        }
        
        return $user;
    }

    /**
     * Actualiza el token de "recuérdame" del usuario en la base de datos local.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param string $token
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        /** @var \Illuminate\Database\Eloquent\Model $user */
        $user->forceFill(['remember_token' => $token])->save();
    }

    /**
     * Autentica al usuario contra la API externa y sincroniza su estado local.
     * 
     * Delega la validación de credenciales a Django. Si es exitosa, hidrata el DTO
     * y lo almacena temporalmente en caché para inyectarlo en peticiones subsecuentes.
     *
     * @param array $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     * @throws \App\Domain\Autenticacion\Exceptions\AuthException
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials['username']) || empty($credentials['password'])) {
            return null;
        }

        $response = $this->authClient->authenticate($credentials['username'], $credentials['password']);

        if (!$response->successful()) {
            $data = $response->json();
            $errorDetail = $data['error'] ?? $data['message'] ?? 'Credenciales inválidas o error de conexión.';
            
            if ($response->status() === 403 || $response->status() === 404 || str_contains(strtolower((string)$errorDetail), 'permiso')) {
                throw AuthException::accessDenied($errorDetail);
            }
            
            throw AuthException::invalidCredentials($errorDetail);
        }

        $data = $response->json();
        $userDto = $this->userMapper->fromDjangoToDTO($data);
        
        $persistenceData = $this->userMapper->toPersistenceArray($userDto);
        $user = User::updateOrCreate(
            ['username' => $persistenceData['username']], 
            $persistenceData
        );
        
        $fullDto = $userDto->withId($user->id);
        
        // Almacenar el DTO en caché de forma temporal (30 minutos, alineado a la inactividad de la sesión)
        // Usamos Cache en lugar de Session directamente para mantener el Provider agnóstico del estado web/api
        Cache::put("user_context_{$user->id}", $fullDto, now()->addMinutes(30));
        
        $user->contexto = $fullDto;

        return $user;
    }

    /**
     * Valida las credenciales del usuario.
     * 
     * Retorna siempre verdadero porque la validación real (contraseña/acceso) ya
     * ocurrió de forma síncrona durante retrieveByCredentials contra Django.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param array $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        // En este punto, retrieveByCredentials ya validó el password con la API externa.
        // Si llegamos aquí con un usuario válido, las credenciales son correctas.
        return true;
    }

    /**
     * Inyecta el contexto volátil (DTO) en la instancia del modelo User.
     * 
     * @param \App\Domain\Autenticacion\Models\User $user
     * @return void
     */
    private function hydrateUserContext(User $user): void
    {
        $dto = Cache::get("user_context_{$user->id}");
        
        if ($dto) {
            $user->contexto = $dto;
        } else {
            // Si el DTO no está en caché (expiro), el usuario debería volver a autenticarse.
            $user->contexto = null;
        }
    }

    /**
     * Ignora el re-hasheo nativo de contraseñas de Laravel.
     * 
     * Dado que el control criptográfico y validación pertenece 100% a Django,
     * este método se mantiene vacío por diseño.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param array $credentials
     * @param bool $force
     * @return void
     */
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        // Las contraseñas se manejan en Django, por lo que aquí no se requiere re-hasheo local.
    }
}