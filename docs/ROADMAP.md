# Roadmap de Proyecto
# Roadmap de Implementación: Centro de Reemplazos y Precios Dinámicos

Este documento detalla el plan de ejecución paso a paso para construir el módulo de "Reemplazos Inteligentes" con cálculo de ROI y actualización de precios automática vía Mercado Libre.

---

## 📅 Fase 1: Cimientos de Datos (Database)
**Objetivo:** Preparar la base de datos para almacenar benchmarks de eficiencia y precios.

- [ ] **1.1. Crear Migración `efficiency_benchmarks`**
    - Tabla para guardar el "estándar de oro" de cada categoría (ej: Aire Inverter).
    - Campos clave: `efficiency_gain_factor`, `average_market_price`, `meli_search_term`, `affiliate_link`.
- [ ] **1.2. Crear Modelo `EfficiencyBenchmark`**
    - Definir relaciones (`belongsTo EquipmentType`).
    - Configurar `$fillable` y casts.
- [ ] **1.3. Crear Seeder Inicial (`EfficiencyBenchmarkSeeder`)**
    - Poblar con los 6 casos de uso principales:
        1. Aire Acondicionado (Inverter A++)
        2. Heladera (Inverter No-Frost)
        3. Lavarropas (Inverter)
        4. Iluminación (LED)
        5. Termotanque Eléctrico (Solar)
        6. Calefón a Gas (Solar)

---

## 🧠 Fase 2: Motor de Inteligencia (Logic)
**Objetivo:** Implementar la lógica matemática que calcula el ahorro y el tiempo de repago.

- [ ] **2.1. Crear `ReplacementService`**
    - Método `generateOpportunities($invoice)`:
        - Analizar consumos calibrados (`kwh_reconciled`).
        - Comparar contra el benchmark.
        - Calcular `monthly_savings` y `payback_months`.
- [ ] **2.2. Implementar Lógica de Veredicto**
    - Función para etiquetar la inversión:
        - "💎 Retorno Inmediato" (< 12 meses)
        - "🔥 Gran Oportunidad" (1-3 años)
        - "📈 Ahorro a Largo Plazo" (hasta 10 años)
- [ ] **2.3. Tests Unitarios**
    - Verificar que no sugiera cambios si el equipo ya es eficiente.
    - Verificar que el cálculo de ROI sea matemáticamente correcto.

---

## 🛒 Fase 3: Precios Dinámicos (Mercado Libre Integration)
**Objetivo:** Mantener los precios actualizados automáticamente para que el ROI sea real.

- [ ] **3.1. Crear `MarketPriceService`**
    - Método `fetchPricesFromMeli($searchTerm)`:
        - Conectar a API pública `https://api.mercadolibre.com/sites/MLA/search`.
        - Filtrar resultados (eliminar outliers muy baratos/caros).
        - Calcular promedio robusto.
- [ ] **3.2. Crear Comando de Consola**
    - `php artisan prices:update`: Ejecuta la actualización para todos los benchmarks.
- [ ] **3.3. Configurar Scheduler**
    - Programar el comando para correr semanalmente (`weekly()`) en `Kernel.php`.

---

## 🖥️ Fase 4: Interfaz de Usuario (UI/UX)
**Objetivo:** Mostrar estas oportunidades de forma irresistible al usuario.

- [ ] **4.1. Tarjeta en Dashboard Principal**
    - Actualizar la tarjeta "Reemplazos" en `index.blade.php` para mostrar un resumen (ej: "3 Oportunidades detectadas").
- [ ] **4.2. Vista Detallada (`replacements.index`)**
    - Listado de equipos a reemplazar.
    - Gráfico de barras: "Gasto Actual" vs "Gasto Futuro".
    - Badge de "Tiempo de Repago" con colores (Verde/Amarillo).
- [ ] **4.3. Integración de Links de Afiliados**
    - Botón "Ver Precios en Mercado Libre".
    - Redirección usando el `affiliate_link` generado dinámicamente.

---

## 💰 Fase 5: Monetización y Ajustes Finales
**Objetivo:** Configurar el sistema para generar ingresos.

- [ ] **5.1. Configuración de Afiliados**
    - Agregar campo `MELI_AFFILIATE_ID` en `.env`.
    - Generar links de búsqueda con el ID de afiliado incrustado.
- [ ] **5.2. Documentación Final**
    - Actualizar `MANUAL_INTEGRAL.md` con el nuevo módulo.

---

*Este roadmap está diseñado para ser ejecutado secuencialmente. La Fase 1 y 2 son el "Core" funcional, la Fase 3 agrega automatización y la Fase 5 cierra el modelo de negocio.*
