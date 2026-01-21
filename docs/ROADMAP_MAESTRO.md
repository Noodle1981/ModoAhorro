# 🚀 ROADMAP MAESTRO - Modo Ahorro

> **Visión**: Democratizar la eficiencia energética mediante una plataforma integral que evoluciona desde auditoría pasiva hasta gestión activa con IoT, optimización financiera y monetización sostenible.

---

## 📊 Estado Actual del Proyecto

### ✅ Componentes Implementados (MVP Funcional)

#### **Motor de Cálculo de Consumos** 🔋
- ✅ Sistema de 3 fases: Entrada → Cálculo Teórico → Calibración Jerárquica
- ✅ Categorización inteligente: Base Crítica, Hormigas, Base Pesada, Ballenas
- ✅ Integración con Open-Meteo API para ajustes climáticos
- ✅ Cálculo de consumo vampiro (standby)
- ✅ Ajustes estacionales para termotanques
- ✅ Penalización por mantenimiento vencido

#### **Módulos de Recomendaciones** 💡
- ✅ Energía Solar Fotovoltaica (ROI y amortización)
- ✅ Termotanque Solar
- ✅ Optimización de Red (Peak Shifting)
- ✅ Detección de Anomalías (Vacaciones)
- ✅ Análisis de Standby
- ✅ Mantenimiento Preventivo

#### **Gestión de Datos** 📁
- ✅ Sistema de Entidades (Hogares, Oficinas, Comercios)
- ✅ Gestión de Habitaciones y Equipos
- ✅ Registro de Facturas
- ✅ Historial de Equipos (instalación/retiro)
- ✅ Bloqueo de períodos cerrados

---

## 🔴 Problemas Críticos Identificados (Auditoría 2026-01-21)

### **1. Clasificación de Equipos Frágil**
- **Problema**: Categorización por string matching (`str_contains`)
- **Impacto**: Equipos mal clasificados afectan calibración
- **Prioridad**: 🔴 CRÍTICA

### **2. Falta de Validación Pre-Calibración**
- **Problema**: No hay límites por categoría
- **Impacto**: Usuario puede declarar valores incoherentes
- **Prioridad**: 🔴 CRÍTICA

### **3. Dependencia Absoluta de API Climática**
- **Problema**: Resultados inconsistentes si API falla
- **Impacto**: Climatización puede sobre/sub-estimarse
- **Prioridad**: 🟠 ALTA

### **4. Consumo Standby Siempre Activo**
- **Problema**: No considera si usuario desenchufa equipos
- **Impacto**: Sobrestimación de consumo vampiro
- **Prioridad**: 🟠 ALTA

### **5. Umbrales Hardcoded**
- **Problema**: 28°C para calor, 15°C para frío no configurables
- **Impacto**: Imprecisión regional
- **Prioridad**: 🟡 MEDIA

---

## 🎯 ROADMAP POR FASES

### **FASE 0: Estabilización del Motor de Cálculo** ⚡
**Objetivo**: Resolver problemas críticos identificados en auditoría  
**Duración**: 2-3 semanas  
**Prioridad**: 🔴 CRÍTICA

#### Sprint 0.1: Refactorización de Clasificación de Equipos
- [ ] Agregar campo `tier` (enum) a tabla `equipment_types`
- [ ] Migración de datos: clasificar tipos existentes
- [ ] Eliminar lógica de string matching duplicada
- [ ] Crear interfaz para reclasificación manual de equipos
- [ ] Tests unitarios para clasificación

#### Sprint 0.2: Validación Pre-Calibración
- [ ] Implementar `ValidationService` con límites por categoría:
  - Hormigas: Max 5% del total
  - Base Crítica: Max 30%
  - Base Pesada: Max 40%
  - Ballenas: Resto
- [ ] Alertas visuales en UI cuando se exceden límites
- [ ] Sugerencias automáticas de ajuste
- [ ] Tests de validación

#### Sprint 0.3: Mejoras en Manejo de Datos Climáticos
- [ ] Implementar fallback inteligente si API falla
- [ ] Agregar tabla `climate_thresholds` para configuración regional
- [ ] Campo `is_unplugged_when_off` en `equipment_usages`
- [ ] Mejorar caché de datos climáticos

