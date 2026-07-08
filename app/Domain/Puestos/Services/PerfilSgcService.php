<?php

namespace App\Domain\Puestos\Services;

use App\Infrastructure\Clients\SgcClient;
use App\Domain\Puestos\DTOs\PerfilSgcDTO;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PerfilSgcService
{
    public function __construct(
        private readonly SgcClient $client
    ) {}

    /**
     * Obtiene y mapea los perfiles de puesto disponibles desde el SGC.
     *
     * @return Collection<int, PerfilSgcDTO>
     */
    public function getPerfilesActivos(): Collection
    {
        $data = $this->client->getPerfilesPuesto();
        
        return collect($data)->map(function (array $item) {
            return PerfilSgcDTO::fromArray($item);
        });
    }

    /**
     * Retorna los perfiles en un diccionario (keyBy 'id') usando caché por 1 hora
     * para optimizar búsquedas cruzadas sin golpear el API en cada consulta.
     *
     * @return Collection<int, PerfilSgcDTO>
     */
    public function getPerfilesActivosDiccionario(): Collection
    {
        return Cache::remember('sgc_perfiles_activos_dict', 3600, function () {
            return $this->getPerfilesActivos()->keyBy('id');
        });
    }
}