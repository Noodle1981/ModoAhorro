# Documento de Requerimientos del Producto (PDR) - ModoAhorro

## 1. Visión General
**ModoAhorro** es un ecosistema SaaS inteligente diseñado para democratizar la eficiencia energética. El sistema permite a hogares, comercios y oficinas auditar, simular y optimizar su consumo eléctrico sin necesidad de hardware costoso inicial, evolucionando hacia una gestión activa mediante integraciones IoT y análisis predictivo.

### 1.1 Propuesta de Valor
*   **Transparencia**: Entender exactamente en qué se gasta la energía.
*   **Optimización**: Estrategias concretas para reducir costos sin perder confort.
*   **Sustentabilidad**: Facilitar la transición a energías renovables (Solar).

---

## 2. Objetivos Estratégicos
1.  **Cálculo de Consumo de Extremo a Extremo**: Cerrar la brecha entre el consumo facturado y el real de los equipos.
2.  **Educación Energética**: Proveer diagnósticos automáticos y sugerencias de ahorro personalizadas.
3.  **Escalabilidad SaaS**: Soportar múltiples tipos de entidades (Hogares, Comercios, Oficinas) con dashboards específicos.
4.  **Preparación para el Futuro**: Establecer las bases para la integración con medidores inteligentes (IoT).

---

## 3. Arquitectura Técnica
El sistema utiliza un enfoque monolítico modular con una capa de servicios desacoplada para los cálculos complejos.

*   **Backend**: Laravel 12.x (PHP 8.2+).
*   **Frontend**: Blade Templates + Tailwind CSS + Alpine.js (UI Kit Components).
*   **Interactividad**: Livewire y Chart.js para visualizaciones de datos dinámicas.
*   **Base de Datos**: SQLite para desarrollo/demo y MySQL para producción.
*   **Integraciones**: Open-Meteo API (Datos climáticos).

---

## 4. Módulos Funcionales

### 4.1 Gestión de Entidades y Equipamiento
*   Múltiples perfiles: Hogar, Oficina, Comercio.
*   Gestión de habitaciones y jerarquía de equipos.
*   Categorización inteligente (Base, Hormigas, Elefantes) para calibración de consumo.

### 4.2 Análisis y Calibración de Consumo
*   **Motor de Calibración**: Algoritmo que ajusta el consumo teórico (Potencia × Horas) a la realidad de la factura, distribuyendo deltas según el peso de cada categoría.
*   **Trazabilidad**: Registro de fechas de instalación/retiro de equipos para auditorías históricas precisas.

### 4.3 Recomendaciones de Valor Agregado
*   **Confort Térmico**: Autodiagnóstico de la envolvente edilicia y recomendaciones bioclimáticas.
*   **Análisis de Standby**: Identificación de "consumo vampiro" y su impacto económico.
*   **Mantenimiento**: Alertas para limpieza de filtros y service de equipos.

### 4.4 Energías Renovables
*   **Simulador Fotovoltaico**: Cálculo de superficie de techo necesaria, ROI y ahorro estimado.
*   **Calefacción Solar**: Análisis de viabilidad para termotanques solares.

### 4.5 Optimización Financiera
*   **Grid Optimization**: Análisis de tarifas por tiempo de uso (ToU) y sugerencias de desplazamiento horario (Peak Shifting).
*   **Módulo de Vacaciones**: Checklist de ahorro y exclusión automática de periodos de ausencia en proyecciones.

---

## 5. Requerimientos UX/UI
El sistema se basa en un **UI Kit** propio construido con Tailwind CSS para asegurar consistencia y velocidad de desarrollo:

*   **Componentes Atómicos**: Cards dinámicas, botones con variantes, badges de estado, tablas responsivas.
*   **Visualización**: Uso intensivo de gráficos de barras y torta (Chart.js) con temas visuales acordes a la marca Modohorro.
*   **Responsive First**: El 70% de las interacciones (cargas de equipos, ajustes) debe ser fluida en dispositivos móviles.

---

## 6. Roadmap y Próximos Pasos

### Fase Actual: Consolidación (100% Sprints 0-8)
*   Calibración inteligente completada.
*   Módulos de energía solar y optimización de red operativos.

### Fase Próxima: Expansión (Sprint 9-10)
*   **Dashboard Ejecutivo**: Métricas agregadas para gestión a gran escala.
*   **API IoT**: Capacidad de recibir lecturas en tiempo real de medidores inteligentes.
*   **Reportes PDF**: Generación de reportes de auditoría descargables.

---

## 7. Verificación de Lógica
El PDR exige que toda lógica de cálculo sea verificable mediante comandos Artisan dedicados:
*   `php artisan test:anomaly` (Lógica de vacaciones)
*   `php artisan test:grid-optimization` (Ahorro tarifario)
*   `php artisan test:climate` (Integración climática)