#### Sprint 0.4: Audit Trail y Logging
- [ ] Crear tabla `calibration_logs`
- [ ] Registrar cada decisión del calibrador
- [ ] Dashboard de debugging para administradores
- [ ] Exportación de logs a CSV

**Verificación Fase 0:**
- [ ] Suite de tests unitarios para `ConsumptionCalibrator` (>80% cobertura)
- [ ] Tests de integración con datos reales
- [ ] Validación manual con 10 facturas históricas
- [ ] Documentación técnica actualizada

---

### **FASE 1: Optimización y UX** 🎨
**Objetivo**: Mejorar experiencia de usuario y performance  
**Duración**: 3-4 semanas  
**Prioridad**: 🟠 ALTA

#### Sprint 1.1: Interfaz de Ajuste de Consumos
- [ ] Preview en tiempo real (AJAX) del impacto de ajustes
- [ ] Gráficos interactivos de distribución por categoría
- [ ] Indicadores visuales de coherencia (semáforo)
- [ ] Wizard guiado para usuarios nuevos

#### Sprint 1.2: Performance y Caché
- [ ] Implementar caché de resultados para facturas cerradas
- [ ] Optimizar consultas N+1 en `UsageAdjustmentController`
- [ ] Lazy loading de equipos en listados
- [ ] Índices de base de datos optimizados

#### Sprint 1.3: Reportes y Exportación
- [ ] Exportación de consumo a PDF (DomPDF)
- [ ] Exportación a Excel (Maatwebsite)
- [ ] Gráficos comparativos mensuales/anuales
- [ ] Reporte de ahorro proyectado

#### Sprint 1.4: Notificaciones y Alertas
- [ ] Sistema de notificaciones in-app
- [ ] Alertas de mantenimiento vencido
- [ ] Alertas de consumo anómalo
- [ ] Recordatorios de carga de facturas

**Verificación Fase 1:**
- [ ] Tests de performance (tiempo de carga <2s)
- [ ] Tests de usabilidad con 5 usuarios beta
- [ ] Validación de PDFs generados
- [ ] Métricas de engagement (tiempo en plataforma)

---

### **FASE 2: Inteligencia y Automatización** 🤖
**Objetivo**: Machine Learning y sugerencias predictivas  
**Duración**: 4-6 semanas  
**Prioridad**: 🟡 MEDIA

#### Sprint 2.1: Detección de Patrones
- [ ] Algoritmo de detección de patrones de uso
- [ ] Sugerencias automáticas de horarios óptimos
- [ ] Predicción de consumo futuro (ARIMA/Prophet)
- [ ] Alertas de desviación de patrón habitual

#### Sprint 2.2: Benchmarking Inteligente
- [ ] Comparación con hogares similares (anonimizada)
- [ ] Ranking de eficiencia por región
- [ ] Sugerencias personalizadas de mejora
- [ ] Gamificación (badges, logros)

#### Sprint 2.3: Asistente Virtual
- [ ] Chatbot con IA para consultas comunes
- [ ] Integración con GPT-4 para análisis de facturas
- [ ] Recomendaciones contextuales
- [ ] Onboarding interactivo

**Verificación Fase 2:**
- [ ] Precisión de predicciones >85%
- [ ] Tests A/B de sugerencias automáticas
- [ ] Feedback de usuarios sobre asistente
- [ ] Métricas de adopción de recomendaciones

---

### **FASE 3: IoT y Tiempo Real** 📡
**Objetivo**: Integración con hardware y monitoreo en vivo  
**Duración**: 6-8 semanas  
**Prioridad**: 🟢 BAJA (Post-MVP)

#### Sprint 3.1: Integración con Medidores Inteligentes
- [ ] API para recepción de datos de medidores
- [ ] Dashboard de consumo en tiempo real
- [ ] Alertas de picos de consumo
- [ ] Control remoto de equipos (ON/OFF)

#### Sprint 3.2: Protocolo de Comunicación
- [ ] Implementar MQTT para IoT
- [ ] Seguridad end-to-end (TLS)
- [ ] Manejo de desconexiones y reconexión
- [ ] Buffer de datos offline

