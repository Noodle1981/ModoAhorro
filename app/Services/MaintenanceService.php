<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\MaintenanceTask;
use App\Models\MaintenanceLog;
use Carbon\Carbon;

class MaintenanceService
{
    /**
     * Verifica el estado de mantenimiento de un equipo.
     * 
     * @param Equipment $equipment
     * @return array
     */
    public function checkStatus(Equipment $equipment): array
    {
        $type = $equipment->type;
        if (!$type) {
            return [
                'health_score' => 100,
                'pending_tasks' => [],
                'penalty_factor' => 1.0
            ];
        }

        $tasks = $type->maintenanceTasks;
        $pendingTasks = [];
        $totalPenalty = 0;
        $maxScore = 100;

        foreach ($tasks as $task) {
            $isOverdue = false;
            $lastLog = MaintenanceLog::where('equipment_id', $equipment->id)
                ->where('maintenance_task_id', $task->id)
                ->latest('completed_at')
                ->first();

            // 1. Verificación por Frecuencia (Días)
            if ($task->frequency_days) {
                if (!$lastLog) {
                    // Si no hay log, asumimos vencido si el equipo tiene cierta antigüedad (ej: 1 mes)
                    // Para simplificar, si no hay log y hay frecuencia, está pendiente/vencido.
                    $isOverdue = true;
                } else {
                    $daysSince = Carbon::parse($lastLog->completed_at)->diffInDays(Carbon::now());
                    if ($daysSince > $task->frequency_days) {
                        $isOverdue = true;
                    }
                }
            }

            // 2. Verificación Estacional (Mes)
            if ($task->season_month) {
                $currentMonth = Carbon::now()->month;
                // Si estamos en el mes de la temporada (o posterior) y no se hizo este año
                if ($currentMonth >= $task->season_month) {
                    $logThisYear = MaintenanceLog::where('equipment_id', $equipment->id)
                        ->where('maintenance_task_id', $task->id)
                        ->whereYear('completed_at', Carbon::now()->year)
                        ->exists();
                    
                    if (!$logThisYear) {
                        $isOverdue = true;
                    }
                }
            }

            if ($isOverdue) {
                $pendingTasks[] = [
                    'task' => $task->title,
                    'impact' => $task->efficiency_impact * 100 . '%',
                    'due_date' => $lastLog ? Carbon::parse($lastLog->completed_at)->addDays($task->frequency_days)->format('d/m/Y') : 'Inmediato'
                ];
                $totalPenalty += $task->efficiency_impact;
                $maxScore -= ($task->efficiency_impact * 100); // 10% impact = -10 points
            }
        }

        return [
            'health_score' => max(0, $maxScore),
            'pending_tasks' => $pendingTasks,
            'penalty_factor' => 1.0 + $totalPenalty
        ];
    }

    /**
     * Obtiene solo el factor de penalización para cálculos rápidos.
     * 
     * @param Equipment $equipment
     * @return float
     */
    public function getPenaltyFactor(Equipment $equipment): float
    {
        $status = $this->checkStatus($equipment);
        return $status['penalty_factor'];
    }
}
