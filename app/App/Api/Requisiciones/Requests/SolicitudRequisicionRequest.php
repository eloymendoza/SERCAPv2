<?php

namespace App\App\Api\Requisiciones\Requests;

use Illuminate\Validation\Rule;
use App\Domain\Puestos\Models\Puesto;
use App\Domain\Catalogos\Models\Proyecto;
use Illuminate\Foundation\Http\FormRequest;
use App\Domain\Requisiciones\Models\Requisicion;
use App\Domain\Catalogos\Models\TabuladorSalario;
use App\Domain\Requisiciones\Enums\TipoContratoEnum;
use App\Domain\Requisiciones\Models\SolicitudRequisicion;
use App\Domain\Requisiciones\DTOs\SolicitudRequisicionDTO;
use App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa;

/**
 * Valida los datos recibidos para la creación o edición de una SolicitudRequisicion.
 */
class SolicitudRequisicionRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta solicitud.
     *
     * Para create, se pasa el par (direccion_id, proyecto_id) a la Policy para evaluación
     * contextual. Si proyecto_id no existe en BD, se descarta antes de llegar a la Policy
     * para que rules() pueda emitir el 422 correcto en lugar de un 403 engañoso.
     */
    public function authorize(): bool
    {
        if ($solicitud = $this->route('solicitud')) {
            return $this->user()?->can('update', $solicitud) ?? false;
        }

        $unidadOrganizativaId = $this->filled('unidad_organizativa_id') ? (int) $this->input('unidad_organizativa_id') : null;
        $proyectoId  = $this->filled('proyecto_id')  ? (int) $this->input('proyecto_id') : null;

        if ($proyectoId && !Proyecto::where('idProyecto', $proyectoId)->where('activoProyecto', true)->exists()) {
            $proyectoId = null;
        }

        return $this->user()?->can('create', [
            SolicitudRequisicion::class,
            $unidadOrganizativaId,
            $proyectoId,
        ]) ?? false;
    }

    /**
     * Prepara los datos antes de la validación.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('requisicion.detalle') && is_array($this->input('requisicion.detalle'))) {
            $requisicion = $this->input('requisicion');
            foreach ($requisicion['detalle'] as $key => $detalle) {
                if (isset($detalle['propuesta_nombre']) && is_string($detalle['propuesta_nombre'])) {
                    $requisicion['detalle'][$key]['propuesta_nombre'] = mb_strtoupper($detalle['propuesta_nombre'], 'UTF-8');
                }
            }
            $this->merge(['requisicion' => $requisicion]);
        }
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
            'requisicion_padre_id' => [
                'nullable',
                'integer',
                Rule::exists(Requisicion::class, 'id'),
            ],
            'proyecto_id' => [
                'nullable',
                'integer',
                Rule::exists(Proyecto::class, 'idProyecto')->where('activoProyecto', true),
            ],
            'solicitante_id' => [
                'nullable',
                'integer',
            ],
            'unidad_organizativa_id' => [
                'required',
                'integer',
                Rule::exists(UnidadOrganizativa::class, 'id')->where('estado', 'Activo'),
            ],
            'observaciones' => ['nullable', 'string',],
            'accion' => ['nullable', 'string', Rule::in(['guardar', 'emitir'])],
            
            'requisicion' => ['nullable', 'array'],
            'requisicion.tipo' => ['nullable', 'integer'],
            'requisicion.detalle' => [
                'nullable', 
                'array',
            ],
            'requisicion.detalle.*.puesto_id' => [
                'nullable', 
                'integer', 
                Rule::exists(Puesto::class, 'id')->where('unidad_organizativa_id', $this->input('unidad_organizativa_id'))
            ],
            'requisicion.detalle.*.propuesta_nombre' => [
                'required_without:requisicion.detalle.*.puesto_id',
                'string',
                'max:255',
                'distinct',
                Rule::unique('puestos', 'nombre_puesto')
                    ->where('unidad_organizativa_id', $this->input('unidad_organizativa_id')),
            ],
            'requisicion.detalle.*.propuesta_reporta_a' => [
                'nullable',
                'integer',
                Rule::exists(Puesto::class, 'id')->where('unidad_organizativa_id', $this->input('unidad_organizativa_id'))
            ],
            'requisicion.detalle.*.propuesta_tipo' => [
                'required_without:requisicion.detalle.*.puesto_id',
                'string'
            ],
            'requisicion.detalle.*.cantidad_solicitada' => ['required_with:requisicion.detalle', 'integer', 'min:1'],
            'requisicion.detalle.*.disciplina_id' => ['required_with:requisicion.detalle', 'integer'],
            'requisicion.detalle.*.tipo_contrato' => ['required_with:requisicion.detalle', Rule::enum(TipoContratoEnum::class)],
            'requisicion.detalle.*.tabulador_id' => [
                'required_with:requisicion.detalle', 
                'integer',
                Rule::exists(TabuladorSalario::class, 'id')
            ],
            'requisicion.detalle.*.sueldo_asignado' => [
                'required_with:requisicion.detalle', 
                'numeric',
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
            'unidad_organizativa_id.required' => 'El campo :attribute es obligatorio.',
            'unidad_organizativa_id.integer' => 'El campo :attribute debe ser un número entero.',
            'unidad_organizativa_id.exists' => 'La unidad organizativa seleccionada no es válida o está inactiva.',
            
            'observaciones.string' => 'El campo :attribute debe ser una cadena de texto.',
            
            'proyecto_id.integer' => 'El campo :attribute debe ser un número entero.',
            'proyecto_id.exists' => 'El proyecto seleccionado no existe.',
            
            'requisicion.detalle.*.puesto_id.exists' => 'El puesto seleccionado no es válido o no pertenece a la unidad organizativa solicitada.',
            'requisicion.detalle.*.propuesta_nombre.unique' => 'El nombre del puesto propuesto ya existe en la unidad organizativa seleccionada.',
            'requisicion.detalle.*.propuesta_nombre.distinct' => 'El nombre del puesto propuesto no puede repetirse en la misma solicitud.',

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
            'unidad_organizativa_id' => 'unidad organizativa',
            'observaciones' => 'observaciones',
            'estado' => 'estado',
            'requisicion_padre_id' => 'requisición original (enmienda)',
        ];
    }

    public function toDTO(?int $id = null): SolicitudRequisicionDTO
    {
        $data = $this->validated();
        $data['elaborador_id'] = $this->user()->id_personal;

        if ($id !== null) {
            $data['id'] = $id;
        }

        return SolicitudRequisicionDTO::fromArray($data);
    }

}