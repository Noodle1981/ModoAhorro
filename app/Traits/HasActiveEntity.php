<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait HasActiveEntity
{
    /**
     * Get the active entity for the current user session.
     * Centralizes the logic used across multiple controllers.
     */
    protected function getActiveEntity(Request $request)
    {
        $user = $request->user();
        $activeEntityId = session('active_entity_id');

        $entity = null;
        if ($activeEntityId) {
            $entity = $user->entities()->where('entities.id', $activeEntityId)->first();
        }

        if (!$entity) {
            $entity = $user->entities()->first();
            if ($entity) {
                session(['active_entity_id' => $entity->id]);
            }
        }

        return $entity;
    }
}
