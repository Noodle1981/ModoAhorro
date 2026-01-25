<?php

namespace App\Services;

class EnergyEngineService
{
    protected $facturaTotal;
    protected $diasPeriodo;
    protected $categoriaHogar;
    protected $gradosDia;
    protected $logs = [];

    // Diccionario de Periodicidad (Frecuencia de uso)
    protected $periodicidadMap = [
        "diariamente"         => 1.0,
        "diario"              => 1.0,
        "casi_frecuentemente" => 0.8,
        "frecuentemente"      => 0.6,
        "semanal"             => 0.6,
        "ocasionalmente"      => 0.3,
        "quincenal"           => 0.3,
        "raramente"           => 0.1,
        "mensual"             => 0.1,
        "nunca"               => 0.0,
        "puntual"             => 0.05
    ];

    // Matriz de Elasticidad (Prioridad de Ajuste para Tanque 3)
    protected $elasticidadMap = [
        "bajo"      => 0.1,
        "medio"     => 0.3,
        "alto"      => 0.6,
        "excesivo"  => 0.9
    ];

    /**
     * @param float $facturaKwh Total de energía consumida real.
     * @param int $diasPeriodo Días entre inicio y fin de factura.
     * @param string $categoriaHogar A, B, C, D, E.
     * @param array $gradosDia ['cooling_days' => X, 'heating_days' => Y]
     */
    public function setData($facturaKwh, $diasPeriodo, $categoriaHogar = 'C', $gradosDia = ['cooling_days' => 0, 'heating_days' => 0])
    {
        $this->facturaTotal = (float) $facturaKwh;
        $this->diasPeriodo = (int) $diasPeriodo;
        $this->categoriaHogar = $categoriaHogar;
        $this->gradosDia = $gradosDia;
        return $this;
    }

    /**
     * Motor principal de calibración v3.0
     * 
     * @param array $equipos Lista de equipos con sus metadatos.
     * @return array
     */
    public function calibrate(array $equipos)
    {
        $remanenteFactura = $this->facturaTotal;
        $this->logs = [];

        // --- PASO 0: Identificación Automática de Tanques y Cálculo Teórico ---
        foreach ($equipos as &$eq) {
            $frecuencia = $this->periodicidadMap[$eq['periodicidad'] ?? 'frecuentemente'] ?? 0.6;
            
            // Consumo Físico Teórico: (Watts * Horas * (Dias * Frecuencia) * LoadFactor) / 1000
            $eq['consumo_teorico'] = ($eq['potencia_w'] * ($eq['horas_declaradas'] ?? 0) * 
                                     ($this->diasPeriodo * $frecuencia) * 
                                     ($eq['load_factor'] ?? 1.0)) / 1000;
            
            $eq['calibrado_kwh'] = 0;
            $eq['ajustado'] = false;

            // REGLA DE ORO v3: Identificación de Tanques
            // Tanque 1: 24/7, Diario, NO Clima
            if (($eq['horas_declaradas'] ?? 0) == 24 && 
                ($eq['periodicidad'] ?? '') === 'diariamente' && 
                !($eq['es_climatizacion'] ?? false)) {
                $eq['tanque'] = 1;
                $eq['elasticidad'] = 0;
            } 
            elseif ($eq['es_climatizacion'] ?? false) {
                $eq['tanque'] = 2;
            } 
            else {
                $eq['tanque'] = 3;
            }
        }

        // --- TANQUE 1: Base Automática (Inmutable) ---
        foreach ($equipos as &$eq) {
            if ($eq['tanque'] === 1) {
                $eq['calibrado_kwh'] = $eq['consumo_teorico'];
                $eq['ajustado'] = true;
                $remanenteFactura -= $eq['calibrado_kwh'];
                
                $this->logs[] = "Tanque 1: Fijado '{$eq['nombre']}' como base inmutable (24/7). Consumo: " . round($eq['calibrado_kwh'], 2) . " kWh.";
            }
        }

        // Control de Sanidad Tanque 1
        if ($remanenteFactura < 0) {
            $this->logs[] = "ALERTA: El consumo base inmutable supera el total de la factura en " . abs(round($remanenteFactura, 2)) . " kWh. Revisar datos de equipos 24/7.";
        }

        // --- TANQUE 2: Climatización (Ajuste por Clima e Impacto Térmico) ---
        $this->procesarTanqueClima($equipos, $remanenteFactura);

        // --- TANQUE 3: Rutina y Ocio (Ajuste Final por Elasticidad) ---
        if ($remanenteFactura != 0) {
            $this->procesarTanqueRutina($equipos, $remanenteFactura);
        }

        return [
            'factura_real' => $this->facturaTotal,
            'remanente_final' => round($remanenteFactura, 4),
            'equipos' => $equipos,
            'logs' => $this->logs,
            'status' => abs($remanenteFactura) < 0.1 ? 'exitoso' : 'requiere_revision'
        ];
    }

