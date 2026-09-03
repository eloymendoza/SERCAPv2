<?php

namespace App\Domain\EstructuraOrganizacional\DTOs;

class UnidadOrganizativaDTO
{
    public function __construct(
        public readonly string $nivel,
        public readonly string $nombre,
        public readonly ?int $id = null,
        public readonly ?int $parentId = null,
        public readonly ?string $abreviatura = null,
        public readonly ?string $nombreCorto = null,
        public readonly ?string $rfc = null,
        public readonly ?int $encargadoId = null,
        public readonly ?string $enabledAt = null,
        public readonly ?string $disabledAt = null,
        public readonly ?int $reemplazaAId = null,
        public readonly string $estado = 'Activo',
        public readonly ?UnidadOrganizativaDTO $parent = null,
        public readonly ?array $children = null,
        public readonly ?array $encargado = null
    ) {}

    /**
     * Fabrica una instancia del DTO extrayendo el payload validado.
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            parentId: $data['parent_id'] ?? null,
            nivel: $data['nivel'],
            nombre: $data['nombre'],
            abreviatura: $data['abreviatura'] ?? null,
            nombreCorto: $data['nombre_corto'] ?? null,
            rfc: $data['rfc'] ?? null,
            encargadoId: $data['encargado_id'] ?? null,
            enabledAt: $data['enabled_at'] ?? null,
            disabledAt: $data['disabled_at'] ?? null,
            reemplazaAId: $data['reemplaza_a_id'] ?? null,
            estado: $data['estado'] ?? 'Activo'
        );
    }
}