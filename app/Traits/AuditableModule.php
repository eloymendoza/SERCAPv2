<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait AuditableModule
 * 
 * Intercepta los eventos de Eloquent para registrar un historial de cambios detallado.
 * Requiere que el modelo implemente el método `getHistoryModelFqcn()` que retorne
 * el FQCN (Fully Qualified Class Name) del modelo de historial, y opcionalmente
 * la llave foránea correspondiente.
 * 
 * @method static void created(\Closure|string|array $callback)
 * @method static void updated(\Closure|string|array $callback)
 * @method static void deleted(\Closure|string|array $callback)
 * @method static void restored(\Closure|string|array $callback)
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait AuditableModule
{
    public static function bootAuditableModule()
    {
        static::created(function (Model $model) {
            $model->logChange('created', null, $model->getAttributes());
        });

        static::updated(function (Model $model) {
            // Solo registramos si hubo cambios reales
            if ($model->wasChanged()) {
                $model->logChange('updated', $model->getOriginal(), $model->getChanges());
            }
        });

        static::deleted(function (Model $model) {
            $model->logChange('deleted', $model->getAttributes(), null);
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function (Model $model) {
                $model->logChange('restored', null, $model->getAttributes());
            });
        }
    }

    /**
     * Registra el cambio en la tabla de historial correspondiente.
     */
    protected function logChange(string $event, ?array $oldValues, ?array $newValues): void
    {
        $historyModelClass = $this->getHistoryModelFqcn();
        
        // Asume por convención que la llave foránea es el nombre de la tabla en singular + '_id'
        // ej: 'unidades_organizativas' -> 'unidad_organizativa_id'
        // Si el modelo lo sobrescribe, usa ese valor.
        $foreignKey = method_exists($this, 'getHistoryForeignKey') 
            ? $this->getHistoryForeignKey() 
            : Str::singular($this->getTable()) . '_id';

        /** @var \App\Domain\Autenticacion\Models\User|null $user */
        $user = Auth::user();
        $username = $user ? ($user->username ?? $user->email ?? $user->name ?? 'Sistema') : 'Sistema';

        $historyModelClass::create([
            $foreignKey => $this->getKey(),
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'username' => $username,
            'created_at' => now(),
        ]);
    }

    /**
     * FQCN del modelo de historial asociado.
     * Ejemplo: return UnidadOrganizativaHistorialCambio::class;
     * 
     * @return string
     */
    abstract public function getHistoryModelFqcn(): string;
}