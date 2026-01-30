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

    public function getClimateDays()
    {
        return $this->gradosDia;
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

        // Calcular Consumo Teórico Total
        $totalTeorico = array_sum(array_column($equipos, 'consumo_teorico'));
        
        // --- DETECCIÓN DE ESCENARIO ---
        // Si el teórico ya supera la factura, entramos en MODO DÉFICIT (Recorte)
        // en lugar de MODO SUPERÁVIT (Llenado).
        if ($totalTeorico > $this->facturaTotal) {
            $this->logs[] = "📉 MODO DÉFICIT: Tu consumo calculado ($totalTeorico kWh) supera la factura ({$this->facturaTotal} kWh). Se aplicará reducción proporcional inteligente.";
            $this->procesarEscenarioDeficit($equipos, $remanenteFactura);
        } else {
            // --- TANQUE 2: Climatización (Ajuste por Clima e Impacto Térmico) ---
            $this->procesarTanqueClima($equipos, $remanenteFactura);

            // --- TANQUE 3: Rutina y Ocio (Ajuste Final por Elasticidad y CONFIANZA) ---
            if ($remanenteFactura != 0) {
                $this->procesarTanqueRutina($equipos, $remanenteFactura);
            }
        }

        // Recalcular remanente final (debería tender a 0)
        $totalCalibrado = array_sum(array_column($equipos, 'calibrado_kwh'));
        $remanenteFactura = $this->facturaTotal - $totalCalibrado;

        // --- AUDITORIA DE VALORES GENÉRICOS ---
        $precisionSummary = $this->getPrecisionSummary($equipos);
        
        // Agregar logs adicionales de precisión
        if ($precisionSummary['real_adjusted_count'] > 0) {
            $this->logs[] = "⚠️ ALERTA: Tuvimos que ajustar {$precisionSummary['real_adjusted_count']} equipos marcados como REALES para cerrar el balance.";
        } else {
            $this->logs[] = "✅ Balance cerrado respetando tus valores validados.";
        }

        return [
            'factura_real' => $this->facturaTotal,
            'remanente_final' => round($remanenteFactura, 4),
            'equipos' => $equipos,
            'logs' => $this->logs,
            'status' => abs($remanenteFactura) < 0.1 ? 'exitoso' : 'requiere_revision',
            'precision_summary' => $precisionSummary,
            'climate_data' => $this->gradosDia // Exponer datos climáticos para la vista
        ];
    }

    /**
     * Estrategia de Reducción Proporcional (Cuando Teórico > Factura)
     */
    protected function procesarEscenarioDeficit(&$equipos, &$remanenteFactura)
    {
        // 1. Proteger Tanque 1 (Base Inmutable)
        $consumoTanque1 = 0;
        foreach ($equipos as $eq) {
            if ($eq['tanque'] === 1) $consumoTanque1 += $eq['calibrado_kwh'];
        }

        // 2. Definir Presupuesto Restante para Tanques 2 y 3
        $presupuestoDisponible = max(0, $this->facturaTotal - $consumoTanque1);
        
        // 3. Identificar Candidatos a Recorte (Todos los que NO sean Tanque 1)
        $candidatos = [];
        $consumoCandidatosTeorico = 0;
        
        foreach ($equipos as &$eq) {
            if ($eq['tanque'] !== 1) {
                // Factor de Sensibilidad al Recorte
                // Validado (Real) -> 0.2 (Dificil de recortar)
                // Sugerido      -> 1.0 (Facil de recortar)
                $sensibilidad = ($eq['is_validated'] ?? false) ? 0.2 : 1.0;
                
                // Peso de Recorte = Tamaño del Consumo * Sensibilidad
                // Equipos grandes y no validados sufren mayor recorte absoluto
                $eq['_peso_recorte'] = $eq['consumo_teorico'] * $sensibilidad;
                
                $candidatos[] = &$eq;
                $consumoCandidatosTeorico += $eq['consumo_teorico'];
            }
        }
        unset($eq);

        // Si no hay candidatos o presupuesto, salir
        if (empty($candidatos)) return;

        // 4. Calcular el Déficit a cubrir
        $deficit = $consumoCandidatosTeorico - $presupuestoDisponible;
        
        if ($deficit <= 0) {
            // Caso raro: Tanque 1 consumió casi todo, y lo que queda alcanza para cubrir el teórico de T2+T3
            // Asignamos teórico directo
            foreach ($candidatos as &$eq) {
                $eq['calibrado_kwh'] = $eq['consumo_teorico'];
                $eq['ajustado'] = true;
            }
            return;
        }

        // 5. Distribuir el Recorte
        $sumaPesosRecorte = array_sum(array_column($candidatos, '_peso_recorte'));
        if ($sumaPesosRecorte == 0) $sumaPesosRecorte = 1;

        foreach ($candidatos as &$eq) {
            $ratio = $eq['_peso_recorte'] / $sumaPesosRecorte;
            $montoRecorte = $deficit * $ratio;
            
            // Aplicar recorte, asegurando no bajar de 0
            $nuevoConsumo = max(0, $eq['consumo_teorico'] - $montoRecorte);
            
            $eq['calibrado_kwh'] = $nuevoConsumo;
            $eq['ajustado'] = true;
            
            // Log solo para recortes significativos
            if ($montoRecorte > 5) {
                $tipo = ($eq['is_validated'] ?? false) ? "(Real)" : "(Sugerido)";
                $this->logs[] = "✂️ Recorte: '{$eq['nombre']}' $tipo reducido en " . round($montoRecorte, 1) . " kWh para ajustar al presupuesto.";
            }
        }
    }

    /**
     * Lógica del Tanque 2 con Grados-Día reales y Distribución Proporcional
     */
    protected function procesarTanqueClima(&$equipos, &$remanenteFactura)
    {
        // Impacto térmico según categoría A-E
        $impactoTermico = [
            'A' => 0.20, 'B' => 0.40, 'C' => 0.60, 'D' => 0.85, 'E' => 1.00
        ][$this->categoriaHogar] ?? 0.70;

        $candidatos = [];
        $totalAjusteRequerido = 0;
        $consumoBaseT2 = 0;

        // PASO 1: Calcular necesidades teóricas
        foreach ($equipos as &$eq) {
            if ($eq['tanque'] === 2 && !$eq['ajustado']) {
                $category = $eq['es_climatizacion'] ? 'Climatización' : 'Otro'; // Idealmente pasar category name
                // Mapeo por categoría para consistencia
                // Si es Climatización -> Cooling Days
                // Si fuera Calefacción (que en este motor entra como T2 si es_climatizacion=true) -> Heating Days
                // Asumimos 'es_climatizacion' true para ambos grupos.
                // Usamos el nombre o tipo para distinguir, o si tenemos los gradosDia, usamos el mayor?
                // Mejor: Si el equipo tiene flag de Clima, usamos CoolingDays por defecto para Aire, Heating para Estufa.
                $name = strtolower($eq['nombre']);
                $isCooling = str_contains($name, 'aire') || str_contains($name, 'ventilador') || str_contains($name, 'split');
                
                $gradosRelevantes = $isCooling 
                                    ? ($this->gradosDia['cooling_days'] ?? 0) 
                                    : ($this->gradosDia['heating_days'] ?? 0);

                // Fórmula Física v3
                $ajusteIdeal = ($gradosRelevantes * $impactoTermico * ($eq['potencia_w'] / 1000) * (($eq['horas_declaradas'] ?? 0) / 24));
                
                // CAP 30% individual
                $maxAumento = $eq['consumo_teorico'] * 0.30;
                $ajusteSolicitado = min($ajusteIdeal, $maxAumento);
                
                $eq['_req_clima'] = $ajusteSolicitado;
                $candidatos[] = &$eq;
                $totalAjusteRequerido += $ajusteSolicitado;
                $consumoBaseT2 += $eq['consumo_teorico'];
            }
        }
        unset($eq);

        if (empty($candidatos)) return;

        // PASO 2: Verificar presupuesto
        // Remanente actual = Factura - T1.
        // T2 debe consumir: Teórico + Ajuste.
        // Remanente disponible para Ajuste = RemanenteActual - TeóricoT2.
        
        $remanenteParaAjuste = $remanenteFactura - $consumoBaseT2;
        
        $ratio = 1.0;
        if ($remanenteParaAjuste < $totalAjusteRequerido) {
            // Si hay menos remanente que lo solicitado, escalamos
            // (Si es negativo, ratio será 0 o negativo -> lo manejamos con max(0))
            $ratio = ($remanenteParaAjuste > 0) ? ($remanenteParaAjuste / $totalAjusteRequerido) : 0;
        }

        // PASO 3: Aplicar
        foreach ($candidatos as &$eq) {
            $ajusteFinal = $eq['_req_clima'] * $ratio;
            
            $eq['calibrado_kwh'] = $eq['consumo_teorico'] + $ajusteFinal;
            
             // Seguridad final: No podemos consumir más de lo que queda globalmente
            if ($eq['calibrado_kwh'] > $remanenteFactura) {
                // Caso extremo donde ni el teórico entra
                 $eq['calibrado_kwh'] = $remanenteFactura; 
            }

            $eq['ajustado'] = true;
            $remanenteFactura -= $eq['calibrado_kwh'];
            
            $pct = round($ratio * 100);
            $this->logs[] = "Tanque 2: '{$eq['nombre']}' ajustado " . round($ajusteFinal, 2) . " kWh (Ratio: {$pct}%). Total: " . round($eq['calibrado_kwh'], 2);
        }
    }

    /**
     * Lógica del Tanque 3 (Rutina) - Absorbe el resto
     */
    protected function procesarTanqueRutina(&$equipos, &$remanenteFactura)
    {
        $candidatos = [];
        $consumoBaseT3 = 0;
        
        foreach ($equipos as &$eq) {
            if (!$eq['ajustado']) {
                $candidatos[] = &$eq;
                $consumoBaseT3 += $eq['consumo_teorico'];
            }
        }
        unset($eq);

        if (empty($candidatos)) return;

        // Distribución del remanente final (Superávit o Déficit leve)
        // El remanenteDeFactura DEBE ser consumido por T3.
        // Meta T3 = RemanenteFactura.
        // Diferencia = RemanenteFactura - TeóricoT3.
        
        $differencia = $remanenteFactura - $consumoBaseT3;
        
        // Repartir diferencia según elasticidad / tamaño
        $totalPeso = 0;
        foreach ($candidatos as &$eq) {
            $eq['_peso'] = $eq['consumo_teorico']; // Simplificado por tamaño
            $totalPeso += $eq['_peso'];
        }
        unset($eq);

        if ($totalPeso == 0) $totalPeso = 1;

        foreach ($candidatos as &$eq) {
            $ratio = $eq['_peso'] / $totalPeso;
            $ajuste = $differencia * $ratio;
            
            $eq['calibrado_kwh'] = max(0, $eq['consumo_teorico'] + $ajuste);
            $eq['ajustado'] = true;
            $remanenteFactura -= $eq['calibrado_kwh'];
        }
        
        $this->logs[] = "Tanque 3: Ajuste final de " . round($differencia, 2) . " kWh distribuido entre " . count($candidatos) . " equipos.";
    }
    /**
     * Genera resumen de precisión y confianza
     */
    protected function getPrecisionSummary($equiposCalibrados)
    {
        $realCount = 0;
        $suggestedCount = 0;
        $realAdjustedCount = 0; // Cuántos reales sufrieron cambios significativos

        foreach ($equiposCalibrados as $eq) {
            if ($eq['is_validated'] ?? false) {
                $realCount++;
                // Detectar si fue ajustado significativamente (>5% y >1kWh)
                $diff = abs(($eq['calibrado_kwh'] ?? 0) - ($eq['consumo_teorico'] ?? 0));
                if ($diff > 1 && $diff > ($eq['consumo_teorico'] * 0.05)) {
                    $realAdjustedCount++;
                }
            } else {
                $suggestedCount++;
            }
        }
        
        $message = "Se ha respetado la potencia de tus equipos validados ($realCount), ajustando principalmente los valores estimados ($suggestedCount) para cerrar el balance de la factura.";

        return [
            'real_count' => $realCount,
            'suggested_count' => $suggestedCount,
            'real_adjusted_count' => $realAdjustedCount,
            'message' => $message
        ];
    }
}
