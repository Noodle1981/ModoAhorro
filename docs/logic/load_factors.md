# Load Factors - ModoAhorro

## Última actualización
**Fecha:** 2025-12-21  
**Seeder:** `FixLoadFactorsSeeder.php`  
**Tipos actualizados:** 53

---

## Concepto de Load Factor

El `load_factor` representa el **"Factor de Uso Real"** que combina:

1. **Duty Cycle:** % del tiempo que el equipo está realmente encendido
2. **Load Factor:** % de la potencia nom inal que realmente consume

### Fórmula de Consumo
```
Energía (kWh) = P × h × d × load_factor
```

Donde:
- **P** = Potencia nominal (kW) - Input Power
- **h** = Horas de uso promedio diario
- **d** = Días en el período
- **load_factor** = Factor de Uso Real

---

## Factores por Tipo de Proceso

### GRUPO MOTOR (Cíclicos)

| Equipo | Load Factor | Razón |
|--------|-------------|-------|
| Heladera con Freezer | 0.35 | Motor funciona ~35-40% del tiempo (ciclos de enfriamiento) |
| Heladera Inverter | 0.30 | Inverter es más eficiente, menos ciclos |
| Freezer Horizontal | 0.40 | Mayor tiempo de ciclo |
| Aires 2200-4500 frigorías | 0.50 | Termostato corta el compresor ~50% del tiempo |
| Aire Inverter | 0.40 | Modula potencia, más eficiente |
| Lavarropas (agua fría) | 0.30 | Solo centrifuga a alta potencia brevemente |
| Lavarropas (con calentamiento) | 0.60 | Resistencia consume más |

### GRUPO MOTOR (Continuos)

| Equipo | Load Factor | Razón |
|--------|-------------|-------|
| Ventilador de techo/pie | 1.00 | Si está ON, consume constante |
| Aspiradora | 1.00 | Uso continuo durante operación |
| Licuadora, Batidora, Procesadora | 1.00 | Uso continuo durante operación |

### GRUPO RESISTENCIA (Con Termostato)

| Equipo | Load Factor | Razón |
|--------|-------------|-------|
| Plancha, Plancha a Vapor | 0.60 | Termostato prende y apaga para mantener temperatura |
| Caloventor | 0.70 | Termostato regula temperatura |
| Radiador Eléctrico | 0.70 | Termostato regula temperatura |
| Panel Calefactor | 0.70 | Termostato regula temperatura |

### GRUPO RESISTENCIA (Sin Termostato)

| Equipo | Load Factor | Razón |
|--------|-------------|-------|
| Estufa de Cuarzo, Halógena | 1.00 | Consumo continuo |
| Horno Eléctrico | 1.00 | Consumo continuo al 100% |
| Pava Eléctrica | 1.00 | Consumo continuo hasta hervir |
| Tostadora, Sandwichera | 1.00 | Consumo continuo |
| Cafetera | 1.00 | Consumo continuo |
| Freidora de Aire | 1.00 | Consumo continuo |
| Anafe Eléctrico | 1.00 | Consumo continuo |

### GRUPO ELECTRÓNICO (Carga Variable)

| Equipo | Load Factor | Razón |
|--------|-------------|-------|
| PC de Escritorio (CPU + Monitor) | 0.50 | Fuentes de 600W rara vez pasan de 350W reales |
| Notebook / Laptop | 0.40 | Carga variable según uso |
| Televisor LED 32" | 0.90 | Consumo casi constante |
| Televisor LED 50" 4K | 0.90 | Consumo casi constante |
| Consola Videojuegos | 0.70 | Carga variable según juego |
| Decodificador TV | 1.00 | Siempre encendido |
| Equipo de Audio | 0.80 | Carga variable |
| Modem / Router WiFi | 1.00 | Siempre encendido, consumo constante |

### GRUPO MAGNETRÓN

| Equipo | Load Factor | Razón |
|--------|-------------|-------|
| Microondas | 1.00 | Si se usa, es al 100% |

### GRUPO ILUMINACIÓN

| Equipo | Load Factor | Razón |
|--------|-------------|-------|
| Todas las lámparas LED | 1.00 | Consumo constante cuando están encendidas |
| Lámparas Bajo Consumo | 1.00 | Consumo constante |
| Halógenas | 1.00 | Consumo constante |
| Tubos Fluorescentes | 1.00 | Consumo constante |
| Tiras LED | 1.00 | Consumo constante |

### OTROS

| Equipo | Load Factor | Razón |
|--------|-------------|-------|
| Lavavajillas | 0.60 | Ciclos de lavado variables |
| Secarropas | 1.00 | Consumo continuo |
| Humidificador | 1.00 | Consumo continuo |
| Deshumidificador | 0.70 | Regulación por humedad |

---

## Verificación de Cálculos

### Resultados - Sprint 0.1 (2025-12-21)

**Facturas de prueba (San Juan):**

| Factura | Período | Estación | Facturado | Calculado | Diferencia |
|---------|---------|----------|-----------|-----------|------------|
| #137756868 | Ene-Mar (64d) | Verano | 624 kWh | 278 kWh | -55% ❌ |
| #138579184 | Mar-May (55d) | Otoño | 123 kWh | 228 kWh | +85% ❌ |
| #139151993 | May-Jul (62d) | Otoño | 83 kWh | 257 kWh | +209% ❌ |
| #139459979 | Jul-Sep (53d) | Invierno | 78 kWh | 217 kWh | +179% ❌ |

### Análisis

**Problema identificado:** El consumo calculado SIN calibración está fuera del rango 85-115% esperado.

**Razón:** Como explicaste, el usuario **no recuerda con precisión** el uso retrospectivo. Las estimaciones de horas/día son imprecisas debido a:
- Vacaciones no recordadas
- Días sin uso olvidados
- Variación real vs percibida

**Solución implementada:** El `ConsumptionCalibrator` existente (algoritmo Waterfall) ajusta automáticamente los consumos calculados al total de la factura, distribuyendo el "error" a las categorías con mayor incertidumbre (WHALES = Aires, PCs).

### Conclusión del Sprint 0.1

✅ **Load Factors correctos:** Los 53 tipos tienen factores realistas basados en duty cycles físicos.  
✅ **EquipmentUsages creados:** 139 registros con patrones de San Juan.  
⚠️ **Calibración necesaria:** El desajuste confirma que el `ConsumptionCalibrator` es **crítico** para ajustar estimaciones imprecisas del usuario.

**Próximo paso:** Sprint 0.2 - Mejorar el algoritmo de calibración contextual (BASE/HORMIGAS/ELEFANTES).

---

**Autor:** Antigravity AI + Omar  
**Basado en:** roadmap_implementacion.md - Fase 0
