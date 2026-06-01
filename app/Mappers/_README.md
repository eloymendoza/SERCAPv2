# 🔄 Mappers (Capa de Transformación)

## Propósito

Esta carpeta contiene los **Mappers**. Un Mapper se encarga de **transformar** datos entre diferentes formatos: DTO → array para Eloquent, Model → DTO para respuestas, etc.

## Responsabilidades

- Convertir un DTO en un array compatible con los campos del modelo Eloquent.
- Convertir un Model Eloquent en un DTO cuando sea necesario.
- Centralizar la lógica de mapeo para evitar duplicación en Services.

## Convenciones de Nomenclatura

- **Sufijo**: `Mapper` → Ejemplo: `UserMapper.php`, `ReportMapper.php`
- **Namespace**: `App\Mappers`
- Usar métodos estáticos (`toArray`, `toDTO`) para simplicidad.

## Relación con otras Capas

```
Service usa Mapper::toArray(DTO) → pasa array al Repository
Repository retorna Model → Mapper::toDTO(Model) → Service retorna DTO
```

# 🔄 REGLAS PARA UN MAPPER

✅ **UN MAPPER DEBE:**
- Convertir Model → DTO
- Convertir DTO → Array (para persistencia en BD)
- Convertir DTO → Array (para respuesta HTTP)
- Convertir Array → DTO
- Convertir colecciones de Models a DTOs
- Aplicar transformaciones de campos (snake_case ↔ camelCase)
- Incluir/excluir campos según contexto
- Ser stateless (sin estado, sin cache)

❌ **UN MAPPER NUNCA DEBE:**
- Validar datos
- Persistir datos en BD
- Tener lógica de negocio
- Disparar eventos
- Acceder a BD (excepto leer Models ya cargados)
- Encriptar datos
- Tener estado (propiedades privadas, cache)
- Tener métodos con efectos secundarios

**RESUMEN:** El Mapper es un **TRANSFORMADOR DE DATOS**. Solo convierte de un formato a otro.

## ESTRUCTURA MÍNIMA:
```php
class [Entidad]Mapper
{
    // Model → DTO
    public function toDTO(Model $model): [Entidad]DTO {
        return new [Entidad]DTO(
            campo1: $model->campo1,
            campo2: $model->campo2,
        );
    }

    // DTO → Array para guardar en BD
    public function toPersistenceArray([Entidad]DTO $dto): array {
        return [
            'campo1' => $dto->campo1,
            'campo2' => $dto->campo2,
        ];
    }

    // DTO → Array para respuesta HTTP
    public function toResponseArray([Entidad]DTO $dto): array {
        return [
            'campo1' => $dto->campo1,
            'campo2' => $dto->campo2,
        ];
    }

    // Múltiples Models → DTOs
    public function toDTOCollection(iterable $models): array {
        $dtos = [];
        foreach ($models as $model) {
            $dtos[] = $this->toDTO($model);
        }
        return $dtos;
    }
}
```
## 🟢 Cuándo usar
- Como Capa Anticorrupción para traducir estructuras de datos externas (JSON de APIs) a DTOs locales.

## 🔴 Cuándo NO usar
- No los crees para mapeos triviales donde el FormRequest o el Modelo Eloquent pueden generar el DTO directamente.

