<?php

namespace App\App\Api\Requests\EstructuraOrganizacional;

use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'parent_id'    => 'nullable|exists:unidades_organizativas,id',
            'nivel'        => 'required|string|in:direccion,gerencia,area',
            'nombre'       => 'required|string|max:255',
            'abreviatura'  => 'nullable|string|max:50',
            'nombre_corto' => 'nullable|string|max:150',
            'rfc'          => 'nullable|string|max:20',
            'encargado_id' => 'nullable|integer',
            'estado'       => 'nullable|string|in:Activo,Inactivo,Borrador',
        ];
    }
}