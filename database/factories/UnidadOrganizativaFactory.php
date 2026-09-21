<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Domain\EstructuraOrganizacional\Models\UnidadOrganizativa;

class UnidadOrganizativaFactory extends Factory
{
    protected $model = UnidadOrganizativa::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->company(),
            'nivel' => 'direccion',
            'estado' => 'activo',
            'encargado_id' => $this->faker->randomNumber(4, true),
        ];
    }
}