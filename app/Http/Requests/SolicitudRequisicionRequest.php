<?php

namespace App\Http\Requests;

use App\Enums\SolicitudRequisicionEstado;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * Valida los datos recibidos para la creación o edición de una SolicitudRequisicion.
 */
class SolicitudRequisicionRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Retorna las reglas de validación aplicadas a la petición.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $id = $this->route('solicitud_requisicion') ?? $this->input('id');

        return [
            'folio' => ['required','string','max:255',Rule::unique('solicitud_requisiciones', 'folio')->ignore($id),],
            'proyecto_id' => ['nullable','integer',],
            'id_instancia_workflow' => ['nullable','integer',],
            'solicitante_id' => ['nullable','integer',],
            'direccion_id' => ['required','integer',],
            'gerencia_id' => ['required','integer',],
            'coordinacion_id' => ['nullable','integer',],
            'observaciones' => ['nullable', 'string',],
            'estado' => ['nullable', Rule::enum(SolicitudRequisicionEstado::class)],
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
            'folio.required' => 'El campo :attribute es obligatorio.',
            'folio.string' => 'El campo :attribute debe ser una cadena de texto.',
            'folio.max' => 'El campo :attribute debe tener menos de :max caracteres.',
            'folio.unique' => 'El :attribute ingresado ya se encuentra registrado.',
            
            'direccion_id.required' => 'El campo :attribute es obligatorio.',
            'direccion_id.integer' => 'El campo :attribute debe ser un número entero.',
            
            'gerencia_id.required' => 'El campo :attribute es obligatorio.',
            'gerencia_id.integer' => 'El campo :attribute debe ser un número entero.',
            
            'coordinacion_id.integer' => 'El campo :attribute debe ser un número entero.',
            
            'observaciones.string' => 'El campo :attribute debe ser una cadena de texto.',
            
            'proyecto_id.integer' => 'El campo :attribute debe ser un número entero.',
            'id_instancia_workflow.integer' => 'El campo :attribute debe ser un número entero.',
            'solicitante_id.integer' => 'El campo :attribute debe ser un número entero.',
            'estado.Illuminate\Validation\Rules\Enum' => 'El :attribute seleccionado no es válido.',
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
            'folio' => 'folio de la solicitud',
            'proyecto_id' => 'proyecto',
            'id_instancia_workflow' => 'instancia de workflow',
            'solicitante_id' => 'solicitante',
            'direccion_id' => 'dirección',
            'gerencia_id' => 'gerencia',
            'coordinacion_id' => 'coordinación',
            'observaciones' => 'observaciones',
            'estado' => 'estado',
        ];
    }

    /**
     * Controla el comportamiento ante fallos de validación, arrojando una respuesta JSON estructurada.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Parámetros inválidos',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}