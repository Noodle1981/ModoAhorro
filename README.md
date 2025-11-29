# ModoAhorro - Sistema de Gestión Energética

Aplicación Laravel para análisis y optimización del consumo energético en hogares, comercios y oficinas.

## 🎯 Características Principales

### Gestión de Entidades
- Soporte para múltiples tipos: Hogar, Comercio, Oficina
- Gestión de habitaciones por entidad
- Sala "Portátiles" creada automáticamente para equipos recargables

### Equipamiento
- **39 equipos** de ejemplo en base de datos
- **8 categorías**: Climatización, Iluminación, Electrodomésticos, Entretenimiento, Cocina, Oficina, **Portátiles**, Otros
- Gestión por habitación con potencia nominal (W)
- Equipos portátiles (notebooks, tablets, cargadores) en categoría dedicada

### Análisis de Consumo
- **Panel de consumo** con comparación visual: Facturado vs Calculado
- Desglose por categoría con porcentajes
- Agrupación por habitación en vistas de ajuste
- Cálculo automático de kWh basado en potencia y uso

### Ajustes de Uso
- Configuración de horas/día y días de uso por equipo
- Soporte para uso diario, semanal y ocasional
- Histórico de equipos inactivos preservado en facturas pasadas
- Visualización de consumo calculado por equipo

### Módulos de Valor Agregado
- **Mantenimiento**: Gestión de tareas (limpieza filtros, etc.) con penalización de consumo por vencimiento.
- **Solar Térmico**: Calculadora de ahorro para calefones solares vs Gas/Electricidad.
- **Clima**: Integración con Open-Meteo para ajustar consumo de climatización.

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

- **Backend**: Laravel 11.x
- **Frontend**: Blade Templates + Bootstrap 5
- **Base de datos**: SQLite (desarrollo)
- **Build**: Vite
- **Análisis**: ConsumptionAnalysisService para cálculos de consumo

## 📱 Rutas Principales

- `/dashboard` - Panel principal
- `/entities` - Gestión de entidades
- `/consumption/panel` - Panel de análisis de consumo
- `/usage-adjustments/{invoice}/edit` - Ajuste de uso por factura
- `/equipment` - Gestión de equipos por habitación

## 🎨 Características UX

- Agrupación visual por habitación
- Comparación facturado vs calculado con código de colores
- Desglose por categoría con gráficos de barras
- Preservación de histórico de equipos inactivos
- Período de factura con cantidad de días

## 📝 Próximos Pasos Recomendados

1. **Optimización de cálculos**: Cachear resultados de consumo
2. **Reportes**: Exportación a PDF/Excel
3. **Gráficos**: Integrar Chart.js para visualizaciones
4. **Notificaciones**: Alertas de consumo anormal
5. **Multi-tenant**: Soporte para múltiples usuarios

## 🐛 Debugging

Los archivos de prueba temporales se limpian automáticamente. Si encuentras alguno:
```bash
Remove-Item debug_*.php, test_*.php -ErrorAction SilentlyContinue
```

## 📄 Licencia

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
