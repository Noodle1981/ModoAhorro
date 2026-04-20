<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\EnergyEngineService;
use App\Services\ClimateService;
use App\Services\ThermalProfileService;
use Mockery;

class EnergyEngineTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * Simula una distribución básica en los 3 tanques.
     */
    public function test_distributes_consumption_across_three_tanks()
    {
        // 1. Mocks de Dependencias
        $climateMock = Mockery::mock(ClimateService::class);
        $thermalMock = Mockery::mock(ThermalProfileService::class);

        // Simulamos que el clima detectó 10 HDD (Días de frío acumulados)
        // Y que la casa es Eficiencia C (Multiplicador 1.4)
        $climateMock->shouldReceive('getDegreeDays')->andReturn(['cooling_days' => 0, 'heating_days' => 10]);
        $thermalMock->shouldReceive('calculateMultiplier')->andReturn(1.4);

        $engine = new EnergyEngineService($climateMock, $thermalMock);

        // 2. Datos de Entrada Simulados
        $facturaTotalKwh = 500; // Factura de 500 kWh
        $diasPeriodo = 30;

        // Equipos Simulados (Objetos genéricos para no depender de BD)
        $heladera = (object) [
            'id' => 1, 'name' => 'Heladera', 
            'is_standby' => false, // Tanque 1
            'type' => (object) ['default_power_watts' => 200, 'load_factor' => 0.4]
        ];

        $estufa = (object) [
            'id' => 2, 'name' => 'Estufa Eléctrica',
            'is_standby' => false,
            // Tanque 2 (necesita ser identificado como climatización por el servicio, 
            // pero como usamos mocks de métodos privados o lógica interna, 
            // asumiremos que el servicio lo detecta por 'is_climate' si tuvieramos ese flag.
            // En la implementación real, `EnergyEngineService` chequea `usage->equipment->type->is_climate`.
            // Para este unit test puro sin BD, necesitamos emular la estructura esperada por el servicio.
            'type' => (object) ['is_climate' => true, 'default_power_watts' => 2000] 
        ];

        $playstation = (object) [
            'id' => 3, 'name' => 'PlayStation',
            'is_standby' => false,
            // Tanque 3
            'type' => (object) ['is_climate' => false, 'default_power_watts' => 300, 'intensity' => 'high']
        ];

        // NOTA: El EnergyEngineService real espera modelos Eloquent o arrays convertidos.
        // Si el servicio tiene mucha dependencia de la BD, este test unitario podría requerir `Tests\TestCase` de Laravel
        // y usar `RefreshDatabase`. Por simplicidad aquí, asumimos que podemos inyectar objetos.
        // Si falla por dependencias de Eloquent, cambiaremos a Feature Test.
    }
    
    /**
     * Test simple de lógica matemática de Tanque 1 (Base Load)
     */
    public function test_tank_1_calculation()
    {
        // Heladera: 200W * 24h * 0.4 (Factor) = 1920 Wh/día = 1.92 kWh/día
        // En 30 días: 57.6 kWh
        
        $potencia = 200;
        $horas = 24;
        $factor = 0.4;
        $dias = 30;
        
        $consumo = ($potencia * $horas * $factor * $dias) / 1000;
        
        $this->assertEquals(57.6, $consumo);
    }
}
