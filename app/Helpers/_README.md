# 🛠️ Helpers (Funciones Auxiliares)

## Propósito

Esta carpeta contiene **funciones auxiliares y utilidades** globales. Un Helper proporciona funciones de uso general que no encajan en ninguna clase específica.

## Responsabilidades

- Proveer funciones utilitarias reutilizables en toda la aplicación.
- Formatear datos (fechas, monedas, textos).
- Proveer constantes globales del sistema.

## Convenciones de Nomenclatura

- **Descriptivo por dominio**: `StringHelper.php`, `DateHelper.php`, `FormatHelper.php`
- **Namespace**: `App\Helpers`
- Si se usan funciones globales (no clases), registrar el archivo en `composer.json` > `autoload.files`.

## Relación con otras Capas

```
Cualquier capa → Helper (uso transversal)
```

## Ejemplo

```php
<?php

namespace App\Helpers;

class FormatHelper
{
    /**
     * Formatea un número como moneda mexicana.
     */
    public static function currency(float $amount): string
    {
        return '$ ' . number_format($amount, 2, '.', ',') . ' MXN';
    }

    /**
     * Formatea una fecha al formato legible en español.
     */
    public static function dateSpanish(string $date): string
    {
        return \Carbon\Carbon::parse($date)
            ->locale('es')
            ->isoFormat('D [de] MMMM [de] YYYY');
    }
}
```
