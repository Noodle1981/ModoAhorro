<?php

namespace App\Services\Commercial;

use App\Models\Equipment;

abstract class AbstractCommercialProfile implements CommercialEngineProfile
{
    /**
     * Implementación centralizada para evitar DRY. 
     * Verifica si el equipo pertenece a las categorías de proceso de este perfil.
     */
    public function isCoreProcess(Equipment $equipment): bool
    {
        $category = $equipment->category?->name ?? '';
        return in_array($category, $this->getProcessCategories());
    }

    /**
     * Default standby multiplier.
     */
    public function getStandbyMultiplier(): float
    {
        return 1.0;
    }

    /**
     * Default operational load calculation.
     */
    public function calculateOperationalLoad(array $context): float
    {
        return 1.0;
    }
}
