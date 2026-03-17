<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function __construct(
        private readonly User $model
    ) {}

    /**
     * Sincroniza el usuario con los datos ya mapeados.
     */
    public function syncExternalUser(array $mappedData): User
    {
        return $this->model->updateOrCreate(
            ['id_personal' => $mappedData['id_personal']], 
            $mappedData
        );
    }

    public function findById(int $id): ?User
    {
        return $this->model->find($id);
    }
}