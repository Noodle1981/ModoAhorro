<?php

namespace App\Policies;

use App\Models\Entity;
use App\Models\User;

class EntityPolicy
{
    /**
     * Determine if the user can view any entities of a specific type.
     */
    public function viewAny(User $user, string $entityType): bool
    {
        $plan = $user->currentPlan();

        if (!$plan) {
            return false;
        }

        return in_array($entityType, $plan->allowed_entity_types ?? []);
    }

    /**
     * Determine if the user can view the entity.
     */
    public function view(User $user, Entity $entity): bool
    {
        // El usuario puede ver la entidad si está asociada a él
        return $user->entities->contains($entity);
    }

    /**
     * Determine if the user can create a new entity of a specific type.
     */
    public function create(User $user, string $entityType): bool
    {
        $plan = $user->currentPlan();

        if (!$plan) {
            return false;
        }

        // 1. Verificar si el tipo de entidad está permitido en el plan
        if (!in_array($entityType, $plan->allowed_entity_types ?? [])) {
            return false;
        }

        // 2. Verificar límite de cantidad
        $currentCount = $user->entities()->count();
        if ($currentCount >= $plan->max_entities) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the user can update the entity.
     */
    public function update(User $user, Entity $entity): bool
    {
        return $user->entities->contains($entity);
    }

    /**
     * Determine if the user can delete the entity.
     */
    public function delete(User $user, Entity $entity): bool
    {
        return $user->entities->contains($entity);
    }
}
