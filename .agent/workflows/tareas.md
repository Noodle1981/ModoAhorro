---
description: Prompt: Reestructuración de Vista de Auditoría y Motor de Clasificación
---

1 quiero todos los equipos tengan la etiqueta correspondiente a los dias de la frecuencia y los dias del periodo

es decir si la freciencia es diaria y el periodo facturado es 64 debe decir (64/64)

por ejemplo esto es para ocacionalmente

Foco Ventilador
Consumo Variable
5 W	
Ocasionalmente (19 días) =
0.1 h/día • 19 / 64 días (30%)
0.010 kWh	
0.003
-0.007


pero tambien tengo para aire acondicionado

Ventilador de techo
Climatización
75 W	
Diario (64 días) =
6 h/día • 64 días de calor detectados / 64 total
23.040 kWh	
6.070
-16.970

deberia ser asi

Ventilador de techo
Climatización
75 W	
Diario (64 días) - 54 dias de calor detectado
6 h/día • 54 / 64 total - 
valor nuevo kWh 	
6.070
-16.970


ANALIZAR COMO PIMPLEMENTARLO.