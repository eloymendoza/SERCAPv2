<?php

namespace App\Exceptions;

/**
 * Excepción para indicar que un modelo o recurso solicitado no existe.
 */
class ResourceNotFoundException extends BaseApiException
{
    /**
     * @param string $resource Nombre del tipo de recurso (ej: "Usuario").
     * @param string|null $id Identificador del recurso no encontrado.
     */
    public function __construct(string $resource = "Recurso", ?string $id = null)
    {
        $message = $id 
            ? "El {$resource} con identificador {$id} no fue encontrado." 
            : "El {$resource} solicitado no existe.";
            
        parent::__construct($message, 404, 'RESOURCE_NOT_FOUND');
    }
}