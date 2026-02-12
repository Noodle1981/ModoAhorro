<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\ClimateService;

class ClimateServiceTest extends TestCase
{
    /**
     * Verifica el cálculo de CDD (Cooling Degree Days).
     * Base: 24°C
     */
    public function test_calculate_cdd()
    {
        $service = new ClimateService();

        // Caso 1: Temp promedio 30°C (Calor) -> Debería haber 6 CDD (30 - 24)
        $this->assertEquals(6.0, $service->calculateCDD(30));

        // Caso 2: Temp promedio 24°C (Límite) -> 0 CDD
        $this->assertEquals(0.0, $service->calculateCDD(24));

        // Caso 3: Temp promedio 20°C (Fresco) -> 0 CDD (No se prende aire)
        $this->assertEquals(0.0, $service->calculateCDD(20));
    }

    /**
     * Verifica el cálculo de HDD (Heating Degree Days).
     * Base: 18°C
     */
    public function test_calculate_hdd()
    {
        $service = new ClimateService();

        // Caso 1: Temp promedio 10°C (Frío) -> Debería haber 8 HDD (18 - 10)
        $this->assertEquals(8.0, $service->calculateHDD(10));

        // Caso 2: Temp promedio 18°C (Límite) -> 0 HDD
        $this->assertEquals(0.0, $service->calculateHDD(18));

        // Caso 3: Temp promedio 25°C (Calor) -> 0 HDD (No se prende estufa)
        $this->assertEquals(0.0, $service->calculateHDD(25));
    }
}