    /**
     * Lógica del Tanque 2 con Grados-Día reales
     */
    protected function procesarTanqueClima(&$equipos, &$remanenteFactura)
    {
        // Impacto térmico según categoría A-E
        $impactoTermico = [
            'A' => 0.20, 'B' => 0.40, 'C' => 0.60, 'D' => 0.85, 'E' => 1.00
        ][$this->categoriaHogar] ?? 0.70;

        foreach ($equipos as &$eq) {
            if ($eq['tanque'] === 2 && !$eq['ajustado']) {
                $gradosRelevantes = ($eq['tipo_clima'] === 'frio') 
                                    ? ($this->gradosDia['cooling_days'] ?? 0) 
                                    : ($this->gradosDia['heating_days'] ?? 0);

                // Fórmula Física v3: El clima y la casa modulan el consumo real sobre el teórico
                // Si gradosRelevantes es alto, el equipo trabajó más.
                $ajusteClimatico = ($gradosRelevantes * $impactoTermico * ($eq['potencia_w'] / 1000) * (($eq['horas_declaradas'] ?? 0) / 24));
                
                $eq['calibrado_kwh'] = $eq['consumo_teorico'] + $ajusteClimatico;
                
                // Seguridad: El clima no puede absorber el 100% si quedan otros equipos
                $maxPermitido = $remanenteFactura > 0 ? $remanenteFactura * 0.9 : $eq['consumo_teorico'];
                $eq['calibrado_kwh'] = max(0, min($eq['calibrado_kwh'], $maxPermitido));

                $remanenteFactura -= $eq['calibrado_kwh'];
                $eq['ajustado'] = true;
                
                $this->logs[] = "Tanque 2: Ajustado '{$eq['nombre']}' por clima (" . round($gradosRelevantes, 1) . " GD) y perfil '{$this->categoriaHogar}'. Consumo: " . round($eq['calibrado_kwh'], 2) . " kWh.";
            }
        }
    }

    /**
     * Lógica del Tanque 3: Distribución del remanente por Elasticidad
     */
    protected function procesarTanqueRutina(&$equipos, &$remanenteFactura)
    {
        $equiposTanque3 = array_filter($equipos, fn($e) => $e['tanque'] === 3);
        if (empty($equiposTanque3)) return;

        $sumaElasticidad = 0;
        foreach ($equiposTanque3 as $eq) {
            $sumaElasticidad += $this->elasticidadMap[$eq['intensity'] ?? 'medio'] ?? 0.3;
        }

        if ($sumaElasticidad == 0) $sumaElasticidad = 1;

        foreach ($equipos as &$eq) {
            if ($eq['tanque'] === 3) {
                $elasticidad = $this->elasticidadMap[$eq['intensity'] ?? 'medio'] ?? 0.3;
                $porcentajeReparto = $elasticidad / $sumaElasticidad;
                
                // Si el remanente es positivo (sobra factura), se suma. Si es negativo, se resta.
                $ajuste = $remanenteFactura * $porcentajeReparto;
                $eq['calibrado_kwh'] = max(0, $eq['consumo_teorico'] + $ajuste);
                $eq['ajustado'] = true;
                
                $diff = $eq['calibrado_kwh'] - $eq['consumo_teorico'];
                if (abs($diff) > 0.1) {
                    $accion = $diff > 0 ? "incrementado" : "reducido";
                    $this->logs[] = "Tanque 3: Se ha $accion '{$eq['nombre']}' para cerrar balance con factura (Elasticidad: $elasticidad).";
                }
            }
        }

        // Recalcular remanente final (debería tender a 0)
        $totalCalibrado = array_sum(array_column($equipos, 'calibrado_kwh'));
        $remanenteFactura = $this->facturaTotal - $totalCalibrado;
    }
}
