<?php

namespace App\App\Api\Requisiciones\Requests;

use Illuminate\Validation\Rule;
use App\Domain\Catalogos\Models\Proyecto;
use Illuminate\Foundation\Http\FormRequest;
use App\Domain\Requisiciones\Models\Puesto;
use App\Domain\Requisiciones\Models\Aspirante;
use App\Domain\Requisiciones\Enums\TipoContrato;
use App\Domain\Catalogos\Models\TabuladorSalario;
use App\Domain\Requisiciones\DTOs\SolicitudRequisicionDTO;
use App\Domain\Requisiciones\Enums\SolicitudRequisicionEstado;
use App\App\Api\Requisiciones\Rules\ValidarRangoSueldoTabulador;
use App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa;

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
            'solicitante_id' => [
                'nullable',
                'integer',
                Rule::exists(Aspirante::class, 'id'),
            ],
            'direccion_id' => [
                'required',
                'integer',
                Rule::exists(UnidadOrganizativa::class, 'id')->where('nivel', 'direccion')->where('estado', 'Activo'),
            ],
            'gerencia_id' => [
                'nullable',
                'integer',
                Rule::exists(UnidadOrganizativa::class, 'id')->where('nivel', 'gerencia')->where('estado', 'Activo'),
            ],
            'coordinacion_id' => [
                'nullable',
                'integer',
                Rule::exists(UnidadOrganizativa::class, 'id')->where('estado', 'Activo'),
            ],
            'observaciones' => ['nullable', 'string',],
            'accion' => ['nullable', 'string', Rule::in(['guardar', 'emitir'])],
            
            'requisicion' => ['nullable', 'array'],
            'requisicion.tipo' => ['nullable', 'integer'],
            'requisicion.detalle' => ['nullable', 'array'],
            'requisicion.detalle.*.puesto_id' => [
                'required_with:requisicion.detalle', 
                'integer', 
                Rule::exists(Puesto::class, 'id')->where('direccion_id', $this->input('direccion_id'))
            ],
            'requisicion.detalle.*.cantidad_solicitada' => ['required_with:requisicion.detalle', 'integer', 'min:1'],
            'requisicion.detalle.*.disciplina_id' => ['required_with:requisicion.detalle', 'integer'],
            'requisicion.detalle.*.tipo_contrato' => ['required_with:requisicion.detalle', Rule::enum(TipoContrato::class)],
            'requisicion.detalle.*.tabulador_id' => [
                'required_with:requisicion.detalle', 
                'integer',
                Rule::exists(TabuladorSalario::class, 'id')
            ],
            'requisicion.detalle.*.sueldo_asignado' => [
                'required_with:requisicion.detalle', 
                'numeric',
                new ValidarRangoSueldoTabulador()
            ],
            'requisicion.detalle.*.turno_horas' => ['required_with:requisicion.detalle'],
            'requisicion.detalle.*.fecha_inicio' => ['required_with:requisicion.detalle', 'date'],
            'requisicion.detalle.*.fecha_termino' => ['nullable', 'date', 'after_or_equal:requisicion.detalle.*.fecha_inicio'],
            'requisicion.detalle.*.fecha_limite_requerimiento' => ['required_with:requisicion.detalle', 'date'],
            'requisicion.detalle.*.empleados_propuestos' => ['nullable', 'array'],
            'requisicion.detalle.*.empleados_propuestos.*' => ['integer'],
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
            'direccion_id.exists' => 'La dirección seleccionada no es válida o está inactiva.',
            
            'gerencia_id.integer' => 'El campo :attribute debe ser un número entero.',
            'gerencia_id.exists' => 'La gerencia seleccionada no es válida o está inactiva.',
            
            'coordinacion_id.integer' => 'El campo :attribute debe ser un número entero.',
            'coordinacion_id.exists' => 'La coordinación seleccionada no es válida o está inactiva.',
            
            'observaciones.string' => 'El campo :attribute debe ser una cadena de texto.',
            
            'proyecto_id.integer' => 'El campo :attribute debe ser un número entero.',
            'proyecto_id.exists' => 'El proyecto seleccionado no existe.',
            
            'requisicion.detalle.*.puesto_id.exists' => 'El puesto seleccionado no es válido o no pertenece a la dirección solicitada.',

            'id_instancia_workflow.integer' => 'El campo :attribute debe ser un número entero.',
            'solicitante_id.integer' => 'El campo :attribute debe ser un número entero.',
            'solicitante_id.exists' => 'El :attribute especificado no existe.',
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

    public function toDTO(?int $id = null): SolicitudRequisicionDTO
    {
        $data = $this->validated();
        if ($id !== null) {
            $data['id'] = $id;
        }

        $dto = SolicitudRequisicionDTO::fromArray($data);

        if (($data['accion'] ?? null) === 'emitir') {
            $dto = $dto->withEstado(SolicitudRequisicionEstado::EN_PROCESO);
        }

        return $dto;
    }
}