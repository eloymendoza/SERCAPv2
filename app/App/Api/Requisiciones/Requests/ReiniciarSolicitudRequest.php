<?php

namespace App\App\Api\Requisiciones\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Domain\Requisiciones\Models\SolicitudRequisicion;

class ReiniciarSolicitudRequest extends FormRequest
{
    /**
     * Autoriza la operación consultando la Policy de reiniciar.
     */
    public function authorize(): bool
    {
        /** @var SolicitudRequisicion $solicitud */
        $solicitud = $this->route('solicitud');

        return $this->user()->can('reiniciar', $solicitud);
    }

    public function rules(): array
    {
        return [
            'observaciones' => ['nullable', 'string', 'max:500'],
        ];
    }
}
