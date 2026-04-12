<?php

namespace App\Observers;

use App\Models\EquipmentUsage;

class EquipmentUsageObserver
{
    /**
     * Handle the EquipmentUsage "saved" event.
     */
    public function saved(EquipmentUsage $equipmentUsage): void
    {
        $this->resetInvoiceCalibration($equipmentUsage);
    }

    /**
     * Handle the EquipmentUsage "deleted" event.
     */
    public function deleted(EquipmentUsage $equipmentUsage): void
    {
        $this->resetInvoiceCalibration($equipmentUsage);
    }

    /**
     * Resetea el estado de calibración de la factura relacionada
     */
    private function resetInvoiceCalibration(EquipmentUsage $equipmentUsage): void
    {
        if ($equipmentUsage->invoice_id) {
            $equipmentUsage->invoice()->update(['calibrated_at' => null]);
        }
    }
}
