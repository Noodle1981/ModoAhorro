# THERMAL_COMFORT_MODULE.md
# Módulo Implementado: Diagnóstico de Envolvente Térmica

## 1. Descripción General
Modulo que permite al usuario realizar una autoevaluación rápida de la calidad térmica de su vivienda para identificar pérdidas de energía pasiva y recibir recomendaciones de aislamiento.
Este módulo ataca la "Causa Raíz" del consumo ineficiente (la vivienda), complementando el análisis de equipos (los síntomas).

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
  "sun_exposure": "high|medium|low",
  "thermal_score": 45, // Calculado (0-100)
  "energy_label": "E"  // Calculado (A-G)
}
```

### Arquitectura de Servicios
1.  **`ThermalScoreService`** (`App\Services\Thermal\ThermalScoreService`):
    -   Calcula un puntaje base de 50.
    -   Penaliza por techos de chapa (-20), losa sin aislar (-10), chifletes (-15), sol directo (-10).
    -   Premia por aislación (+15), DVH (+20), marcos con RPT (+5).
    -   Determina la etiqueta (A > 90, B > 75, etc.).

2.  **`ThermalAdviceEngine`** (`App\Services\Thermal\ThermalAdviceEngine`):
    -   Genera recomendaciones basadas en condiciones específicas.
    -   *Ejemplo:* Si `drafts_detected` es true -> Recomienda "Burletes".
    -   *Ejemplo:* Si `roof_type` es chapa y no hay aislación -> Recomienda "Membrana Líquida".

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
