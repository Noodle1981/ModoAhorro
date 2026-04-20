<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Entity;

class EntityPolicy
{
    /**
     * Determine whether the user can view the entity.
     */
    public function view(User $user, Entity $entity): bool
    {
        return $user->entities()->where('entity_id', $entity->id)->exists();
    }

    /**
     * Determine whether the user can update the entity.
     */
    public function update(User $user, Entity $entity): bool
    {
        return $user->entities()->where('entity_id', $entity->id)->exists();
    }

    /**
     * Determine whether the user can delete the entity.
     */
    public function delete(User $user, Entity $entity): bool
    {
        return $user->entities()->where('entity_id', $entity->id)->exists();
    }
}
