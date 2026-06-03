<?php

namespace App\App\Api\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transforma el DTO/Modelo en una respuesta JSON estructurada.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        // El Resource puede recibir tanto un Modelo como un DTO
        // ya que ambos exponen las propiedades necesarias.
        return [
            'id'             => $this->id,
            'idPersonal'     => $this->idPersonal,
            'usuario'        => $this->username,
            'nombreCompleto' => $this->name,
            'email'          => $this->email,
            'puestoActual'   => $this->puestoActual,
            'rutaFoto'       => $this->rutaFoto,
            'permisos'       => $this->permisos,
        ];
    }
}
