# Módulo de Confort Térmico

## 1. Descripción General
Módulo que permite al usuario realizar una autoevaluación rápida de la calidad térmica de su vivienda para identificar pérdidas de energía pasiva y recibir recomendaciones de aislamiento.

**Integración con Motor v3:** El perfil térmico calculado se utiliza directamente en el `EnergyEngineService` (Tanque 2) para ajustar el consumo estimado de climatización según la eficiencia real del hogar.

---

## 2. Implementación Técnica

### Base de Datos
- **Tabla:** `entities`
- **Columna:** `thermal_profile` (JSON, Nullable)

**Estructura JSON:**
```json
{
  "roof_type": "sheet_metal|concrete_slab|wood_tiles",
  "roof_insulation": true|false,
  "window_type": "single_glass|dvh",
  "window_frame": "aluminum|wood|pvc",
  "drafts_detected": true|false,
  "orientation": "norte_sur|este_oeste|diagonal",
  "south_window": true|false,
  "sun_exposure": "high|medium|low",
  "thermal_score": 45, // Calculado (0-100)
  "energy_label": "E"  // Calculado (A-G)
}
```

### Arquitectura de Servicios

1.  **`ThermalProfileService`** (`App\Services\ThermalProfileService`):
    -   **Método principal:** `calculate(array $profile): array`
    -   Calcula un puntaje base de 50.
    -   **Techumbre:** Penaliza chapa (-15) y losa (0), premia aislación (+10).
    -   **Aberturas:** Premia DVH (+15), penaliza chifletes (-15).
    -   **Bioclimática:** Ajusta según orientación y exposición solar.
    -   Determina la etiqueta (A >= 85, B >= 70, C >= 50, D >= 30, E < 30).
    -   **Retorna:** `['thermal_score' => int, 'energy_label' => string]`
    -   **Método clave:** `getMultiplierForScore(string $label): float`
        - A = 1.0 (sin penalización)
        - B = 1.2
        - C = 1.4
        - D = 1.6
        - E = 1.8 (penalización severa)
    -   **Uso en Motor v3:** El multiplicador se aplica al consumo de climatización en el Tanque 2.

2.  **`ThermalAdviceEngine`** (`App\Services\Thermal\ThermalAdviceEngine`):
    -   Genera recomendaciones basadas en condiciones específicas.
    -   *Ejemplo:* Si `drafts_detected` es true -> Recomienda "Instalar Burletes Autoadhesivos".
    -   *Ejemplo:* Si `window_type` es 'single_glass' y score < 60 -> Recomienda "Plan Canje de Ventanas a DVH".

### Rutas y Controladores
-   **Controller:** `ThermalComfortController`
-   **Rutas:**
    -   `GET /entities/{entity}/thermal`: Redirige a Wizard o Resultado.
    -   `GET /entities/{entity}/thermal/wizard`: Formulario de diagnóstico visual.
    -   `POST /entities/{entity}/thermal`: Procesa el formulario y guarda el JSON.
    -   `GET /entities/{entity}/thermal/result`: Muestra Score, Semáforo y Tarjetas de Recomendación.

### Vistas
-   `resources/views/thermal/wizard.blade.php`: Formulario paso a paso con íconos.
-   `resources/views/thermal/result.blade.php`: Dashboard de resultados con semáforo y tarjetas de acción.

## 3. Integración en UI
-   **Acceso Principal:**
    -   Ubicación: `entities.index` (Listado de Entidades).
    -   Sección: "Centro de Recomendaciones".
    -   Tarjeta: "Salud Térmica".
-   **Acceso en Detalle:**
    -   Ubicación: `entities.show` (Dashboard de Entidad).
    -   Botón: "Salud Térmica".

---

## 4. Flujo de Usuario
1.  El usuario ingresa a "Mis Entidades".
2.  En el "Centro de Recomendaciones", hace clic en "Diagnóstico Térmico".
3.  Responde 3 preguntas simples (Techo, Ventanas, Sol).
4.  Recibe un Diagnóstico inmediato con su "Clase Energética" (ej: Clase E).
5.  Recibe "Top 3 Mejoras" con costo estimado e impacto.
