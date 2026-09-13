<?php

namespace App\Domain\Commercial\Profiles;

use App\Domain\Commercial\Contracts\CommercialProfileInterface;
use App\Models\Equipment;
use App\Services\Commercial\AbstractCommercialProfile;

abstract class AbstractModularCommercialProfile extends AbstractCommercialProfile implements CommercialProfileInterface
{
    public function getCategoryKey(): string
    {
        return 'general';
    }

    public function getCategoryLabel(): string
    {
        return 'Comercio General';
    }

    public function getDescription(): string
    {
        return 'Perfil operativo comercial genérico.';
    }

    public function getThermalSensitivity(): float
    {
        return 1.0;
    }

    public function getVisitorsSocialCoefficient(): float
    {
        return 0.10;
    }

    public function getVisitorsUnitLabel(): string
    {
        return 'clientes';
    }

    public function getSuggestedAppliances(): array
    {
        return [];
    }

    public function getCustomFields(): array
    {
        return [];
    }

    public function isCoreProcess(Equipment $equipment): bool
    {
        $category = $equipment->category?->name ?? '';

        return in_array($category, $this->getProcessCategories());
    }
}
