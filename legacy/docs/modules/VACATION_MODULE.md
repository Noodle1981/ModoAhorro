# VACATION_MODULE.md
# Especificación: Módulo de Recomendaciones para Vacaciones

## 1. Objetivo
Generar una lista de chequeo (Checklist) personalizada para preparar la casa antes de un viaje, maximizando el ahorro sin comprometer la seguridad.

## 2. Inputs Requeridos
* `trip_duration_days` (int): Input del usuario.
* `inventory`: Lista de equipos del usuario (para detectar dependencias).

## 3. Lógica de Recomendaciones (Rules Engine)

El sistema debe evaluar cada regla y generar una tarjeta de acción.

### A. Regla de Conectividad (Router/Modem)
* **Condición:** ¿Existe en el inventario algún equipo tipo 'Cámara', 'Alarma Smart', 'Enchufe Wifi'?
* **Si TRUE (Hay seguridad):**
    * Acción: "DEJAR ENCENDIDO".
    * Mensaje: "Tus cámaras dependen del Wi-Fi. No lo desconectes."
    * Ahorro: $0.
* **Si FALSE (No hay seguridad):**
    * Acción: "DESCONECTAR".
    * Ahorro: `PotenciaRouter * 24h * DíasViaje`.

### B. Regla de Refrigeración (Heladera)
* **Condición:** `trip_duration_days`
* **Caso Corto (< 5 días):**
    * Acción: "MODO ECO".
    * Detalle: "No la desconectes. Sube la temperatura al mínimo para ahorrar."
* **Caso Medio (5 - 20 días):**
    * Acción: "VACIAR PERECEDEROS".
    * Detalle: "Consume lo que se vence. Sube el termostato."
* **Caso Largo (> 20 días):**
    * Acción: "DESCONECTAR Y ABRIR".
    * Detalle: "Vacíala por completo, desconéctala y deja las puertas abiertas para evitar moho."
    * Ahorro: `PotenciaHeladera * 24h * LoadFactor(0.35) * DíasViaje`.

### C. Regla de Agua Caliente (Termotanque)
* **Si es Eléctrico:**
    * Acción: "DESCONECTAR SIEMPRE".
    * Detalle: "Es un gasto innecesario mantener agua caliente que nadie usará."
    * Ahorro: `ConsumoDiarioEstimado * DíasViaje`.
* **Si es Gas:**
    * Acción: "PILOTO OFF / MODO VACACIONES".

### D. Regla de Vampiros (Standby)
* **Acción:** "DESCONECTAR TODO".
* **Objetivos:** TV, Microondas, PC, Consolas.
* **Razón:** Ahorro energético + Protección contra tormentas eléctricas mientras no estás.
* Ahorro: `SumaConsumoStandbyDiario * DíasViaje`.

---

## 4. Visualización en Dashboard (UI)

**Entrada:**
> 🌴 **Modo Vacaciones**
> "¿Por cuántos días te vas?" [ Input: 15 ] [ Calcular ]

**Salida (Tarjeta de Resumen):**
> **Resumen de tu Viaje (15 días)**
> Si sigues estos pasos, ahorrarás aprox: **$12,500** y protegerás tus equipos.

**Checklist Interactivo:**
(El usuario puede marcar lo que ya hizo)

1.  [ ] **Termotanque:** Apagar/Desenchufar. (Ahorro: $4,500)
2.  [ ] **Vampiros:** Desenchufar TV, PC y Microondas. (Ahorro: $1,200)
    * *Nota: Protege tus equipos de rayos.*
3.  [x] **Router Wifi:** ¡DEJAR ENCENDIDO! ⚠️
    * *Motivo: Tienes cámaras de seguridad.*
4.  [ ] **Heladera:** Subir termostato (Modo Mínimo).
5.  [ ] **Luces:** Programar sensor/timer en entrada.