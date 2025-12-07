# Manual Integral del Proyecto "Modo Ahorro"

## 1. Visión General
**Modo Ahorro** es una plataforma integral de gestión energética diseñada para hogares y pequeñas empresas. Su objetivo es democratizar la eficiencia energética mediante herramientas de diagnóstico, simulación y optimización, permitiendo a los usuarios reducir sus costos y huella de carbono sin necesidad de hardware costoso inicialmente.

La plataforma evoluciona desde una herramienta de auditoría pasiva hacia un sistema de gestión activa (IoT) y optimización financiera.

---

## 2. Arquitectura Técnica
El sistema está construido sobre un stack robusto y moderno:

*   **Framework**: Laravel 10/11 (PHP 8.2+).
*   **Base de Datos**: SQLite (Optimizado para despliegue local/demo) / MySQL (Producción).
*   **Frontend**: Blade Templates + Bootstrap 5 + JavaScript (Chart.js para visualizaciones).
*   **Patrón de Diseño**: MVC (Modelo-Vista-Controlador) con una capa de Servicios (`App\Services`) para lógica de negocio compleja.

### Servicios Principales
*   `ConsumptionAnalysisService`: Motor de cálculo de consumo histórico y proyecciones.
*   `SolarPowerService`: Calculadora de viabilidad fotovoltaica.
*   `VacationService`: Lógica de detección de anomalías y checklists de ahorro.
*   `GridOptimizerService`: Algoritmo de arbitraje de tarifas (Peak Shifting).
*   `ClimateDataService`: Integración con APIs climáticas para normalizar consumo.

---

## 3. Módulos del Sistema

### 3.1. Gestión de Entidades y Consumo
El núcleo del sistema. Permite modelar hogares ("Entidades") con sus habitaciones y equipamiento detallado.
*   **Funcionalidad**: Registro de facturas, carga de equipos, cálculo de consumo estimado vs. real.
*   **Documentación Relacionada**: 
    *   [Planes y Entidades](planes_y_entidades.md)
    *   [Lógica de Cálculo de Energía](ENERGY_CALC_FIX.md)

### 3.2. Energía Solar (Fotovoltaica y Térmica)
Herramientas para evaluar el retorno de inversión en energías renovables.
*   **Calculadora Solar**: Estima generación, ahorro y amortización de paneles solares basándose en el techo disponible y consumo histórico.
*   **Termotanque Solar**: Simula el ahorro de gas/electricidad al instalar calentadores solares de agua.
*   **Documentación Relacionada**:
    *   [Lógica de Cobertura Solar](SOLAR_COVERAGE_LOGIC.MD)
    *   [Lógica de Agua Solar](SOLAR_WATER_LOGIC.md)

### 3.3. Módulo de Vacaciones (Detección de Anomalías)
Gestiona el comportamiento energético durante ausencias prolongadas.
*   **Funcionalidad**: Checklist personalizado (desconectar router, vaciar heladera), y **Lógica de Anomalía de Datos** que marca automáticamente las facturas de periodos vacacionales como "No Representativas" para no ensuciar las proyecciones anuales.
*   **Documentación Relacionada**:
    *   [Módulo de Vacaciones](VACATION_MODULE.md)
    *   [Lógica de Anomalía de Datos](DATA_ANOMALY_LOGIC.md)

### 3.4. Optimización de Red (Grid Optimization)
Módulo financiero para usuarios con tarifas horarias (Time-of-Use).
*   **Funcionalidad**: Detecta equipos "desplazables" (Lavarropas, Bombas) y calcula el ahorro exacto de mover su uso de horas Pico a horas Valle o "Resto" (Plan B).
*   **Características**: Simulador de Tarifas, Línea de tiempo visual 24h.
*   **Documentación Relacionada**:
    *   [Módulo de Optimización de Red](GRID_OPTIMIZATION_MODULE.md)

### 3.5. Medidor Inteligente (Simulador IoT)
Una demostración de capacidades de tiempo real para vender hardware.
*   **Funcionalidad**: Simulador estocástico en JS que muestra voltaje, potencia instantánea y costo por hora en tiempo real, con simulación de "Corte Remoto" y alertas de baja tensión.
*   **Documentación Relacionada**:
    *   [Demo Medidor Inteligente](SMART_METER_DEMO.md)

### 3.6. Mantenimiento y Consumo Fantasma
*   **Mantenimiento**: Seguimiento de salud de equipos (limpieza de filtros AA) para evitar degradación de eficiencia.
*   **Standby (Vampiro)**: Análisis del costo oculto de equipos en espera y herramientas para mitigarla.
*   **Documentación Relacionada**:
    *   [Módulo de Mantenimiento](MAINTENANCE_MODULE.md)
    *   [Implementación Standby](STANDBY_IMPLEMENTATION.md)

### 3.7. Validación y Trazabilidad (Sprint 1)
Nuevas capacidades para asegurar la integridad de los datos históricos y la precisión de los cálculos.
*   **Validación de Consumo**: Servicio que compara automáticamente el consumo calculado vs. facturado, generando alertas (Verde/Amarillo/Rojo) y sugerencias de corrección.
*   **Trazabilidad de Equipos**: Historial de instalación y retiro (`installed_at`, `removed_at`) para que los ajustes de facturas pasadas reflejen solo los equipos activos en ese momento.
*   **Bloqueo de Facturas**: Mecanismo de seguridad para "Cerrar Periodos" y evitar modificaciones accidentales en facturas ya auditadas.
*   **Documentación Relacionada**:
    *   [Walkthrough Sprint 1](walkthrough.md) (Detalles de implementación)

## 5. Guía de Instalación y Despliegue

### Requisitos Previos
*   PHP 8.2+
*   Composer
*   Node.js & NPM

### Pasos
1.  Clonar el repositorio.
2.  Instalar dependencias PHP: `composer install`
3.  Instalar dependencias JS: `npm install`
4.  Configurar entorno: `cp .env.example .env` (Configurar DB_CONNECTION=sqlite)
5.  Generar clave: `php artisan key:generate`
6.  Crear base de datos: `touch database/database.sqlite`
7.  Ejecutar migraciones y seeders: `php artisan migrate --seed`
    *   *Nota: Esto poblará la base de datos con tarifas, equipos y usuarios de prueba.*
8.  Iniciar servidor: `php artisan serve`
9.  Compilar assets (en otra terminal): `npm run dev`

---

## 6. Verificación y Testing
El proyecto cuenta con una suite de comandos de prueba personalizados para verificar la lógica de negocio compleja:

*   `php artisan test:anomaly`: Verifica la lógica de exclusión de facturas en vacaciones.
*   `php artisan test:grid-optimization`: Verifica el cálculo de ahorro por desplazamiento horario (Plan A y Plan B).
*   `php artisan test:climate`: Verifica la integración con APIs climáticas.
*   `php artisan test:vacation`: Verifica la generación de checklists de viaje.

---

*Documentación actualizada: 07/12/2025*
