# 🌐 Clients (Clientes de APIs Externas)

## Propósito

Esta carpeta contiene los **Clients**. Un Client es una clase especializada en la comunicación con servicios externos (APIs REST, SOAP, etc.). Su objetivo es aislar la lógica de transporte y protocolo del resto de la aplicación.

## Responsabilidades

- Encapsular la configuración de red (URLs base, timeouts, verificación SSL).
- Gestionar la autenticación con la API externa (tokens, llaves secretas).
- Realizar las peticiones HTTP y retornar objetos de respuesta (`Response`).
- Manejar errores de red y propagarlos de forma controlada.

## Convenciones de Nomenclatura

- **Sufijo**: `SustantivoClient` → Ejemplo: `DjangoAuthClient.php`, `WeatherApiClient.php`.
- **Namespace**: `App\Clients`.
- Se recomienda usar métodos descriptivos para cada endpoint de la API.

## Relación con otras Capas

```
Service → Client (petición a API externa)
Client → DTO (opcionalmente para estructurar datos de entrada complejos)
```

## Ejemplo

```php
<?php

namespace App\Clients;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class DjangoAuthClient
{
    public function authenticate(string $username, string $password): Response
    {
        return Http::timeout(30)
            ->post('https://api.externa.com/login', [
                'user' => $username,
                'pass' => $password
            ]);
    }
}
```
