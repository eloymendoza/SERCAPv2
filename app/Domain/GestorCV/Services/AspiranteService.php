<?php

class AspiranteService
{
    // función tentativa cuando se llegue a requerir validar si el aspirante tiene un trabajo actual, entonces no aceptar fecha fin
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->experiencias ?? [] as $index => $exp) {
                if (($exp['trabajoActual'] ?? false) && !empty($exp['fechaFin'])) {
                    $validator->errors()->add(
                        "experiencias.$index.fechaFin",
                        'Si es el trabajo actual, no debe tener fecha de fin.'
                    );
                }
            }
        });
    }
}