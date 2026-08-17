# Documento de Arquitectura del Sistema — ModoAhorro

**Última Actualización:** 2026-08-17  
**Estado:** Activo / En Producción / Beta Cerrada  
**Roles:**
- **Director de Proyecto (Arquitecto):** IA notebookLM
- **Desarrollador Senior (Ejecutor):** IA Antigravity
- **QA & Usuario Final:** Omar

---

## 1. Visión General y Stack Tecnológico

ModoAhorro es una plataforma SaaS de diagnóstico y eficiencia energética basada en software (sin necesidad obligatoria de sensores o hardware físico), capaz de conciliar facturas de servicios públicos reales (luz y gas) contra inventarios de equipamiento, factores climáticos zonales, comportamiento de uso y balances de potencia.

### Stack Tecnológico:
- **Backend:** Laravel 11.x (PHP 8.2+) con arquitectura orientada a Servicios y Dominio (`app/Domain/`).
- **Frontend:** Vue.js 3 (Composition API con `<script setup>`), Inertia.js y Tailwind CSS v4.
- **Base de Datos:** MySQL / SQLite (en tests).
- **APIs Externas:** Open-Meteo / Climate Service (series térmicas y grado-días de refrigeración/calefacción).

---

## 2. Capa de Dominio Comercial y Modularidad de Rubros (`App\Domain\Commercial\`)

A partir de la versión de agosto 2026, el módulo comercial se estructura mediante el **Patrón Strategy + Registry**, permitiendo añadir rubros y sub-rubros sin tocar el motor central (*Open/Closed Principle*).

```
app/Domain/Commercial/
 ├── Contracts/
 │    └── CommercialProfileInterface.php        <-- Contrato estricto
 ├── Registry/
 │    └── CommercialProfileRegistry.php         <-- Auto-descubrimiento y catálogo
 └── Profiles/
      ├── AbstractModularCommercialProfile.php
      ├── Gastronomy/
      │    ├── GastronomyBaseProfile.php
      │    ├── IceCreamShopProfile.php          <-- Heladería Artesanal (Frío 24/7 + Mantecado)
      │    ├── PizzeriaProfile.php              <-- Pizzería & Rotisería (Hornos + Extracción)
      │    ├── CoffeeShopProfile.php            <-- Cafetería & Bar (Calderas continuas)
      │    └── RestaurantProfile.php            <-- Restaurante tradicional
      ├── Retail/
      │    └── RetailShopProfile.php            <-- Tiendas y locales comerciales
      └── Office/
           └── CorporateOfficeProfile.php       <-- Oficinas y servicios corporativos
```

### Principales Parámetros de los Perfiles Comerciales:
- **`StandbyMultiplier`:** Factor de carga continua ininterrumpible (ej: Heladerías = `1.35`).
- **`ThermalSensitivity`:** Multiplicador de estrés térmico estival sobre compresores (ej: Heladerías = `1.45`).
- **`CriticalCategories`:** Categorías que entran al balance de línea base 24/7 (Refrigeración comercial, cámaras, servidores).
- **`ProcessCategories`:** Categorías de elaboración y transformación (Mantecadoras, hornos, extracción).
- **`SuggestedAppliances`:** Catálogo pre-cargado para acelerar el onboarding de nuevos comercios.

---

## 3. Arquitectura del Motor de Energía por Tanques (Energy Engine)

El motor desglosa el consumo total de la factura en **4 Tanques Energéticos**:

```
                       ┌──────────────────────────────────────┐
                       │           FACTURA DE LUZ             │
                       └──────────────────┬───────────────────┘
                                          │
    ┌──────────────────┬──────────────────┼──────────────────┬──────────────────┐
    ▼                  ▼                  ▼                  ▼                  ▼
[ TANQUE 0 ]       [ TANQUE 1 ]       [ TANQUE 2 ]       [ TANQUE 3 ]       [ TANQUE 4 ]
Certeza / Standby   Línea Base         Climatización      Proceso /          Variable
(Vampiro 24h)       (Operativa)        (Térmico / Clima)  Elasticidad        (Remanente)
```

1. **Tanque 0 (Certeza / Standby):** Equipos 24h continuos con consumo fijo o pasivo.
2. **Tanque 1 (Base Operativa):** Equipos con horario fijo de apertura/cierre o turnos.
3. **Tanque 2 (Climatización):** Aires acondicionados y split condicionados por la temperatura exterior de la API climática.
4. **Tanque 3 (Proceso / Elasticidad):** Maquinaria productiva pesada (mantecadoras, hornos, extracción).
5. **Tanque 4 (Variable / Remanente):** Distribución proporcional del saldo remanente según potencia e intensidad.

---

## 4. Estructura de la Base de Datos (`tablas/`)

- `entities`: Entidades físicas (Hogares, Oficinas, Comercios) con campos `business_category` y `business_subcategory`.
- `rooms`: Ambientes físicos asociados a cada entidad.
- `equipment`: Artefactos físicos instalados con potencias, horas de uso y patrones.
- `equipment_types`: Catálogo maestro de tipos de equipos y tanques por defecto.
- `equipment_categories`: Agrupaciones lógicas de artefactos.
- `contracts`: Contratos de suministro eléctrico con distribuidoras.
- `invoices`: Facturas eléctricas vinculadas con consumo en kWh, cargos fijos y períodos.
- `equipment_usages`: Registro histórico conciliado por artefacto y tanque asignado.

---

## 5. Preparación Futura: Multi-Vector (Luz + Gas) e IoT

- **Multi-Vector:** Estructura preparada para el enum `EnergySource` (`ELECTRICITY` en kWh, `NATURAL_GAS` en m³, `SOLAR`).
- **IoT & Medidores Inteligentes:** La arquitectura permite que los tanques sustituyan o calibren su cálculo matemático con telemetría real proveniente de APIs de sensores externos.
