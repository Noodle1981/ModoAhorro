# Deuda Técnica y Mejoras Pendientes

## 🔴 Crítico (Resolver antes de Sprint 2)

### 1. **Duplicación de Campos en Invoices** ✅ RESUELTO
- ~~`consumption_kwh` vs `total_energy_consumed_kwh`~~
- ~~`energy_cost` vs `cost_for_energy`~~
- ~~`taxes_cost` vs `taxes`~~
- **Estado**: Migración aplicada, campos consolidados

### 2. **Falta de Validación de Desviación**
- No hay alertas cuando consumo calculado difiere mucho del facturado
- **Impacto**: Usuarios pueden tener errores del 500% sin saberlo
- **Solución**: Sprint 1 - `ValidationService`

### 3. **Equipos sin Fecha de Instalación**
- No se puede saber si un equipo existía en un período histórico
- **Impacto**: Equipos nuevos aparecen en facturas antiguas
- **Solución**: Sprint 1 - campos `installed_at` y `removed_at`

---

## 🟡 Importante (Resolver en Sprint 2-3)

### 4. **Sin Cache de Datos Climáticos**
- Cada request a la API es costoso
- **Solución**: Tabla `climate_data` con cache de 30 días

### 5. **Falta de Tests Automatizados**
- Solo testing manual
- **Riesgo**: Romper funcionalidades al agregar nuevas
- **Solución**: Tests unitarios para Services críticos

### 6. **Sin Logging Estructurado**
- Difícil debuggear en producción
- **Solución**: Implementar Laravel Log con contexto

---

## 🟢 Mejoras Futuras (Post-MVP)

### 7. **Performance del Panel de Consumo**
- Cálculos en tiempo real sin cache
- **Solución**: Cache de resultados para facturas cerradas

### 8. **UI/UX Mejorable**
- Falta feedback visual en ajustes
- No hay preview de impacto antes de guardar
- **Solución**: AJAX para preview en tiempo real

### 9. **Sin Exportación de Reportes**
- Usuarios no pueden exportar a PDF/Excel
- **Solución**: Sprint 9 - integración con DomPDF

---

## 📋 Checklist de Calidad por Sprint

Antes de considerar un sprint "completo", verificar:

- [ ] Migración aplicada sin errores
- [ ] Seeder actualizado (si aplica)
- [ ] Service creado con métodos documentados
- [ ] Controller actualizado
- [ ] Vistas actualizadas
- [ ] Testing manual exitoso
- [ ] Commit con mensaje semántico
- [ ] README.md actualizado
- [ ] Sin errores de lint/syntax

---

## 🛠️ Refactorings Pendientes

### 1. **ConsumptionPanelController**
- Actualmente tiene lógica de agrupación por categoría
- **Debería**: Delegar a `ConsumptionAnalysisService`

### 2. **UsageAdjustmentController**
- Método `edit` muy largo
- **Debería**: Extraer lógica a `UsageAdjustmentService`

### 3. **Seeders**
- `DatosHogarSeeder` tiene 200+ líneas
- **Debería**: Separar en `EntitySeeder`, `EquipmentSeeder`, etc.

---

## 📊 Métricas de Calidad de Código

**Objetivo para Mes 2:**
- Cobertura de tests: >60%
- Complejidad ciclomática: <10 por método
- Duplicación de código: <5%
- Documentación: 100% de Services

---

## 🚨 Lecciones Aprendidas del Proyecto Anterior

### ❌ Errores a NO Repetir

1. **JSON en campos relacionales**
   - Problema: Rooms con JSON de equipos
   - Solución actual: Tablas normalizadas ✅

2. **Mezclar lógicas en un solo Service**
   - Problema: Un Service gigante que hacía todo
   - Solución actual: Un Service por módulo ✅

3. **Sin roadmap claro**
   - Problema: Agregar features sin orden
   - Solución actual: Roadmap por sprints ✅

4. **Modificar migraciones antiguas**
   - Problema: Romper base de datos en producción
   - Solución actual: Solo migraciones nuevas ✅

---

## 💡 Recomendaciones

### Para Mantener el Proyecto Saludable

1. **Revisión semanal de deuda técnica**
   - Dedicar 20% del tiempo a refactoring

2. **Documentar decisiones importantes**
   - Crear `docs/decisions/` con ADRs (Architecture Decision Records)

3. **No agregar features sin tests**
   - Mínimo: test manual documentado
   - Ideal: test automatizado

4. **Git commits pequeños y frecuentes**
   - Mejor 5 commits pequeños que 1 gigante

5. **Pedir feedback temprano**
   - Mostrar prototipos antes de implementar completo
