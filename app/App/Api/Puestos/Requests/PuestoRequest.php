<?php

namespace App\App\Api\Puestos\Requests;

use Illuminate\Validation\Rule;
use App\Domain\Puestos\Models\Puesto;
use Illuminate\Foundation\Http\FormRequest;
use App\Domain\Puestos\Enums\TipoPuestoEnum;
use App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa;

/**
 * Valida los datos recibidos para la creación o edición de un Puesto.
 */
class PuestoRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta solicitud.
     */
    public function authorize(): bool
    {
        if ($puesto = $this->route('puesto')) {
            return $this->user()?->can('update', $puesto) ?? false;
        }

        return $this->user()?->can('create') ?? false;
    }

    /**
     * Retorna las reglas de validación aplicadas a la petición.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'nombre_puesto' => ['required', 'string', 'max:255'],
            'direccion_id' => [
                'required',
                'integer',
                Rule::exists(UnidadOrganizativa::class, 'id')
                    ->where('nivel', 'direccion')
                    ->where('estado', 'Activo'),
            ],
            'reporta_a_puesto_id' => [
                'present',
                'nullable',
                'integer',
                Rule::exists(Puesto::class, 'id')
                    ->where('direccion_id', $this->input('direccion_id')),
            ],
            'tipo' => ['required', Rule::enum(TipoPuestoEnum::class)],
            'id_documento' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Retorna los mensajes de error personalizados para la validación.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre_puesto.required' => 'El campo :attribute es obligatorio.',
            
            'direccion_id.required' => 'El campo :attribute es obligatorio.',
            'direccion_id.integer' => 'El campo :attribute debe ser un número entero.',
            'direccion_id.exists' => 'La dirección seleccionada no es válida o está inactiva.',
            
            'reporta_a_puesto_id.integer' => 'El campo :attribute debe ser un número entero.',
            'reporta_a_puesto_id.exists' => 'El puesto superior seleccionado no existe o no pertenece a la misma dirección.',
            
            'tipo.required' => 'El campo :attribute es obligatorio.',
            'tipo.Illuminate\Validation\Rules\Enum' => 'El :attribute seleccionado no es válido.',
        ];
    }

    /**
     * Retorna los nombres de los atributos amigables.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nombre_puesto' => 'nombre del puesto',
            'direccion_id' => 'dirección',
            'reporta_a_puesto_id' => 'puesto superior (reporta a)',
            'tipo' => 'tipo de puesto',
            'id_documento' => 'ID de documento',
        ];
    }
}