<?php

namespace Tests\Unit;

use App\Domain\Commercial\Contracts\CommercialProfileInterface;
use App\Domain\Commercial\Profiles\Gastronomy\IceCreamShopProfile;
use App\Domain\Commercial\Profiles\Gastronomy\PizzeriaProfile;
use App\Domain\Commercial\Registry\CommercialProfileRegistry;
use App\Models\Entity;
use PHPUnit\Framework\TestCase;

class CommercialProfileRegistryTest extends TestCase
{
    protected CommercialProfileRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registry = new CommercialProfileRegistry;
    }

    public function test_it_registers_default_commercial_profiles(): void
    {
        $iceCream = $this->registry->get('heladeria_artesanal');
        $this->assertInstanceOf(CommercialProfileInterface::class, $iceCream);
        $this->assertInstanceOf(IceCreamShopProfile::class, $iceCream);
        $this->assertEquals('Heladería Artesanal & Fábrica de Frío', $iceCream->getSubcategoryLabel());
        $this->assertEquals('gastronomia', $iceCream->getCategoryKey());

        $pizzeria = $this->registry->get('pizzeria');
        $this->assertInstanceOf(PizzeriaProfile::class, $pizzeria);
        $this->assertEquals('pizzeria', $pizzeria->getSubcategoryKey());
    }

    public function test_ice_cream_profile_has_correct_thermal_and_standby_physics(): void
    {
        $iceCream = $this->registry->get('heladeria_artesanal');

        // La heladería debe tener carga continua 24/7 alta por pozos y cámaras
        $this->assertGreaterThan(1.2, $iceCream->getStandbyMultiplier());
        $this->assertEquals(1.35, $iceCream->getStandbyMultiplier());

        // La heladería es altamente sensible a olas de calor en verano
        $this->assertGreaterThan(1.3, $iceCream->getThermalSensitivity());
        $this->assertEquals(1.45, $iceCream->getThermalSensitivity());

        // Incluye refrigeración en categorías críticas
        $this->assertContains('Refrigeración Comercial', $iceCream->getCriticalCategories());
        $this->assertContains('Cadena de Frío Negativo', $iceCream->getCriticalCategories());

        // Sugiere artefactos típicos de heladería
        $suggested = $iceCream->getSuggestedAppliances();
        $this->assertNotEmpty($suggested);
        $names = array_column($suggested, 'name');
        $this->assertContains('Mantecadora de Helado Trifásica', $names);
    }

    public function test_it_generates_hierarchical_catalog_for_frontend(): void
    {
        $catalog = $this->registry->getHierarchicalCatalog();

        $this->assertIsArray($catalog);
        $this->assertNotEmpty($catalog);

        $keys = array_column($catalog, 'key');
        $this->assertContains('gastronomia', $keys);
        $this->assertContains('retail', $keys);
        $this->assertContains('oficina', $keys);

        // Gastronomía debe contener las subcategorías especializadas
        $gastronomy = collect($catalog)->firstWhere('key', 'gastronomia');
        $subKeys = array_column($gastronomy['subcategories'], 'key');
        $this->assertContains('heladeria_artesanal', $subKeys);
        $this->assertContains('pizzeria', $subKeys);
        $this->assertContains('cafeteria', $subKeys);
        $this->assertContains('restaurante_general', $subKeys);
    }

    public function test_it_resolves_profile_by_subcategory_and_fallback(): void
    {
        // 1. Entidad con subcategoría explícita
        $entity1 = new Entity([
            'type' => 'comercio',
            'business_category' => 'gastronomia',
            'business_subcategory' => 'heladeria_artesanal',
        ]);
        $profile1 = $this->registry->resolveForEntity($entity1);
        $this->assertInstanceOf(IceCreamShopProfile::class, $profile1);

        // 2. Entidad con legacy comercio_type = gastronomia
        $entity2 = new Entity([
            'type' => 'comercio',
            'comercio_type' => 'gastronomia',
        ]);
        $profile2 = $this->registry->resolveForEntity($entity2);
        $this->assertNotNull($profile2);

        // 3. Entidad de tipo oficina
        $entity3 = new Entity([
            'type' => 'oficina',
        ]);
        $profile3 = $this->registry->resolveForEntity($entity3);
        $this->assertEquals('oficina', $profile3->getCategoryKey());

        // 4. Entidad de tipo hogar (no comercial)
        $entity4 = new Entity([
            'type' => 'hogar',
        ]);
        $this->assertNull($this->registry->resolveForEntity($entity4));
    }
}
