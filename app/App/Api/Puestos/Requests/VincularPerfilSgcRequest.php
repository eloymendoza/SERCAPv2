<?php

namespace App\App\Api\Puestos\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida los datos recibidos para vincular un Puesto existente con su perfil SGC.
 */
class VincularPerfilSgcRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta solicitud.
     */
    public function authorize(): bool
    {
        // TODO: ERS debe ser el unico que pueda ejecutar esta accion.
        return true;
    }

    /**
     * Obtiene las reglas de validación que aplican a la solicitud.
     */
    public function rules(): array
    {
        return [
            'id_documento' => ['required', 'integer', 'min:1'],
        ];
    }
}