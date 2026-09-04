<?php

namespace App\Http\Controllers;

use App\Models\EnergyLabelCoefficient;
use App\Models\Equipment;
use App\Models\EquipmentBenchmark;
use App\Models\EquipmentCategory;
use App\Models\EquipmentModel;
use App\Models\EquipmentType;
use App\Models\Entity;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AdminController extends Controller
{
    private function checkAdmin()
    {
        if (! Auth::user() || ! Auth::user()->is_super_admin) {
            abort(403, 'No tienes permisos de administrador.');
        }
    }

    public function index()
    {
        $this->checkAdmin();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_types' => EquipmentType::count(),
                'total_models' => EquipmentModel::count(),
                'total_benchmarks' => EquipmentBenchmark::count(),
                'total_coefficients' => EnergyLabelCoefficient::count(),
                'total_users' => User::count(),
            ],
        ]);
    }

    public function equipmentTypes()
    {
        $this->checkAdmin();

        return Inertia::render('Admin/EquipmentTypes', [
            'equipmentTypes' => EquipmentType::with('category')->withCount('equipment')->orderBy('name')->get(),
            'categories' => EquipmentCategory::orderBy('name')->get(),
        ]);
    }

    public function storeEquipmentType(Request $request)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'category_id' => 'required|exists:equipment_categories,id',
            'name' => 'required|string|max:255|unique:equipment_types,name',
            'is_active' => 'boolean',
            'default_power_watts' => 'required|integer|min:0|max:100000',
            'min_watts' => 'nullable|integer|min:0|max:100000',
            'max_watts' => 'nullable|integer|min:0|max:100000',
            'default_avg_daily_use_hours' => 'nullable|numeric|min:0|max:24',
            'default_standby_power_w' => 'nullable|integer|min:0|max:1000',
            'default_tank' => 'required|integer|in:0,1,2,3',
            'load_factor' => 'nullable|numeric|min:0|max:1',
            'thermal_efficiency_penalty' => 'nullable|numeric|min:0|max:100',
            'determinism_score' => 'nullable|numeric|min:0|max:1',
            'social_coefficient' => 'nullable|numeric|min:0|max:10',
            'consumption_logic' => 'nullable|string|max:50',
            'usage_unit' => 'nullable|string|max:50',
            'is_climatization' => 'boolean',
            'is_inverter_capable' => 'boolean',
            'is_shiftable' => 'boolean',
        ]);

        if (isset($validated['min_watts']) && isset($validated['max_watts']) && $validated['min_watts'] > $validated['max_watts']) {
            return back()->withErrors(['min_watts' => 'La potencia mínima no puede ser mayor a la potencia máxima.']);
        }

        EquipmentType::create($validated);

        return redirect()->back()->with('success', 'Tipo de equipo creado con éxito en el Catálogo Maestro.');
    }

    public function updateEquipmentType(Request $request, EquipmentType $equipmentType)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'category_id' => 'required|exists:equipment_categories,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('equipment_types', 'name')->ignore($equipmentType->id),
            ],
            'is_active' => 'boolean',
            'default_power_watts' => 'required|integer|min:0|max:100000',
            'min_watts' => 'nullable|integer|min:0|max:100000',
            'max_watts' => 'nullable|integer|min:0|max:100000',
            'default_avg_daily_use_hours' => 'nullable|numeric|min:0|max:24',
            'default_standby_power_w' => 'nullable|integer|min:0|max:1000',
            'default_tank' => 'required|integer|in:0,1,2,3',
            'load_factor' => 'nullable|numeric|min:0|max:1',
            'thermal_efficiency_penalty' => 'nullable|numeric|min:0|max:100',
            'determinism_score' => 'nullable|numeric|min:0|max:1',
            'social_coefficient' => 'nullable|numeric|min:0|max:10',
            'consumption_logic' => 'nullable|string|max:50',
            'usage_unit' => 'nullable|string|max:50',
            'is_climatization' => 'boolean',
            'is_inverter_capable' => 'boolean',
            'is_shiftable' => 'boolean',
        ]);

        if (isset($validated['min_watts']) && isset($validated['max_watts']) && $validated['min_watts'] > $validated['max_watts']) {
            return back()->withErrors(['min_watts' => 'La potencia mínima no puede ser mayor a la potencia máxima.']);
        }

        $equipmentType->update($validated);

        return redirect()->back()->with('success', 'Tipo de equipo actualizado correctamente.');
    }

    public function toggleActiveEquipmentType(EquipmentType $equipmentType)
    {
        $this->checkAdmin();

        $equipmentType->update([
            'is_active' => ! $equipmentType->is_active,
        ]);

        $status = $equipmentType->is_active ? 'activado' : 'archivado/desactivado';

        return redirect()->back()->with('success', "El tipo de equipo ha sido {$status} exitosamente.");
    }

    public function destroyEquipmentType(EquipmentType $equipmentType)
    {
        $this->checkAdmin();

        $inUseCount = $equipmentType->equipment()->count();

        if ($inUseCount > 0) {
            return redirect()->back()->withErrors([
                'error' => "No es posible eliminar '{$equipmentType->name}' porque está en uso por {$inUseCount} equipo(s) de usuarios. Puedes archivarlo o desactivarlo para que no aparezca a nuevos usuarios.",
            ]);
        }

        $equipmentType->delete();

        return redirect()->back()->with('success', "'{$equipmentType->name}' ha sido eliminado definitivamente del Catálogo Maestro.");
    }

    public function efficiencyLabels()
    {
        $this->checkAdmin();

        return Inertia::render('Admin/EfficiencyLabels', [
            'coefficients' => EnergyLabelCoefficient::with(['category', 'equipmentType'])->orderBy('category_id')->get(),
            'categories' => EquipmentCategory::orderBy('name')->get(),
            'equipmentTypes' => EquipmentType::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function storeEfficiencyLabel(Request $request)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'category_id' => 'required|exists:equipment_categories,id',
            'equipment_type_id' => 'nullable|exists:equipment_types,id',
            'label' => 'required|string|in:A+++,A++,A+,A,B,C,D,E,F,G',
            'coefficient' => 'required|numeric|min:0.1|max:10.0',
        ]);

        EnergyLabelCoefficient::updateOrCreate(
            [
                'category_id' => $validated['category_id'],
                'equipment_type_id' => $validated['equipment_type_id'] ?? null,
                'label' => $validated['label'],
            ],
            [
                'coefficient' => $validated['coefficient'],
            ]
        );

        return redirect()->back()->with('success', "Coeficiente para Etiqueta '{$validated['label']}' guardado correctamente.");
    }

    public function updateEfficiencyLabel(Request $request, EnergyLabelCoefficient $coefficient)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'coefficient' => 'required|numeric|min:0.1|max:10.0',
        ]);

        $coefficient->update($validated);

        return redirect()->back()->with('success', "Coeficiente de Clase '{$coefficient->label}' actualizado a {$coefficient->coefficient}x.");
    }

    public function destroyEfficiencyLabel(EnergyLabelCoefficient $coefficient)
    {
        $this->checkAdmin();

        $label = $coefficient->label;
        $coefficient->delete();

        return redirect()->back()->with('success', "Coeficiente de Clase '{$label}' eliminado de la matriz.");
    }

    public function resetEfficiencyLabels()
    {
        $this->checkAdmin();

        $seeder = new \Database\Seeders\EnergyLabelSeeder();
        $seeder->run();

        return redirect()->back()->with('success', 'Matriz de coeficientes restablecida a los valores oficiales estándar.');
    }

    public function benchmarks()
    {
        $this->checkAdmin();

        return Inertia::render('Admin/Benchmarks', [
            'benchmarks' => EquipmentBenchmark::with(['category', 'equipmentType'])->orderBy('category_id')->get(),
            'categories' => EquipmentCategory::orderBy('name')->get(),
            'equipmentTypes' => EquipmentType::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function storeBenchmark(Request $request)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'category_id' => 'required|exists:equipment_categories,id',
            'equipment_type_id' => 'nullable|exists:equipment_types,id',
            'name' => 'required|string|max:255',
            'energy_label' => 'required|string|max:10',
            'watts' => 'required|integer|min:0|max:100000',
            'efficiency_gain_factor' => 'required|numeric|min:0.01|max:0.99',
            'average_market_price' => 'nullable|numeric|min:0',
            'meli_search_term' => 'nullable|string|max:255',
            'affiliate_link' => 'nullable|string|max:500',
            'recommendation_text' => 'nullable|string|max:1000',
        ]);

        $validated['average_market_price'] = $validated['average_market_price'] ?? 0;
        $validated['efficiency_ratio'] = 1 - $validated['efficiency_gain_factor'];

        EquipmentBenchmark::create($validated);

        return redirect()->back()->with('success', "Benchmark '{$validated['name']}' registrado exitosamente.");
    }

    public function updateBenchmark(Request $request, EquipmentBenchmark $benchmark)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'category_id' => 'required|exists:equipment_categories,id',
            'equipment_type_id' => 'nullable|exists:equipment_types,id',
            'name' => 'required|string|max:255',
            'energy_label' => 'required|string|max:10',
            'watts' => 'required|integer|min:0|max:100000',
            'efficiency_gain_factor' => 'required|numeric|min:0.01|max:0.99',
            'average_market_price' => 'nullable|numeric|min:0',
            'meli_search_term' => 'nullable|string|max:255',
            'affiliate_link' => 'nullable|string|max:500',
            'recommendation_text' => 'nullable|string|max:1000',
        ]);

        $validated['average_market_price'] = $validated['average_market_price'] ?? 0;
        $validated['efficiency_ratio'] = 1 - $validated['efficiency_gain_factor'];

        $benchmark->update($validated);

        return redirect()->back()->with('success', "Benchmark '{$benchmark->name}' actualizado correctamente.");
    }

    public function destroyBenchmark(EquipmentBenchmark $benchmark)
    {
        $this->checkAdmin();

        $name = $benchmark->name;
        $benchmark->delete();

        return redirect()->back()->with('success', "Benchmark '{$name}' eliminado del catálogo de referencias.");
    }

    // ==========================================
    // MODELOS COMERCIALES & INTELIGENCIA COLECTIVA
    // ==========================================

    public function equipmentModels()
    {
        $this->checkAdmin();

        $verifiedModels = EquipmentModel::with(['category', 'type'])
            ->withCount('equipment')
            ->orderBy('brand')
            ->orderBy('model')
            ->get();

        // 1. Obtener pares (brand, model) ya verificados
        $existingVerified = EquipmentModel::select('brand', 'model')->get()->map(function ($m) {
            return strtolower(trim($m->brand)) . '|||' . strtolower(trim($m->model));
        })->toArray();

        // 2. Agrupar equipos cargados por usuarios que no tengan model_id
        $rawSuggestions = Equipment::query()
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->whereNotNull('model')
            ->where('model', '!=', '')
            ->selectRaw('
                TRIM(brand) as brand,
                TRIM(model) as model,
                category_id,
                type_id,
                COUNT(*) as user_count,
                ROUND(AVG(nominal_power_w)) as avg_watts,
                MIN(nominal_power_w) as min_watts,
                MAX(nominal_power_w) as max_watts,
                SUM(CASE WHEN is_inverter = 1 THEN 1 ELSE 0 END) as inverter_count,
                MAX(capacity) as sample_capacity,
                MAX(capacity_unit) as sample_capacity_unit,
                MAX(energy_label) as sample_energy_label
            ')
            ->groupBy('brand', 'model', 'category_id', 'type_id')
            ->havingRaw('COUNT(*) >= 1')
            ->orderByDesc('user_count')
            ->get();

        $communitySuggestions = $rawSuggestions->filter(function ($s) use ($existingVerified) {
            $key = strtolower(trim($s->brand)) . '|||' . strtolower(trim($s->model));

            return ! in_array($key, $existingVerified);
        })->values();

        // Enlazar categorías y tipos a las sugerencias de la comunidad
        $categoriesById = EquipmentCategory::all()->keyBy('id');
        $typesById = EquipmentType::all()->keyBy('id');

        $communitySuggestions->transform(function ($s) use ($categoriesById, $typesById) {
            $s->category = $categoriesById->get($s->category_id);
            $s->type = $typesById->get($s->type_id);
            $s->suggested_inverter = ($s->user_count > 0 && ($s->inverter_count / $s->user_count) >= 0.5);

            return $s;
        });

        return Inertia::render('Admin/EquipmentModels', [
            'verifiedModels' => $verifiedModels,
            'communitySuggestions' => $communitySuggestions,
            'categories' => EquipmentCategory::orderBy('name')->get(),
            'equipmentTypes' => EquipmentType::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function storeEquipmentModel(Request $request)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'category_id' => 'required|exists:equipment_categories,id',
            'type_id' => 'required|exists:equipment_types,id',
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'nominal_power_w' => 'required|integer|min:0|max:100000',
            'is_inverter' => 'boolean',
            'energy_label' => 'nullable|string|max:10',
            'capacity' => 'nullable|numeric|min:0',
            'capacity_unit' => 'nullable|string|max:50',
            'is_verified' => 'boolean',
            'notes' => 'nullable|string|max:1000',
            'source' => 'nullable|string|max:50',
        ]);

        $validated['brand'] = trim($validated['brand']);
        $validated['model'] = trim($validated['model']);
        $validated['source'] = $validated['source'] ?? 'ADMIN_MANUAL';

        $model = EquipmentModel::updateOrCreate(
            ['brand' => $validated['brand'], 'model' => $validated['model']],
            $validated
        );

        // Vincular retroactivamente los equipos existentes en la red
        $linkedCount = Equipment::whereRaw('LOWER(TRIM(brand)) = ?', [strtolower($validated['brand'])])
            ->whereRaw('LOWER(TRIM(model)) = ?', [strtolower($validated['model'])])
            ->update(['model_id' => $model->id]);

        $model->update(['occurrences_count' => max(1, $linkedCount)]);

        return redirect()->back()->with('success', "Modelo '{$model->brand} {$model->model}' oficializado con éxito y vinculado a {$linkedCount} equipo(s) en la red.");
    }

    public function updateEquipmentModel(Request $request, EquipmentModel $equipmentModel)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'category_id' => 'required|exists:equipment_categories,id',
            'type_id' => 'required|exists:equipment_types,id',
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'nominal_power_w' => 'required|integer|min:0|max:100000',
            'is_inverter' => 'boolean',
            'energy_label' => 'nullable|string|max:10',
            'capacity' => 'nullable|numeric|min:0',
            'capacity_unit' => 'nullable|string|max:50',
            'is_verified' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $equipmentModel->update($validated);

        return redirect()->back()->with('success', "Modelo '{$equipmentModel->brand} {$equipmentModel->model}' actualizado correctamente.");
    }

    public function destroyEquipmentModel(EquipmentModel $equipmentModel)
    {
        $this->checkAdmin();

        // Desvincular de los equipos sin borrarlos
        Equipment::where('model_id', $equipmentModel->id)->update(['model_id' => null]);

        $name = "{$equipmentModel->brand} {$equipmentModel->model}";
        $equipmentModel->delete();

        return redirect()->back()->with('success', "El modelo '{$name}' ha sido eliminado del catálogo oficial.");
    }

    public function autocompleteModels(Request $request)
    {
        $q = trim($request->input('q', ''));
        $categoryId = $request->input('category_id');
        $typeId = $request->input('type_id');

        $query = EquipmentModel::with(['category', 'type'])
            ->where('is_verified', true);

        if ($q) {
            $query->where(function ($sq) use ($q) {
                $sq->where('brand', 'LIKE', "%{$q}%")
                    ->orWhere('model', 'LIKE', "%{$q}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($typeId) {
            $query->where('type_id', $typeId);
        }

        return response()->json(
            $query->orderBy('occurrences_count', 'desc')
                ->limit(25)
                ->get()
        );
    }

    // ==========================================
    // GESTIÓN DE USUARIOS
    // ==========================================

    public function users()
    {
        $this->checkAdmin();

        return Inertia::render('Admin/Users', [
            'users' => User::withCount('entities')
                ->orderBy('is_super_admin', 'desc')
                ->orderBy('name', 'asc')
                ->get(),
        ]);
    }

    public function storeUser(Request $request)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'is_super_admin' => 'boolean',
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->is_super_admin = $validated['is_super_admin'] ?? false;
        $user->save();

        return redirect()->back()->with('success', "Usuario '{$validated['name']}' creado exitosamente.");
    }

    public function updateUser(Request $request, User $user)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'is_super_admin' => 'boolean',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        if (isset($validated['is_super_admin'])) {
            $user->is_super_admin = (bool) $validated['is_super_admin'];
        }

        $user->save();

        return redirect()->back()->with('success', "Usuario '{$user->name}' actualizado correctamente.");
    }

    public function toggleAdminUser(User $user)
    {
        $this->checkAdmin();

        // Evitar que el usuario actual se quite a sí mismo el super admin
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'No puedes revocar tus propios privilegios de Super Administrador.');
        }

        $user->is_super_admin = ! $user->is_super_admin;
        $user->save();

        $status = $user->is_super_admin ? 'Super Administrador' : 'Usuario Estándar';

        return redirect()->back()->with('success', "Rol de '{$user->name}' actualizado a {$status}.");
    }

    public function destroyUser(User $user)
    {
        $this->checkAdmin();

        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'No puedes eliminar tu propia cuenta de usuario.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->back()->with('success', "Usuario '{$name}' eliminado del sistema.");
    }

    // ==========================================
    // RESETEOS DE CLAVE
    // ==========================================

    public function userResets()
    {
        $this->checkAdmin();

        $activeTokens = DB::table('password_reset_tokens')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($t) {
                $user = User::where('email', $t->email)->first();

                return [
                    'email' => $t->email,
                    'token' => $t->token,
                    'user_name' => $user?->name ?? 'Usuario no registrado',
                    'user_id' => $user?->id,
                    'created_at' => $t->created_at,
                    'reset_url' => url('/reset-password/'.$t->token.'?email='.urlencode($t->email)),
                ];
            });

        return Inertia::render('Admin/UserResets', [
            'activeTokens' => $activeTokens,
            'users' => User::select('id', 'name', 'email')->orderBy('name')->get(),
        ]);
    }

    public function generateResetLink(Request $request)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $validated['email']],
            ['token' => $token, 'created_at' => now()]
        );

        $resetUrl = url('/reset-password/'.$token.'?email='.urlencode($validated['email']));

        return redirect()->back()->with([
            'success' => "Enlace de reseteo generado para {$validated['email']}.",
            'generated_reset_url' => $resetUrl,
        ]);
    }

    public function forceResetPassword(Request $request)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'new_password' => 'required|string|min:6',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $user->password = Hash::make($validated['new_password']);
        $user->save();

        // Limpiar tokens pendientes
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        return redirect()->back()->with('success', "Contraseña actualizada exitosamente para {$user->name}.");
    }

    public function destroyResetToken($email)
    {
        $this->checkAdmin();

        DB::table('password_reset_tokens')->where('email', urldecode($email))->delete();

        return redirect()->back()->with('success', 'Token de reseteo revocado.');
    }

    // ==========================================
    // PAGOS & SUSCRIPCIONES
    // ==========================================

    public function userPayments()
    {
        $this->checkAdmin();

        $plans = Plan::all()->keyBy('id');
        $defaultPlan = Plan::where('name', 'Gratuito')->first() ?? $plans->first();

        $users = User::with('entities')->orderBy('name')->get()->map(function ($user) use ($plans, $defaultPlan) {
            $latestPivot = DB::table('entity_user')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->first();

            $planId = $latestPivot && $latestPivot->plan_id ? $latestPivot->plan_id : ($defaultPlan?->id ?? 1);
            $plan = $plans->get($planId) ?? $defaultPlan;

            $entitiesList = $user->entities->map(function ($e) {
                return [
                    'id' => $e->id,
                    'name' => $e->name,
                    'type' => $e->type,
                ];
            });

            return [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'is_super_admin' => $user->is_super_admin,
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'plan_price' => (float) $plan->price,
                'max_entities' => $plan->max_entities,
                'allowed_entity_types' => $plan->allowed_entity_types ?? ['hogar'],
                'entities_count' => $user->entities->count(),
                'entities' => $entitiesList,
                'subscribed_at' => $latestPivot?->subscribed_at,
                'expires_at' => $latestPivot?->expires_at,
                'created_at' => $user->created_at,
            ];
        });

        // Contabilizar por Plan
        $totalUsers = $users->count();
        $freeUsers = $users->filter(fn ($u) => strtolower($u['plan_name']) === 'gratuito')->count();
        $premiumUsers = $users->filter(fn ($u) => strtolower($u['plan_name']) === 'premium')->count();
        $enterpriseUsers = $users->filter(fn ($u) => strtolower($u['plan_name']) === 'enterprise')->count();
        $monthlyRevenue = $users->sum('plan_price');

        return Inertia::render('Admin/UserPayments', [
            'usersList' => $users,
            'plans' => $plans->values(),
            'stats' => [
                'total_users' => $totalUsers,
                'free_users' => $freeUsers,
                'premium_users' => $premiumUsers,
                'enterprise_users' => $enterpriseUsers,
                'monthly_revenue' => $monthlyRevenue,
            ],
        ]);
    }

    public function assignPlan(Request $request)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'plan_id' => 'required|exists:plans,id',
        ]);

        $user = User::findOrFail($validated['user_id']);

        // Si el usuario ya tiene registros en entity_user, actualizar plan_id en todos sus registros
        $existingPivots = DB::table('entity_user')->where('user_id', $user->id)->count();

        if ($existingPivots > 0) {
            DB::table('entity_user')
                ->where('user_id', $user->id)
                ->update([
                    'plan_id' => $validated['plan_id'],
                    'updated_at' => now(),
                ]);
        } else {
            $firstEntity = Entity::where('user_id', $user->id)->first();
            if ($firstEntity) {
                DB::table('entity_user')->insert([
                    'user_id' => $user->id,
                    'entity_id' => $firstEntity->id,
                    'plan_id' => $validated['plan_id'],
                    'subscribed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $plan = Plan::find($validated['plan_id']);

        return redirect()->back()->with('success', "Plan '{$plan->name}' asignado correctamente al usuario '{$user->name}'.");
    }

    public function extendSubscription(Request $request)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'days' => 'required|integer|min:1|max:3650',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $sub = DB::table('entity_user')->where('user_id', $user->id)->orderBy('created_at', 'desc')->first();

        $baseDate = $sub && $sub->expires_at && strtotime($sub->expires_at) > time()
            ? strtotime($sub->expires_at)
            : time();

        $newExpiresAt = date('Y-m-d H:i:s', strtotime("+{$validated['days']} days", $baseDate));

        DB::table('entity_user')
            ->where('user_id', $user->id)
            ->update([
                'expires_at' => $newExpiresAt,
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', "Membresía del usuario '{$user->name}' extendida por {$validated['days']} días hasta el {$newExpiresAt}.");
    }

    public function updatePlan(Request $request, Plan $plan)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'max_entities' => 'required|integer|min:1',
            'allowed_entity_types' => 'required|array',
            'features' => 'nullable|string|max:255',
        ]);

        $plan->update($validated);

        return redirect()->back()->with('success', "Plan '{$plan->name}' actualizado correctamente.");
    }

    // ==========================================
    // APIS & INTEGRACIONES
    // ==========================================

    public function apis()
    {
        $this->checkAdmin();

        return Inertia::render('Admin/Apis', [
            'integrations' => [
                'mercadolibre' => [
                    'name' => 'Mercado Libre Marketplace & Afiliados',
                    'status' => 'Conectado (Modo Lectura)',
                    'site_id' => 'MLA',
                    'enabled' => true,
                ],
                'cammesa' => [
                    'name' => 'CAMMESA / ENRE Tarifas Eléctricas Mayoristas',
                    'status' => 'Activo (Sincronización Semanal)',
                    'enabled' => true,
                ],
                'openmeteo' => [
                    'name' => 'Open-Meteo Clima & Grados Día (HDD/CDD)',
                    'status' => 'Activo (Tiempo Real)',
                    'enabled' => true,
                ],
            ],
        ]);
    }

    public function updateApis(Request $request)
    {
        $this->checkAdmin();

        return redirect()->back()->with('success', 'Configuración de conectores y APIs actualizada correctamente.');
    }
}
