<?php

namespace App\Services\Analytics;

use App\Models\User;
use App\Models\Entity;
use App\Models\Room;
use App\Models\Equipment;
use App\Models\Invoice;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;

class AdminAnalyticsService
{
    /**
     * Get comprehensive analytics for super admin dashboard
     */
    public function getAnalytics(): array
    {
        return [
            'users' => $this->getUserMetrics(),
            'entities' => $this->getEntityMetrics(),
            'rooms' => $this->getRoomMetrics(),
            'equipment' => $this->getEquipmentMetrics(),
            'business' => $this->getBusinessMetrics(),
            'growth' => $this->getGrowthMetrics(),
            'recent_activity' => $this->getRecentActivity(),
        ];
    }

    /**
     * Get user-related metrics
     */
    protected function getUserMetrics(): array
    {
        $totalUsers = User::count();
        $activeUsers = User::where('updated_at', '>=', now()->subDays(30))->count();

        // Users by plan
        $usersByPlan = DB::table('entity_user')
            ->join('plans', 'entity_user.plan_id', '=', 'plans.id')
            ->select('plans.name', DB::raw('COUNT(DISTINCT entity_user.user_id) as count'))
            ->groupBy('plans.name')
            ->get()
            ->pluck('count', 'name')
            ->toArray();

        return [
            'total' => $totalUsers,
            'active' => $activeUsers,
            'by_plan' => $usersByPlan,
        ];
    }

    /**
     * Get entity-related metrics
     */
    protected function getEntityMetrics(): array
    {
        $totalEntities = Entity::count();
        
        $entitiesByType = Entity::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type')
            ->toArray();

        $avgEntitiesPerUser = $totalEntities > 0 
            ? round(DB::table('entity_user')->count() / User::count(), 2)
            : 0;

        return [
            'total' => $totalEntities,
            'by_type' => $entitiesByType,
            'avg_per_user' => $avgEntitiesPerUser,
        ];
    }

    /**
     * Get room-related metrics
     */
    protected function getRoomMetrics(): array
    {
        $totalRooms = Room::count();
        $totalEntities = Entity::count();
        
        $avgRoomsPerEntity = $totalEntities > 0 
            ? round($totalRooms / $totalEntities, 2)
            : 0;

        $topRoomTypes = Room::select('name', DB::raw('count(*) as count'))
            ->groupBy('name')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->pluck('count', 'name')
            ->toArray();

        return [
            'total' => $totalRooms,
            'avg_per_entity' => $avgRoomsPerEntity,
            'top_types' => $topRoomTypes,
        ];
    }

    /**
     * Get equipment-related metrics
     */
    protected function getEquipmentMetrics(): array
    {
        $totalEquipment = Equipment::count();
        $totalEntities = Entity::count();
        
        $avgEquipmentPerEntity = $totalEntities > 0 
            ? round($totalEquipment / $totalEntities, 2)
            : 0;

        $equipmentByCategory = Equipment::join('equipment_types', 'equipment.type_id', '=', 'equipment_types.id')
            ->join('equipment_categories', 'equipment_types.category_id', '=', 'equipment_categories.id')
            ->select('equipment_categories.name', DB::raw('count(*) as count'))
            ->groupBy('equipment_categories.name')
            ->orderByDesc('count')
            ->get()
            ->pluck('count', 'name')
            ->toArray();

        return [
            'total' => $totalEquipment,
            'avg_per_entity' => $avgEquipmentPerEntity,
            'by_category' => $equipmentByCategory,
        ];
    }

    /**
     * Get business-related metrics
     */
    protected function getBusinessMetrics(): array
    {
        $totalConsumption = Invoice::sum('consumo_kwh') ?? 0;
        $totalInvoices = Invoice::count();
        
        // Simplified savings calculation - based on invoices with data
        // This is a placeholder - you can enhance it later with actual recommendation logic
        $totalSavings = $totalConsumption * 0.15; // Assuming 15% potential savings

        // Count recommendations generated (based on equipment with installation dates)
        $recommendationsGenerated = Equipment::whereNotNull('installed_at')->count();

        return [
            'total_consumption_kwh' => round($totalConsumption, 2),
            'total_savings_kwh' => round($totalSavings, 2),
            'invoices_processed' => $totalInvoices,
            'recommendations_generated' => $recommendationsGenerated,
        ];
    }

    /**
     * Get growth metrics (last 12 months)
     */
    protected function getGrowthMetrics(): array
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('Y-m');
        }

        $usersGrowth = [];
        $entitiesGrowth = [];

        foreach ($months as $month) {
            $monthStart = $month . '-01';
            $monthEnd = date('Y-m-t', strtotime($monthStart));

            $usersGrowth[$month] = User::whereBetween('created_at', [$monthStart, $monthEnd])->count();
            $entitiesGrowth[$month] = Entity::whereBetween('created_at', [$monthStart, $monthEnd])->count();
        }

        return [
            'labels' => array_map(function($m) {
                return date('M Y', strtotime($m . '-01'));
            }, $months),
            'users' => array_values($usersGrowth),
            'entities' => array_values($entitiesGrowth),
        ];
    }

    /**
     * Get recent activity
     */
    protected function getRecentActivity(): array
    {
        $recentUsers = User::where('is_super_admin', false)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'name' => $user->name,
                    'email' => $user->email,
                    'plan' => $user->currentPlan()?->name ?? 'Sin Plan',
                    'created_at' => $user->created_at->format('d/m/Y H:i'),
                ];
            })
            ->toArray();

        $recentEntities = Entity::with('users')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function ($entity) {
                return [
                    'name' => $entity->name,
                    'type' => $entity->type,
                    'owner' => $entity->users->first()?->name ?? 'Sin dueño',
                    'created_at' => $entity->created_at->format('d/m/Y H:i'),
                ];
            })
            ->toArray();

        return [
            'users' => $recentUsers,
            'entities' => $recentEntities,
        ];
    }
}
