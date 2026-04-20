# REPLACEMENT_MODULE.md
# Módulo de Reemplazos: Cómo Funciona

---

## Dos vistas, dos propósitos

### `/efficiency-benchmarks` — Panel de Administración

**¿Para quién?** El administrador del sistema (vos).

**¿Qué muestra?** La *base de datos* de alternativas eficientes: qué tipos de equipo tienen benchmark, cuánto ahorro se estima, precio de referencia y término de búsqueda en Mercado Libre.

**¿Para qué sirve?** Para configurar y mantener el sistema. Es el "catálogo de productos" que gestiona el administrador — los usuarios finales no lo ven.

---

### `/entities/{type}/{id}/replacements` — Vista del Usuario

**¿Para quién?** El usuario final (dueño de la entidad).

**¿Qué muestra?** Recomendaciones *personalizadas* para **sus equipos específicos**: "Tu heladera consume X kWh/mes, si la reemplazás por esta otra ahorrás $Y en Z meses".

**¿Para qué sirve?** Para tomar decisiones de compra concretas, con ROI calculado sobre sus datos reales.

---

### Relación entre ambas

```
[efficiency-benchmarks]          [equipos del usuario]
  "¿Qué alternativas              "¿Cuánto consume
   existen en el mercado?"         cada equipo tuyo?"
         │                                │
         └──────────────┬─────────────────┘
                        ▼
              [ReplacementService]
                        │
                        ▼
           [/replacements - Vista usuario]
         "¿Qué te conviene cambiar primero?"
```

Sin benchmarks cargados → el servicio no puede generar recomendaciones → muestra "Todo Optimizado".

---

## Lógica del Motor (`ReplacementService`)

### Fuente de datos de consumo

El servicio prioriza datos reales, pero tiene fallback:

1. **Datos reales** → `EquipmentUsage.consumption_kwh` de la última factura analizada
2. **Estimación** → `nominal_power_w × avg_daily_use_hours × 30 días / 1000` (kWh/mes)

Las tarjetas muestran el badge **"estimado"** cuando se usa el fallback.

### Ajustes al factor de ahorro

El `efficiency_gain_factor` del benchmark se ajusta dinámicamente:

| Condición | Ajuste |
|---|---|
| Equipo tiene >10 años | +15% de ahorro potencial |
| Etiqueta energética C, D o E | +10% de ahorro potencial |
| Equipo ya es Inverter y tiene <10 años | Se omite (ya es eficiente) |

### Cálculo de ROI

```
Ahorro mensual (ARS) = consumo_kwh × factor_ahorro × tarifa_kwh
Meses de recupero   = precio_referencia / ahorro_mensual
```

### Veredictos

| Meses de recupero | Veredicto |
|---|---|
| ≤ 12 meses | 💎 Retorno Inmediato |
| ≤ 36 meses | 🔥 Gran Oportunidad |
| > 36 meses | 📈 Ahorro a Largo Plazo |

---

## Archivos clave

| Archivo | Rol |
|---|---|
| `app/Services/Recommendations/ReplacementService.php` | Motor de cálculo |
| `app/Http/Controllers/Recommendations/ReplacementController.php` | Controlador |
| `app/Http/Controllers/Admin/EfficiencyBenchmarkController.php` | CRUD admin |
| `app/Models/EfficiencyBenchmark.php` | Modelo de benchmarks |
| `resources/views/replacements/index.blade.php` | Vista del usuario |
| `resources/views/efficiency_benchmarks/index.blade.php` | Vista admin |
| `database/seeders/EfficiencyBenchmarkSeeder.php` | Datos iniciales |

---

## Datos semilla disponibles

Ejecutar para cargar benchmarks iniciales:

```bash
php artisan db:seed --class=EfficiencyBenchmarkSeeder
```

Cubre 19 tipos de equipo: aires acondicionados (todos → Inverter), iluminación (fluorescente/incandescente → LED), heladera, lavarropas, termotanque eléctrico (→ solar), TVs, PC Gamer, Monitor.

---

## Próximas mejoras sugeridas

- [ ] Filtrar por categoría en la vista del usuario
- [ ] Botón "Buscar en MeLi" que abra la búsqueda directamente
- [ ] Integrar precios reales via API de Mercado Libre
- [ ] Mostrar comparativa visual (equipo actual vs. recomendado)