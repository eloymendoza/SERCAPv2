# 📋 DTOs (Data Transfer Objects)

## Propósito

Esta carpeta contiene los **DTOs** (Data Transfer Objects). Un DTO es un objeto **inmutable** cuya única responsabilidad es transportar datos entre capas de la aplicación (Controller → Service → Repository).

## Responsabilidades

- Definir una estructura tipada e inmutable para los datos de entrada.
- Eliminar la dependencia directa de `Request` en los Services.
- Servir como contrato de datos entre capas.

## Convenciones de Nomenclatura

- **Sufijo**: `DTO` → Ejemplo: `UserDTO.php`, `CreateReportDTO.php`
- **Namespace**: `App\DTOs`
- Usar `readonly` properties de PHP 8.2+.
- Incluir un método estático `fromRequest()` para crear el DTO desde un FormRequest.

## Relación con otras Capas

```
FormRequest → DTO::fromRequest() → Service → Mapper::toArray(DTO)
```

# 📋 REGLAS PARA UN DTO (Data Transfer Object)

✅ **UN DTO DEBE:**
- Transportar datos de un lugar a otro
- Tener propiedades públicas (readonly si es posible)
- Tener constructor simple
- Tener método `toArray(): array` (convertir a array)
- Tener método estático `fromArray(array): self` (crear desde array)
- Ser un contenedor simple de datos

❌ **UN DTO NUNCA DEBE:**
- Validar datos
- Acceder a base de datos
- Persistir datos
- Tener lógica de negocio
- Disparar eventos
- Encriptar/desencriptar datos
- Hacer transformaciones complejas
- Tener inyección de dependencias
- Tener métodos con efectos secundarios

**RESUMEN:** El DTO es un **CONTENEDOR DE DATOS**. Nada más.

## ESTRUCTURA MÍNIMA:
```php
class [Entidad]DTO
{
    public function __construct(
        public readonly string $campo1,
        public readonly string $campo2,
        public readonly ?string $campo3 = null,
    ) {}

    public function toArray(): array {
        return [
            'campo1' => $this->campo1,
            'campo2' => $this->campo2,
            'campo3' => $this->campo3,
        ];
    }

    public static function fromArray(array $data): self {
        return new self(
            campo1: $data['campo1'],
            campo2: $data['campo2'],
            campo3: $data['campo3'] ?? null,
        );
    }
}
```