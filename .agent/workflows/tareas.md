---
description: He reestructurado mis categorías en 8 grupos técnicos: Confort Térmico, Línea Blanca, Cocción, Iluminación, Electrónica/Oficina, Cuidado Personal, Agua/Mantenimiento y Movilidad Eléctrica
---


Por favor, actualizá el código del Controlador de Ajuste para que los cálculos de ahorro y los reportes se agrupen por estas categorías. Esto nos permitirá aplicar factores de carga diferenciados: por ejemplo, la Línea Blanca usará ciclos de marcha y la Electrónica usará promedios de intensidad variable

Para profundizar en la estructura de una red domiciliaria —especialmente útil si estás modelando la arquitectura de datos para un software de gestión energética— no basta con las categorías clásicas. Hay que desglosarlas por perfil de carga y uso funcional.

Aquí tenés una categorización técnica completa para EquipmentTypeSeeder.php, no olvidar de seguir trabajando con la nueva lógica de cálculo (Factor de Carga y Eficiencia) que hemos implementado:

1. Confort Térmico (Climatización)
Es la categoría de mayor impacto estacional.

Refrigeración: Aire acondicionado (Split, Central, Ventana), climatizadores evaporativos.

Calefacción Eléctrica: Estufas de cuarzo, caloventores, radiadores de aceite, paneles vitrocerámicos, suelo radiante eléctrico.

Ventilación: Ventiladores de techo, de pie, extractores de aire.

2. Línea Blanca (Grandes Electrodomésticos)
Equipos de funcionamiento cíclico o continuo.

Refrigeración de alimentos: Heladeras, freezers, cavas de vino, frigobares.

Lavado y Secado: Lavarropas (carga frontal/superior), secarropas (por calor o centrifugado), lavasecarropas.

Limpieza de vajilla: Lavavajillas.

3. Cocción y Pequeños Electrodomésticos
Se caracterizan por tener potencias muy altas pero tiempos de uso cortos.

Cocción Eléctrica: Hornos eléctricos, anafe de inducción, microondas, freidoras de aire (AirFryer).

Preparación de infusiones/alimentos: Pavas eléctricas, cafeteras, tostadoras, licuadoras, procesadoras, batidoras.

4. Iluminación
Categoría con el mayor número de puntos de consumo, aunque de baja potencia unitaria.

Interior: Lámparas LED, paneles, tiras LED, dicroicas.

Exterior: Proyectores, iluminación ornamental, luces con sensor de movimiento.

5. Electrónica, Oficina y Entretenimiento
Suelen ser los responsables del consumo Stand-by.

Imagen y Sonido: Televisores (LED, OLED), proyectores, equipos de audio, barras de sonido, consolas de videojuegos.

Informática: Computadoras de escritorio (Desktop), notebooks, monitores, impresoras, routers/modems, celulares.

6. Cuidado Personal
Equipos de uso esporádico pero alta resistencia.

Secadores de pelo, planchitas, máquinas de afeitar, depiladoras eléctricas.

7. Equipos de Agua y Mantenimiento (Bombeo/Exterior)
Suelen ser cargas inductivas (motores) que afectan el factor de potencia.

Agua Sanitaria: Termotanque eléctrico, bombas presurizadoras, bombas de elevación (cisterna a tanque).

Mantenimiento: Bombas de filtrado de pileta, cortadoras de césped eléctricas, hidrolavadoras.

8. Movilidad Eléctrica (Categoría Emergente)
Cargadores para autos eléctricos (Wallbox), monopatines, bicicletas eléctricas.



Para que esto sea realmente escalable, te sugiero que en tu tabla de equipment_categories (o en una columna de equipment_types) agregues un campo llamado calculation_strategy.
Esto le dirá a tu controlador qué fórmula usar sin tener que programar caso por caso:
STRATEGY_CONSTANT: (Iluminación). Watts x Horas.
STRATEGY_CYCLIC: (Línea Blanca - Heladeras). Usa el load_factor como ciclo de marcha.
STRATEGY_DYNAMIC: (Electrónica). Usa el load_factor como promedio de intensidad.
STRATEGY_PULSE: (Cocción/Cuidado Personal). Watts x Minutos (alta potencia, poco tiempo).