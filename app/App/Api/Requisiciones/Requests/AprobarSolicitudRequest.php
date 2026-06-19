<?php

namespace App\App\Api\Requisiciones\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Domain\Requisiciones\Models\SolicitudRequisicion;

class AprobarSolicitudRequest extends FormRequest
{
    /**
     * Autoriza la operación consultando la Policy de aprobación.
     *
     * La Policy valida que la solicitud esté en_proceso y que Django confirme
     * al usuario como firmante activo de la instancia.
     */
    public function authorize(): bool
    {
        /** @var SolicitudRequisicion $solicitud */
        $solicitud = $this->route('solicitud');

        return $this->user()->can('aprobar', $solicitud);
    }

    public function rules(): array
    {
        return [
            'observaciones' => ['nullable', 'string', 'max:500'],
        ];
    }
}