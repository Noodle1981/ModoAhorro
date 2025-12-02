# SMART_METER_DEMO.md
# Especificación: Simulador de Medición Inteligente (IoT Demo)

## 1. Objetivo
Crear un Dashboard en Tiempo Real simulado que demuestre las capacidades de un Medidor Inteligente. Utilizará el inventario del usuario (Ballenas y Hormigas) para generar patrones de consumo realistas mediante algoritmos estocásticos en el Frontend.

---

## 2. Motor de Simulación (JavaScript Service)

No necesitamos backend complejo. Todo ocurre en el navegador del cliente para fluidez total.

**Clase:** `SmartMeterSimulator.js`

### Variables de Entrada (Desde el Inventario del Usuario):
* `base_load_watts`: Suma de Heladeras + Routers + Standby (~150W).
* `whales_capacity_watts`: Suma de Aires + PC + Hornos (~3500W).
* `active_whales_probability`: Probabilidad de que una ballena esté prendida (ej. 30%).

### Algoritmo de Generación de Datos (Tick cada 1 segundo):

```javascript
class SmartMeterSimulator {
    constructor(baseLoad, whaleCapacity) {
        this.baseLoad = baseLoad; // Piso de consumo (Heladera)
        this.whaleCapacity = whaleCapacity;
        this.currentLoad = baseLoad;
        this.voltage = 220;
    }

    tick() {
        // 1. Simular Voltaje (Fluctuación normal de red)
        // Oscila entre 215V y 225V
        const noiseV = (Math.random() - 0.5) * 4; 
        this.voltage = 220 + noiseV;

        // 2. Simular Comportamiento de Ballenas (Encendido/Apagado)
        // A veces prenden el microondas (pico), a veces corta el aire (bajada).
        // Usamos un "ruido browniano" para que no sea caótico, sino suave.
        const change = (Math.random() - 0.5) * 200; // +/- 100W de variación
        
        // Mantener dentro de rangos lógicos
        let potentialLoad = this.currentLoad + change;
        
        // Límites: Nunca menos que la base, nunca más que la capacidad total
        if (potentialLoad < this.baseLoad) potentialLoad = this.baseLoad;
        if (potentialLoad > (this.baseLoad + this.whaleCapacity)) potentialLoad = this.baseLoad + this.whaleCapacity;

        this.currentLoad = potentialLoad;

        // 3. Simular Factor de Potencia (Coseno Fi)
        // Equipos inductivos (motores) bajan el factor.
        const pf = (this.currentLoad > 1000) ? 0.85 : 0.95;

        // 4. Calcular Corriente (Amperes)
        // W = V * A * PF  =>  A = W / (V * PF)
        const amperes = this.currentLoad / (this.voltage * pf);

        return {
            timestamp: new Date(),
            watts: Math.round(this.currentLoad),
            voltage: this.voltage.toFixed(1),
            amperes: amperes.toFixed(2),
            cost_per_hour: (this.currentLoad / 1000) * USER_TARIFF_PRICE
        };
    }
}