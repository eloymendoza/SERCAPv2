<?php

namespace App\App\Api\EstructuraOrganizacional\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Domain\EstructuraOrganizacional\Enums\UnidadOrganizativaEstadoEnum;

class UnidadOrganizativaRequest extends FormRequest
{
    /**
     * Determina la autorización para interactuar con la estructura jerárquica.
     */
    public function authorize(): bool
    {
        return true; // Autenticación gestionada vía middleware Sanctum general
    }

    /**
     * Define los criterios de validación para las mutaciones DML de la entidad.
     */
    public function rules(): array
    {
        $unidad = $this->route('unidad');
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');

        return [
            'parent_id'      => 'nullable|exists:unidades_organizativas,id',
            'nivel'          => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'in:direccion,gerencia,area',
            ],
            'nombre'         => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255',
            ],
            'abreviatura'    => 'nullable|string|max:50',
            'nombre_corto'   => 'nullable|string|max:150',
            'rfc'            => 'nullable|string|max:20',
            'encargado_id'   => 'nullable|integer',
            'encargado_usuario' => 'nullable|string|max:255',
            'reemplaza_a_id' => 'nullable|exists:unidades_organizativas,id'
        ];
    }
}