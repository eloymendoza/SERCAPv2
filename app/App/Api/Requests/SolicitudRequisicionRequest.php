<?php

namespace App\App\Api\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Domain\Catalogos\Models\Proyecto;
use App\Domain\Requisiciones\Enums\TipoContrato;

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
            'folio' => ['nullable','string','max:255'],
            'proyecto_id' => [
                'nullable',
                'integer',
                Rule::exists(Proyecto::class, 'idProyecto')->where('activoProyecto', true),
            ],
            'id_instancia_workflow' => ['nullable','integer',],
            'solicitante_id' => ['nullable','integer',],
            'direccion_id' => ['required','integer',],
            'gerencia_id' => ['required','integer',],
            'coordinacion_id' => ['nullable','integer',],
            'observaciones' => ['nullable', 'string',],
            'accion' => ['nullable', 'string', Rule::in(['guardar', 'emitir'])],
            
            'requisicion' => ['nullable', 'array'],
            'requisicion.tipo' => ['nullable', 'integer'],
            'requisicion.detalle' => ['nullable', 'array'],
            'requisicion.detalle.puesto_id' => ['required_with:requisicion.detalle', 'integer'],
            'requisicion.detalle.cantidad_solicitada' => ['required_with:requisicion.detalle', 'integer', 'min:1'],
            'requisicion.detalle.disciplina_id' => ['required_with:requisicion.detalle', 'integer'],
            'requisicion.detalle.tipo_contrato' => ['required_with:requisicion.detalle', Rule::enum(TipoContrato::class)],
            'requisicion.detalle.tabulador_sueldo' => ['required_with:requisicion.detalle', 'numeric'],
            'requisicion.detalle.turno_horas' => ['required_with:requisicion.detalle'],
            'requisicion.detalle.fecha_inicio' => ['required_with:requisicion.detalle', 'date'],
            'requisicion.detalle.fecha_termino' => ['nullable', 'date', 'after_or_equal:requisicion.detalle.fecha_inicio'],
            'requisicion.detalle.fecha_limite_requerimiento' => ['required_with:requisicion.detalle', 'date'],
            'requisicion.detalle.empleados_propuestos' => ['nullable', 'array'],
            'requisicion.detalle.empleados_propuestos.*' => ['integer'],
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
            
            'direccion_id.required' => 'El campo :attribute es obligatorio.',
            'direccion_id.integer' => 'El campo :attribute debe ser un número entero.',
            
            'gerencia_id.required' => 'El campo :attribute es obligatorio.',
            'gerencia_id.integer' => 'El campo :attribute debe ser un número entero.', 
            
            'coordinacion_id.integer' => 'El campo :attribute debe ser un número entero.',
            
            'observaciones.string' => 'El campo :attribute debe ser una cadena de texto.',
            
            'proyecto_id.integer' => 'El campo :attribute debe ser un número entero.',
            'proyecto_id.exists' => 'El proyecto seleccionado no existe.',

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

    public function toDTO(?int $id = null): \App\Domain\Requisiciones\DTOs\SolicitudRequisicionDTO
    {
        $data = $this->validated();
        if ($id !== null) {
            $data['id'] = $id;
        }

        $dto = \App\Domain\Requisiciones\DTOs\SolicitudRequisicionDTO::fromArray($data);

        if (($data['accion'] ?? null) === 'emitir') {
            $dto = $dto->withEstado(\App\Domain\Requisiciones\Enums\SolicitudRequisicionEstado::EN_PROCESO);
        }

        return $dto;
    }


}