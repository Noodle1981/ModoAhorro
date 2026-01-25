Si un equipo está configurado como 24 horas y Diariamente, el motor puede inferir automáticamente que es un Consumo Base (Inmutable).
Esto simplifica tu base de datos y hace que el sistema sea más inteligente. Sin embargo, para que esta lógica sea perfecta, debemos agregar una pequeña "regla de seguridad".
La Nueva Lógica de "Inmutabilidad Automática"
El motor ahora clasificará como Tanque 1 (Base) a cualquier equipo que cumpla esto:
Horas Declaradas == 24.
Periodicidad == "Diariamente".
NO es de Climatización (porque un aire prendido 24hs sigue dependiendo del clima, no es un consumo lineal constante).
¿Cómo cambia el cálculo para estos equipos?
Aunque sean inmutables, hay una diferencia física entre ellos que el motor debe conocer mediante el load_factor (que ya tienes en tu tabla equipment_types):
Equipo Lineal (Router/Cámaras): Gasta su potencia nominal todo el tiempo. (load_factor = 1.0).
Equipo Cíclico (Heladera/Freezer): Está enchufado 24hs, pero el motor arranca y corta. (load_factor = 0.3 a 0.5).
El motor hará esto en el Tanque 1:
Consumo = Potencia * 24hs * Dias * load_factor
Implementación en PHP (Laravel) con esta lógica automática
Aquí tienes el fragmento del service actualizado. Ya no necesitamos buscar si se llama "Heladera", solo miramos sus horas y frecuencia:
code
PHP
// Dentro de EnergyEngineService.php

public function calibrate(array $equipos)
{
    $remanenteFactura = $this->facturaTotal;

    // --- PASO 0: Identificación Automática de Tanques ---
    foreach ($equipos as &$eq) {
        
        // REGLA DE ORO: Si es 24/7 y no es Clima, es INMUTABLE (Tanque 1)
        if ($eq['horas_declaradas'] == 24 && 
            $eq['periodicidad'] === 'diariamente' && 
            !$eq['es_climatizacion']) {
            
            $eq['tanque'] = 1;
            $eq['elasticidad'] = 0; // No se toca
        } 
        elseif ($eq['es_climatizacion']) {
            $eq['tanque'] = 2; // Clima
        } 
        else {
            $eq['tanque'] = 3; // Rutina / Ocio
        }
    }

    // --- PROCESAR TANQUE 1 (Base Automática) ---
    foreach ($equipos as &$eq) {
        if ($eq['tanque'] === 1) {
            // El load_factor es clave: Heladera (0.4), Router (1.0)
            $eq['calibrado_kwh'] = ($eq['potencia_w'] * 24 * $this->diasPeriodo * ($eq['load_factor'] ?? 1.0)) / 1000;
            $remanenteFactura -= $eq['calibrado_kwh'];
            $eq['ajustado'] = true;
        }
    }

    // ... seguir con Tanque 2 y 3 ...
}
¿Qué pasa con los valores de intensidad (Alto, Medio, Bajo)?
Como ahora el Tanque 1 se define solo por las 24hs, las categorías de Intensidad solo las usaremos para el Tanque 3 (Ocio/Rutina) para saber a quién "castigar" o "premiar" más cuando ajustemos el sobrante de la factura.
Ejemplo de Tanque 3 con tus intensidades:
Usuario declara: Lavarropas (Intensidad: Alto) y TV (Intensidad: Medio).
Situación: La factura es más baja de lo que el usuario calculó.
Acción del motor: Como el Lavarropas es "Alto", el motor le quita más kWh al lavarropas que a la TV, porque los equipos potentes tienen más margen de error en la estimación del usuario.
Resumen de tu nueva arquitectura simplificada:
¿Es 24/7? -> Tanque 1 (Inmutable).
¿Es Clima? -> Tanque 2 (Ajuste por API y Confort Térmico).
¿Es otra cosa? -> Tanque 3 (Ajuste por Intensidad: Alto/Medio/Bajo).
¿Te gusta más este enfoque? Es mucho más robusto porque se adapta a lo que el usuario cargue, sin listas fijas de equipos.