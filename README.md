# ModoAhorro - Sistema de Gestión Energética

Aplicación Laravel para análisis y optimización del consumo energético en hogares, comercios y oficinas.

## 🎯 Características Principales

### Gestión de Entidades
- Soporte para múltiples tipos: Hogar, Comercio, Oficina
- Gestión de habitaciones por entidad
- Sala "Portátiles" creada automáticamente para equipos recargables
- **Dashboards específicos por tipo de entidad**

### Stack Tecnológico Moderno
- **Frontend**: Tailwind CSS + UI Kit Components + Alpine.js
- **Componentes Blade Reutilizables**: Card, Button, Badge, Table, Input, Select, Alert, Stat-Card
- **Backend**: Laravel 12.x con Livewire
- **Gráficos**: Chart.js con temas Tailwind

### Equipamiento
- **39 equipos** de ejemplo en base de datos
- **8 categorías**: Climatización, Iluminación, Electrodomésticos, Entretenimiento, Cocina, Oficina, **Portátiles**, Otros
- Gestión por habitación con potencia nominal (W)
- Equipos portátiles (notebooks, tablets, cargadores) en categoría dedicada

### Análisis de Consumo
- **Panel de consumo avanzado** con paginación y gráficos interactivos (Chart.js)
- **Correlación Climática**: Análisis de consumo vs temperatura y días extremos
- **KPIs Financieros**: Seguimiento de costo diario y evolución del precio del kWh
- Desglose por categoría con porcentajes
- Agrupación por habitación en vistas de ajuste
- Cálculo automático de kWh basado en potencia y uso

### Ajustes de Uso
- Configuración de horas/día y días de uso por equipo
- Soporte para uso diario, semanal y ocasional
- **Interfaz visual con sliders y selectores de días**
- Histórico de equipos inactivos preservado en facturas pasadas
- Visualización de consumo calculado por equipo

### Módulos de Valor Agregado
- **Confort Térmico**: Autodiagnóstico bioclimático con wizard visual (Orientación, Aislación, Ventana Sur) con recomendaciones personalizadas.
- **Mantenimiento**: Gestión de tareas (limpieza filtros, etc.) con penalización de consumo por vencimiento.
- **Solar Térmico**: Calculadora de ahorro para calefones solares vs Gas/Electricidad (Precios actualizados San Juan).
- **Standby Analysis**: Análisis de consumo fantasma con toggles visuales
- **Reemplazos**: Catálogo de oportunidades de ahorro con cálculo de ROI
- **Clima**: Integración con Open-Meteo para visualizar "Días de Calor > 28°C" en facturas.

## 🎨 UI Kit Components

Sistema de componentes Blade reutilizables con Tailwind CSS:

```php
// Cards
<x-card hover>Contenido</x-card>

// Buttons
<x-button variant="primary">Acción</x-button>
<x-button variant="secondary" size="sm">...</x-button>

// Badges
<x-badge variant="success" dot>Activo</x-badge>

// Tables
<x-table hover>...</x-table>

// Inputs
<x-input name="email" label="Email" icon="envelope" />

// Alerts
<x-alert type="warning">Mensaje importante</x-alert>

// Stat Cards
<x-stat-card title="Total" :value="100" icon="bi-graph-up" color="blue" />
```

## 🚀 Instalación

```bash
# Clonar repositorio
git clone <repository-url>
cd ModoAhorroFINAL26

# Instalar dependencias
composer install
npm install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Migrar y poblar base de datos
php artisan migrate:fresh --seed

# Iniciar servidor
php artisan serve
npm run dev
```

## 📊 Estructura de Base de Datos

### Modelos Principales
- **Entity**: Hogares, comercios u oficinas
- **Room**: Habitaciones/ambientes de cada entidad
- **Equipment**: Equipos eléctricos con potencia nominal
- **Invoice**: Facturas de energía con consumo real
- **EquipmentUsage**: Registro de uso de equipos por período
- **Contract**: Contratos con proveedores de energía

### Seeders Incluidos
- `DatosHogarSeeder`: Casa de prueba completa con 39 equipos
- `EquipmentCategorySeeder`: 8 categorías (incluye Portátiles)
- `ProvinceSeeder` / `LocalitySeeder`: Datos geográficos
- `PlanSeeder`: Planes de suscripción

## 🔧 Tecnologías

- **Backend**: Laravel 12.x + Livewire
- **Frontend**: Blade Templates + Tailwind CSS + Alpine.js
- **UI Kit**: Componentes Blade reutilizables (Card, Button, Badge, Table, Input, etc.)
- **Base de datos**: SQLite (desarrollo) / MySQL (producción)
- **Build**: Vite
- **Gráficos**: Chart.js
- **Análisis**: ConsumptionAnalysisService para cálculos de consumo

## 📁 Estructura del Proyecto

```
app/
├── Http/
│   └── Controllers/
│       ├── Entity/           # Controladores por tipo de entidad
│       │   ├── HomeEntityController.php
│       │   ├── OfficeEntityController.php
│       │   └── TradeEntityController.php
│       └── Core/             # Controladores generales
│
├── Services/                 # Lógica de negocio
│   ├── Core/                 # Servicios principales
│   ├── Climate/              # Integración climática
│   ├── Recommendations/      # Recomendaciones
│   ├── Solar/                # Cálculos solares
│   └── IoT/                  # Preparación IoT
│
resources/views/
├── components/               # UI Kit Components
│   ├── card.blade.php
│   ├── button.blade.php
│   ├── badge.blade.php
│   ├── table.blade.php
│   ├── input.blade.php
│   └── ...
├── entities/                 # Vistas por tipo
│   ├── home/
│   ├── office/
│   └── trade/
└── layouts/                  # Layouts principales
```

## 📱 Rutas Principales

- `/dashboard` - Panel principal
- `/entities/home/*` - Gestión de hogares
- `/entities/office/*` - Gestión de oficinas
- `/entities/trade/*` - Gestión de comercios
- `/consumption/panel` - Panel de análisis de consumo
- `/usage-adjustments/{invoice}/edit` - Ajuste de uso por factura
- `/equipment` - Gestión de equipos por habitación

## 🎨 Características UX

- **Diseño moderno** con Tailwind CSS y gradientes
- **Componentes reutilizables** para consistencia visual
- **Interactividad** con Alpine.js (tabs, toggles, sliders)
- Comparación facturado vs calculado con código de colores
- Desglose por categoría con gráficos de barras
- Preservación de histórico de equipos inactivos
- **Responsive design** para móviles y desktop

## 📝 Próximos Pasos Recomendados

1. **Dashboard Ejecutivo**: Métricas agregadas para CEOs
2. **Preparación IoT**: API para medidores inteligentes
3. **Reportes**: Exportación a PDF/Excel
4. **Notificaciones**: Alertas de consumo anormal
5. **Multi-tenant**: Soporte para múltiples usuarios

## 🐛 Debugging

Los archivos de prueba temporales se limpian automáticamente. Si encuentras alguno:
```bash
Remove-Item debug_*.php, test_*.php -ErrorAction SilentlyContinue
```

## 📄 Licencia

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