#### Sprint 3.3: Hardware Propio (Opcional)
- [ ] Diseño de medidor inteligente ModoAhorro
- [ ] Firmware ESP32/Arduino
- [ ] Calibración y certificación
- [ ] Producción piloto (100 unidades)

**Verificación Fase 3:**
- [ ] Tests de carga (1000 dispositivos simultáneos)
- [ ] Tests de latencia (<500ms)
- [ ] Certificación de seguridad
- [ ] Piloto con 20 usuarios beta

---

### **FASE 4: Monetización y Escalabilidad** 💰
**Objetivo**: Modelo de negocio sostenible  
**Duración**: 4-6 semanas  
**Prioridad**: 🟡 MEDIA

#### Sprint 4.1: Sistema de Planes y Suscripciones
- [ ] Integración con Stripe/MercadoPago
- [ ] Planes: Básico (gratis), Pro, Enterprise
- [ ] Límites por plan (entidades, equipos, reportes)
- [ ] Facturación automática

#### Sprint 4.2: Marketplace de Productos
- [ ] Integración con API de Mercado Libre
- [ ] Recomendaciones de equipos eficientes
- [ ] Links de afiliados con tracking
- [ ] Comisiones por venta

#### Sprint 4.3: Servicios Profesionales
- [ ] Directorio de instaladores certificados
- [ ] Solicitud de presupuestos
- [ ] Sistema de reviews y ratings
- [ ] Comisión por referencia

**Verificación Fase 4:**
- [ ] Tests de flujo de pago completo
- [ ] Validación de comisiones de afiliados
- [ ] Tests de seguridad PCI-DSS
- [ ] Métricas de conversión

---

### **FASE 5: Expansión y Comunidad** 🌍
**Objetivo**: Escalamiento regional y construcción de comunidad  
**Duración**: Continuo  
**Prioridad**: 🟢 BAJA

#### Sprint 5.1: Internacionalización
- [ ] Soporte multi-idioma (i18n)
- [ ] Adaptación de tarifas por país
- [ ] Integración con APIs climáticas regionales
- [ ] Localización de contenido

#### Sprint 5.2: API Pública
- [ ] Documentación con Swagger/OpenAPI
- [ ] Rate limiting y autenticación OAuth
- [ ] SDK para desarrolladores
- [ ] Marketplace de integraciones

#### Sprint 5.3: Comunidad y Contenido
- [ ] Blog de eficiencia energética
- [ ] Foro de usuarios
- [ ] Webinars y tutoriales
- [ ] Programa de embajadores

**Verificación Fase 5:**
- [ ] Documentación API completa
- [ ] Tests de integración con partners
- [ ] Métricas de engagement comunitario
- [ ] NPS (Net Promoter Score) >50

---

## 📋 Checklist de Producción

### **Infraestructura**
- [ ] Migrar de SQLite a MySQL/PostgreSQL
- [ ] Configurar Redis para caché
- [ ] Implementar queue workers (Laravel Horizon)
- [ ] Configurar backups automáticos diarios
- [ ] CDN para assets estáticos
- [ ] Monitoreo con Sentry/New Relic

