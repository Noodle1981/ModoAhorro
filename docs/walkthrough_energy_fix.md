# Energy Calculation Fix Walkthrough

## Goal
Reduce the massive overestimation of energy consumption (previously ~755 kWh vs 123 kWh billed) by implementing realistic "Load Factors" (Duty Cycles) and ensuring the calculation logic correctly handles input power vs useful power.

## Changes Implemented

### 1. Code Logic Verification (Phase 2)
Verified `App\Services\ConsumptionAnalysisService.php`:
- Confirmed removal of division by efficiency (since meters measure Input Power).
- Confirmed usage of `load_factor` as a direct multiplier for "Real Usage".
- Confirmed integration with Climate API for "Effective Days".

### 2. Database Update (Load Factors)
Updated `database/seeders/FixLoadFactorsSeeder.php` to include correct equipment names found in the database:
- `PC de Escritorio (CPU + Monitor)`
- `Notebook / Laptop`
- `Modem / Router WiFi`

Ran the seeder to update `equipment_types` table:
```bash
php artisan db:seed --class=FixLoadFactorsSeeder
```

### 3. Survival Engine Calibration (Phase 3: Waterfall Logic)
Implemented `App\Services\ConsumptionCalibrator.php` with the **"Survival Engine"** logic. This algorithm fills "buckets" of consumption in strict priority order:

1.  **Priority 1: Base Load (Intocable)**
    *   **Equipments:** Fridge, Router, Alarm.
    *   **Action:** Filled first. If bill < Base Load, a critical cut is applied.

2.  **Priority 2: Ants (Protegido)**
    *   **Equipments:** Lights, Chargers, Small loads (<100W).
    *   **Action:** Filled second. If bill < (Base + Ants), Ants are cut partially.

3.  **Priority 3: Whales (Ajustable)**
    *   **Equipments:** AC, PC Gamer, Heating.
    *   **Action:** Filled last with whatever energy remains.
    *   **Distribution:** Weighted by category (Climate x3, Kitchen x1.5, Others x1).

### 4. Verification Results

#### Step 1: Theoretical Calculation (Physics Only)
Ran `php artisan verify:consumption 2`
- **Estimate:** 386.11 kWh
- **Improvement:** ~49% reduction from original ~755 kWh.

#### Step 2: Calibrated Calculation (Physics + Math)
Ran `php artisan verify:calibration 2`

**Final Results:**
- **Theoretical Sum:** 386.11 kWh
- **Calibrated Sum:** 123.00 kWh
- **Actual Billed:** 123.00 kWh
- **Status:** ✅ EXACT MATCH

The system now prioritizes keeping essential equipment "alive" in the calculation, sacrificing variable high-power loads first when the bill is low.
