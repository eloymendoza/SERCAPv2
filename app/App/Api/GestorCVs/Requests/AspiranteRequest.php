<?php

namespace App\App\Api\GestorCVs\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AspiranteRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a hacer esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación.
     */
    public function rules(): array
    {
        return [
            // -------------------------------------------------------
            // Datos personales
            // -------------------------------------------------------
            'nombres'          => ['required', 'string', 'max:100'],
            'apellidoPaterno'  => ['required', 'string', 'max:100'],
            'apellidoMaterno'  => ['required', 'string', 'max:100'],
            'telefono'         => ['required', 'string', 'max:15'],
            'email'            => ['required', 'email', 'max:100'],
            'resumen'          => ['nullable', 'string'],
            'tipoAspirante'    => ['sometimes', 'string', 'in:nuevo_aspirante,personal_activo,personal_anterior'],

            // -------------------------------------------------------
            // Ubicación — se reciben como strings; ubicacion_id
            // se resuelve en el Service contra la BD de ubicaciones
            // -------------------------------------------------------
            'codigoPostal'     => ['nullable', 'string', 'max:10'],
            'estado'           => ['nullable', 'string', 'max:100'],
            'municipio'        => ['nullable', 'string', 'max:100'],
            'asentamiento'     => ['nullable', 'string', 'max:150'],

            // -------------------------------------------------------
            // Experiencia laboral
            // -------------------------------------------------------
            'experiencias'                       => ['nullable', 'array'],
            'experiencias.*.cargo'               => ['required_with:experiencias', 'string', 'max:150'],
            'experiencias.*.nombreEmpresa'       => ['required_with:experiencias', 'string', 'max:150'],
            'experiencias.*.trabajoActual'       => ['required_with:experiencias', 'boolean'],
            'experiencias.*.fechaInicio'         => ['required_with:experiencias', 'date'],
            'experiencias.*.fechaFin'            => [
                'nullable',
                'date',
                'after:experiencias.*.fechaInicio',
            ],
            'experiencias.*.responsabilidades'   => ['nullable', 'string'],

            // -------------------------------------------------------
            // Educación
            // -------------------------------------------------------
            'educacion'                          => ['nullable', 'array'],
            'educacion.*.institucion'            => ['required_with:educacion', 'string', 'max:200'],
            'educacion.*.nivelEstudioId'         => ['required_with:educacion', 'integer', 'exists:catalogo_nivel_estudio,nivel_estudio_id'],
            'educacion.*.titulo'                 => ['required_with:educacion', 'string', 'max:200'],
            'educacion.*.estadoEducacion'        => ['required_with:educacion', 'string', 'in:en_curso,titulado,concluido_sin_titulo,incompleto'],
            'educacion.*.anioFin'                => [
                'nullable',
                'integer',
                'min:1950',
                'max:2100',
            ],

            // -------------------------------------------------------
            // Certificados
            // -------------------------------------------------------
            'certificados'                       => ['nullable', 'array'],
            'certificados.*.nombre'              => ['required_with:certificados', 'string', 'max:255'],
            'certificados.*.institucion'         => ['nullable', 'string', 'max:150'],
            'certificados.*.anioFin'             => [
                'nullable',
                'integer',
                'min:1950',
                'max:2100',
            ],

            // -------------------------------------------------------
            // Conocimientos técnicos
            // -------------------------------------------------------
            'conocimientosTecnicos'              => ['nullable', 'array'],
            'conocimientosTecnicos.*.nombre'     => ['required_with:conocimientosTecnicos', 'string', 'max:100'],
            'conocimientosTecnicos.*.categoria'  => ['nullable', 'string', 'in:lenguaje,framework,herramienta,metodologia,sin_clasificar'],

            // -------------------------------------------------------
            // Idiomas
            // -------------------------------------------------------
            'idiomas'                            => ['nullable', 'array'],
            'idiomas.*.idiomaId'                 => ['required_with:idiomas', 'integer', 'exists:catalogo_idiomas,idioma_id'],
            'idiomas.*.nivel'                    => ['required_with:idiomas', 'string', 'in:basico,intermedio,avanzado,nativo'],
        ];
    }

    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            // Datos personales
            'nombres.required'         => 'El campo nombres es obligatorio.',
            'apellidoPaterno.required' => 'El apellido paterno es obligatorio.',
            'apellidoMaterno.required' => 'El apellido materno es obligatorio.',
            'telefono.required'        => 'El teléfono es obligatorio.',
            'email.required'           => 'El correo electrónico es obligatorio.',
            'email.email'              => 'El correo electrónico no tiene un formato válido.',
            'tipoAspirante.in'         => 'El tipo de aspirante no es válido.',

            // Experiencia
            'experiencias.*.cargo.required_with'         => 'El cargo es obligatorio en cada experiencia.',
            'experiencias.*.nombreEmpresa.required_with' => 'El nombre de la empresa es obligatorio en cada experiencia.',
            'experiencias.*.trabajoActual.required_with' => 'Indica si es el trabajo actual.',
            'experiencias.*.fechaInicio.required_with'   => 'La fecha de inicio es obligatoria en cada experiencia.',
            'experiencias.*.fechaInicio.date'            => 'La fecha de inicio no tiene un formato válido.',
            'experiencias.*.fechaFin.date'               => 'La fecha de fin no tiene un formato válido.',
            'experiencias.*.fechaFin.after'              => 'La fecha de fin debe ser posterior a la fecha de inicio.',

            // Educación
            'educacion.*.institucion.required_with'     => 'La institución es obligatoria en cada entrada de educación.',
            'educacion.*.nivelEstudioId.required_with'  => 'El nivel de estudio es obligatorio.',
            'educacion.*.nivelEstudioId.exists'         => 'El nivel de estudio seleccionado no es válido.',
            'educacion.*.titulo.required_with'          => 'El título o carrera es obligatorio.',
            'educacion.*.estadoEducacion.required_with' => 'El estado de educación es obligatorio.',
            'educacion.*.estadoEducacion.in'            => 'El estado de educación no es válido.',
            'educacion.*.anioFin.integer'               => 'El año de fin debe ser un número entero.',
            'educacion.*.anioFin.min'                   => 'El año de fin no puede ser anterior a 1950.',
            'educacion.*.anioFin.max'                   => 'El año de fin no puede ser posterior a 2100.',

            // Certificados
            'certificados.*.nombre.required_with' => 'El nombre del certificado es obligatorio.',
            'certificados.*.anioFin.integer'      => 'El año del certificado debe ser un número entero.',
            'certificados.*.anioFin.min'          => 'El año del certificado no puede ser anterior a 1950.',
            'certificados.*.anioFin.max'          => 'El año del certificado no puede ser posterior a 2100.',

            // Conocimientos técnicos
            'conocimientosTecnicos.*.nombre.required_with'    => 'El nombre del conocimiento es obligatorio.',
            'conocimientosTecnicos.*.categoria.in'            => 'La categoría del conocimiento no es válida.',

            // Idiomas
            'idiomas.*.idiomaId.required_with' => 'El idioma es obligatorio.',
            'idiomas.*.idiomaId.exists'        => 'El idioma seleccionado no existe en el catálogo.',
            'idiomas.*.nivel.required_with'    => 'El nivel del idioma es obligatorio.',
            'idiomas.*.nivel.in'               => 'El nivel del idioma no es válido. Usa: basico, intermedio, avanzado o nativo.',
        ];
    }

    /**
     * Prepara los datos antes de la validación.
     * Normaliza tipoAspirante y establece defaults seguros.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'tipoAspirante' => $this->tipoAspirante ?? 'nuevo_aspirante',
        ]);
    }
}