### **Seguridad**
- [ ] Certificado SSL (Let's Encrypt)
- [ ] Rate limiting en API
- [ ] Sanitización de inputs
- [ ] Protección CSRF
- [ ] Auditoría de seguridad externa
- [ ] Política de privacidad y GDPR

### **DevOps**
- [ ] CI/CD con GitHub Actions
- [ ] Tests automatizados en pipeline
- [ ] Staging environment
- [ ] Blue-Green deployment
- [ ] Rollback automático en errores

### **Documentación**
- [ ] README completo
- [ ] Guía de instalación
- [ ] Documentación de API
- [ ] Manual de usuario
- [ ] Guía de contribución
- [ ] Changelog versionado

---

## 🎯 Hitos Clave

| Hito | Fecha Objetivo | Descripción |
|------|----------------|-------------|
| **Alpha** | Semana 4 | Motor de cálculo estabilizado, problemas críticos resueltos |
| **Beta Privada** | Semana 8 | UX mejorada, 50 usuarios beta testeando |
| **Beta Pública** | Semana 12 | Reportes, notificaciones, 500 usuarios |
| **MVP 1.0** | Semana 16 | Monetización básica, marketplace de afiliados |
| **IoT Preview** | Semana 24 | Integración con medidores inteligentes (piloto) |
| **Escalamiento** | Semana 32 | Multi-región, API pública, 10,000 usuarios |

---

## 📊 Métricas de Éxito

### **Técnicas**
- Cobertura de tests: >80%
- Tiempo de respuesta: <2s (p95)
- Uptime: >99.5%
- Bugs críticos: 0 en producción

### **Producto**
- Usuarios activos mensuales: 10,000 (año 1)
- Tasa de retención: >60% (mes 3)
- NPS: >50
- Ahorro promedio por usuario: >15% en facturas

### **Negocio**
- Conversión a plan pago: >5%
- Comisiones de afiliados: $5,000/mes (año 1)
- Costo de adquisición (CAC): <$10
- Lifetime Value (LTV): >$100

---

## 🚨 Riesgos y Mitigaciones

| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|--------------|---------|------------|
| API climática caída | Media | Alto | Caché robusto + fallback a promedios históricos |
| Baja adopción de usuarios | Alta | Crítico | Marketing de contenido + freemium agresivo |
| Competencia de utilities | Media | Alto | Diferenciación por UX y comunidad |
| Problemas de escalabilidad | Baja | Alto | Arquitectura cloud-native desde inicio |
| Cambios regulatorios | Baja | Medio | Asesoría legal + flexibilidad en modelo |

---

## 🛠️ Stack Tecnológico Recomendado

### **Backend**
- Laravel 11 (PHP 8.3)
- MySQL 8.0 / PostgreSQL 15
- Redis (caché + queues)
- Laravel Horizon (queue monitoring)

### **Frontend**
- Blade + Alpine.js (interactividad)
- Chart.js / ApexCharts (gráficos)
- Bootstrap 5 / Tailwind CSS
- Livewire (para componentes reactivos)

### **Infraestructura**
- DigitalOcean / AWS / Hetzner
- Docker + Kubernetes (opcional)
- Cloudflare (CDN + DDoS)
- GitHub Actions (CI/CD)

### **Monitoreo**
- Sentry (error tracking)
- Google Analytics / Plausible
- Laravel Telescope (debugging)
- Uptime Robot (monitoring)

---

## 📚 Recursos y Referencias

### **Documentación Técnica**
- [Auditoría del Motor de Cálculo](AUDITORIA.MD)
- [Manual Integral del Proyecto](MANUAL_INTEGRAL.md)
- [Deuda Técnica](TECHNICAL_DEBT.md)
- [Checklist de Producción](PRODUCTION_CHECKLIST.md)

### **Módulos Implementados**
- [Optimización de Red](modules/GRID_OPTIMIZATION_MODULE.md)
- [Módulo de Vacaciones](modules/VACATION_MODULE.md)
- [Mantenimiento](modules/MAINTENANCE_MODULE.md)
- [Standby](modules/STANDBY_IMPLEMENTATION.md)
- [Confort Térmico](modules/THERMAL_COMFORT_MODULE.md)

### **Lógica de Negocio**
- [Cálculo de Energía](logic/ENERGY_CALC_FIX.md)
- [Cobertura Solar](logic/SOLAR_COVERAGE_LOGIC.MD)
- [Estrategia de Calibración](logic/calibration_strategy.md)
- [Factores de Carga](logic/load_factors.md)

---

## 🎉 Visión a Largo Plazo (2-3 años)

**Modo Ahorro** se convertirá en la plataforma líder de eficiencia energética en LATAM, con:

- **100,000+ usuarios activos** gestionando su consumo
- **Red de instaladores certificados** en 10 países
- **Hardware propio** (medidores inteligentes) en 10,000 hogares
- **Partnerships** con utilities y gobiernos para programas de eficiencia
- **Impacto ambiental**: 50,000 toneladas de CO₂ evitadas anualmente
- **Modelo B2B**: Soluciones para PyMEs y edificios corporativos

---

**Última actualización**: 2026-01-21  
**Versión**: 1.0  
**Autor**: Equipo Modo Ahorro

> 💡 **Nota**: Este roadmap es un documento vivo. Se actualizará trimestralmente basándose en feedback de usuarios, métricas de producto y cambios en el mercado.
