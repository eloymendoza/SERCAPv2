# 📏 Rules (Reglas de Validación)

## Propósito

Esta carpeta contiene las reglas de validación personalizadas para el framework Laravel.

## Responsabilidades

- Validar un valor específico bajo reglas de negocio complejas.
- Retornar un mensaje de error estandarizado en caso de fallo.

## Convenciones de Nomenclatura

- **Formato**: `[NombreRegla]Rule.php` o `[NombreRegla].php`
- **Namespace**: `App\Rules`

## Relación con otras Capas

```
FormRequest → utiliza → Rule
```

## 🟢 Cuándo usar
- Cuando la lógica de validación de un campo es demasiado compleja para ser expresada con las reglas nativas de Laravel.
- Cuando necesitas validar verificando la base de datos o APIs externas, y reutilizar esta regla.

## 🔴 Cuándo NO usar
- No crees una Rule personalizada para validaciones triviales que Laravel ya soporta.
- No pongas lógica de transformación de datos aquí; las Rules solo dictaminan verdadero/falso.

## Ejemplo

```php
namespace App\Rules;
use Illuminate\Contracts\Validation\Rule;

class ValidRfc implements Rule
{
    public function passes($attribute, $value) { /* lógica */ }
    public function message() { return 'El RFC no es válido.'; }
}
```
