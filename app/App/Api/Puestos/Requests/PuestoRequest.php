<?php

namespace App\App\Api\Puestos\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PuestoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_puesto' => ['required', 'string', 'max:255'],
            'direccion_id' => ['required', 'integer'],
            'reporta_a_puesto_id' => ['present', 'nullable', 'integer'],
            'tipo' => ['required', 'string'],
            'id_documento' => ['nullable', 'integer', 'min:1'],
        ];
    }
}