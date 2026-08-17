<?php

namespace App\Domain\Commercial\Registry;

use App\Domain\Commercial\Contracts\CommercialProfileInterface;
use App\Domain\Commercial\Profiles\Gastronomy\CoffeeShopProfile;
use App\Domain\Commercial\Profiles\Gastronomy\IceCreamShopProfile;
use App\Domain\Commercial\Profiles\Gastronomy\PizzeriaProfile;
use App\Domain\Commercial\Profiles\Gastronomy\RestaurantProfile;
use App\Domain\Commercial\Profiles\Office\CorporateOfficeProfile;
use App\Domain\Commercial\Profiles\Retail\RetailShopProfile;
use App\Models\Entity;
use App\Services\Commercial\CommercialEngineProfile;

class CommercialProfileRegistry
{
    /**
     * @var array<string, CommercialProfileInterface>
     */
    protected array $profiles = [];

    public function __construct()
    {
        $this->registerDefaults();
    }

    /**
     * Registra los perfiles estándar disponibles en el sistema.
     */
    protected function registerDefaults(): void
    {
        $this->register(new IceCreamShopProfile());
        $this->register(new PizzeriaProfile());
        $this->register(new CoffeeShopProfile());
        $this->register(new RestaurantProfile());
        $this->register(new RetailShopProfile());
        $this->register(new CorporateOfficeProfile());
    }

    /**
     * Registra un nuevo perfil comercial en el catálogo.
     */
    public function register(CommercialProfileInterface $profile): self
    {
        $this->profiles[$profile->getSubcategoryKey()] = $profile;

        return $this;
    }

    /**
     * Obtiene un perfil por su clave de subcategoría.
     */
    public function get(string $key): ?CommercialProfileInterface
    {
        return $this->profiles[$key] ?? null;
    }

    /**
     * Resuelve el perfil adecuado para una entidad de forma polimórfica y retrocompatible.
     */
    public function resolveForEntity(Entity $entity): ?CommercialEngineProfile
    {
        if ($entity->type === 'oficina') {
            return $this->get('oficina_servicios');
        }

        if ($entity->type !== 'comercio') {
            return null;
        }

        // 1. Si la entidad tiene sub-rubro específico configurado (nuevo estándar)
        if (! empty($entity->business_subcategory) && isset($this->profiles[$entity->business_subcategory])) {
            return $this->profiles[$entity->business_subcategory];
        }

        // 2. Mapeo retrocompatible con 'comercio_type' legacy
        return match ($entity->comercio_type) {
            'heladeria', 'heladeria_artesanal' => $this->get('heladeria_artesanal'),
            'pizzeria', 'rotiseria' => $this->get('pizzeria'),
            'cafeteria', 'bar' => $this->get('cafeteria'),
            'gastronomia' => $this->get('restaurante_general') ?? $this->get('heladeria_artesanal'),
            'retail' => $this->get('retail_general'),
            'oficina' => $this->get('oficina_servicios'),
            default => $this->get('retail_general'),
        };
    }

    /**
     * Retorna todos los perfiles registrados agrupados jerárquicamente por rubro macro.
     */
    public function getHierarchicalCatalog(): array
    {
        $catalog = [];

        foreach ($this->profiles as $profile) {
            $catKey = $profile->getCategoryKey();

            if (! isset($catalog[$catKey])) {
                $catalog[$catKey] = [
                    'key' => $catKey,
                    'label' => $profile->getCategoryLabel(),
                    'subcategories' => [],
                ];
            }

            $catalog[$catKey]['subcategories'][] = [
                'key' => $profile->getSubcategoryKey(),
                'label' => $profile->getSubcategoryLabel(),
                'description' => $profile->getDescription(),
                'standby_multiplier' => $profile->getStandbyMultiplier(),
                'thermal_sensitivity' => $profile->getThermalSensitivity(),
                'visitors_unit' => $profile->getVisitorsUnitLabel(),
                'suggested_appliances' => $profile->getSuggestedAppliances(),
            ];
        }

        return array_values($catalog);
    }
}
