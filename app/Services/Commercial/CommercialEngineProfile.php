<?php

namespace App\Services\Commercial;

use App\Models\Entity;

interface CommercialEngineProfile
{
    /**
     * Categorías de equipos que se consideran críticas (Tanque 1).
     */
    public function getCriticalCategories(): array;

    /**
     * Categorías de equipos de proceso o clima (Tanque 2).
     */
    public function getProcessCategories(): array;

    /**
     * Coeficiente de uso para visitantes/comensales.
     */
    public function getVisitorsSocialCoefficient(): float;

    /**
     * Unidad de medida para visitantes (comensales, clientes, etc).
     */
    public function getVisitorsUnitLabel(): string;

    /**
     * Determina si un equipo debe ser procesado como proceso clave o térmico.
     */
    public function isCoreProcess(\App\Models\Equipment $equipment): bool;

    /**
     * Multiplicador base para el consumo vampiro/standby.
     */
    public function getStandbyMultiplier(): float;

    /**
     * Calcula la carga operativa basada en turnos, visitantes y staff.
     */
    public function calculateOperationalLoad(array $context): float;
}
