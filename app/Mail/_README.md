# ✉️ Mailables (Envío de Correos)

## Propósito

Esta carpeta contiene las clases Mailable responsables de construir y enviar correos electrónicos a través del sistema.

## Responsabilidades

- Configurar el asunto, la vista (Markdown) y los destinatarios del correo.
- Pasar los datos del DTO o Modelo hacia la vista del correo.

## Convenciones de Nomenclatura

- **Formato**: `[Asunto]Mailable.php`
- **Namespace**: `App\Mail`

## Relación con otras Capas

```
Job / Service / Listener → instancia y envía → Mailable
```

## 🟢 Cuándo usar
- Para definir la estructura y contenido de un correo transaccional o notificación del sistema.

## 🔴 Cuándo NO usar
- No incluyas lógica de negocio pesada ni consultas complejas dentro del constructor del Mailable.
- Si el envío es masivo o lento, asegúrate de utilizar `ShouldQueue` para enviarlo en segundo plano en vez de bloquear el request.

## Ejemplo

```php
namespace App\Mail;
use Illuminate\Mail\Mailable;

class RequisicionAprobadaMailable extends Mailable
{
    public function build()
    {
        return $this->markdown('emails.requisicion.aprobada')->subject('Requisición Aprobada');
    }
}
```
