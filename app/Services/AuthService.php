<?php

namespace App\Services;

use App\DTOs\LoginDTO;
use App\Mappers\UserMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Repositories\UserRepository;
use Exception;

class AuthService
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

    /**
     * Lógica de autenticación contra la API de Django y sincronización local.
     * @throws Exception
     */
    public function authenticate(LoginDTO $dto): array
    {
        $payload = [
            'token'     => base64_encode(env('SECRET_KEY')), 
            'user'      => $dto->username,
            'password'  => base64_encode($dto->password), 
            'idSistema' => env('ID_SISTEMA'),
            'timeExp'   => 1,
            'saveTk'    => 1
        ];

        // Consultar API Django
        $response = Http::timeout(30)
            ->withOptions(['verify' => app()->environment('local')])
            ->post(env('API_AUTH'), $payload);

        if (!$response->successful()) {
            $this->logError('Fallo de respuesta HTTP en Django', $response, $dto->username);
            throw new Exception($response->json()['message'] ?? 'Error en el servidor de autenticación', $response->status());
        }

        $data = $response->json();

        // Validar respuesta "Success" de Django
        if (($data['message'] ?? '') !== 'Success') {
            $this->logError('Django rechazó las credenciales', $response, $dto->username);
            throw new Exception($data['message'] ?? 'Credenciales inválidas', 401);
        }

        Log::channel('auth')->info("Contenido de data: ", $data);

        // Sincronizar usuario localmente usando el Mapper
        $mappedData = UserMapper::fromDjangoToLocal($data);
        $user = $this->userRepository->syncExternalUser($mappedData);

        // Control de Sesiones Concurrentes (Nivel Elite)
        // Eliminar cualquier otra sesión activa del usuario para garantizar sesión única
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->delete();

        // Iniciar sesión en Laravel
        Auth::login($user);

        // Regenerar sesión para prevenir fijación de sesiones (Estándar de Seguridad)
        request()->session()->regenerate();

        // Retornar datos para el controlador
        return [
            'user' => $user,
            'data' => $data
        ];
    }

    /**
     * Cierra la sesión activa en Laravel.
     */
    public function logout(Request $request): void
    {
        // 1. Logout del guard web (Limpia sesión física)
        Auth::guard('web')->logout();

        // 2. Limpiar el usuario del Resolver del Request
        $request->setUserResolver(fn () => null);

        // 3. Limpieza profunda de TODOS los guards cargados
        // Esto evita que Sanctum u otros guards mantengan al usuario en memoria
        foreach (array_keys(config('auth.guards')) as $guardName) {
            $guard = Auth::guard($guardName);
            if (method_exists($guard, 'logout')) {
                $guard->logout();
            }
        }

        // 4. Forzar a olvidar el usuario en el gestor de autenticación
        Auth::forgetUser();

        // 5. Limpieza física de la sesión
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::channel('auth')->info('=== POST-LOGOUT DEBUG ===', [
            'auth_check'       => Auth::check(),
            'auth_id'          => Auth::id(),
            'session_id'       => $request->session()->getId(),
            'session_user_id'  => $request->session()->get('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d'),
            'session_data'     => $request->session()->all(),
        ]);

        Log::channel('auth')->info('Usuario removido de todos los guards y sesión invalidada');
    }

    private function logError(string $message, $response, string $username): void
    {
        Log::channel('auth')->error($message, [
            'status' => $response->status(),
            'usuario' => $username,
            'body' => $response->json()
        ]);
    }
}