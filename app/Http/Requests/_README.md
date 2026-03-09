# ✅ Requests (Form Requests - Validación de Entrada)

## Propósito

Esta carpeta contiene los **Form Requests**. Un FormRequest encapsula las **reglas de validación** de los datos de entrada, sacando esa responsabilidad del Controller.

## Responsabilidades

- Definir reglas de validación para cada endpoint.
- Autorizar si el usuario puede hacer la petición (`authorize()`).
- Personalizar mensajes de error.
- Preparar/transformar datos antes de la validación si es necesario.

## Convenciones de Nomenclatura

- **Prefijo de acción + Sufijo Request**: `StoreUserRequest.php`, `UpdateDocumentRequest.php`
- **Namespace**: `App\Http\Requests`

## Relación con otras Capas

```
Route → Middleware → FormRequest (valida) → Controller
```

## Ejemplo

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique'   => 'Este correo ya está registrado.',
        ];
    }
}
```
