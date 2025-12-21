1. La Identidad del Proyecto
Nombre: ModoAhorro - Sistema de Gestión Energética Inteligente. Slogan sugerido: "De la estimación a la precisión: Tu Gemelo Digital Energético." Visión: Evolucionar de una calculadora de consumo manual a un sistema SaaS de "Gemelo Digital" con integración IoT.

2. El Problema (El "Gancho")
La incertidumbre: "Las facturas de luz son una caja negra. Sabemos cuánto pagamos, pero no exactamente en qué."
La imprecisión: "Las calculadoras tradicionales fallan porque asumen que un equipo consume el 100% de su potencia todo el tiempo. Un aire acondicionado no funciona así; un lavarropas tampoco."
El impacto oculto: "La falta de mantenimiento y el mal aislamiento térmico son costos invisibles que nadie mide."
3. La Solución: ¿Qué hace diferente a ModoAhorro?
Destaca los diferenciales técnicos que encontré en tu roadmap y estructura:

Precisión Basada en Física (Sprint 0):
No es una suma simple de Watts.
Utilizamos Factor de Carga (Duty Cycle) y Eficiencia Real.
Ejemplo: Distinguimos entre un motor (Aire Acondicionado, ~0.7 factor) y una resistencia (Estufa, 1.0 factor). Esto reduce el error de cálculo de un 400% a un margen del 15% real.
Gestión Integral (No solo equipos):
Confort Térmico: Diagnosticamos la "salud" de la casa (orientación, aislación) antes de recomendar cambiar el aire acondicionado.
Mantenimiento: Un equipo sucio consume más. El sistema penaliza el consumo si no se registran mantenimientos (limpieza de filtros, etc.).
Solar Water Heater: Calculadora real de amortización para Energías Renovables.
4. Demo Sugerida (Flujo de Navegación)
Si vas a mostrar la pantalla, sigue este orden lógico:

Dashboard Principal: Vista de pájaro del consumo actual.
Panel de Consumo:
Muestra los gráficos.
Destaca la Correlación Climática: "Miren, aquí cruzamos los días de calor extremo (>28°C) con los picos de consumo gracias a la integración con Open-Meteo."
Entidades y Habitaciones:
Entra a una casa -> Habitación.
Muestra la granularidad: categorías, potencia, horas de uso.
Módulos de Valor:
Muestra el Diagnóstico Térmico (el "Score" de la casa).
Muestra la sección de Mantenimiento o la calculadora de Calefones Solares.
5. Tecnología (Si el público es técnico)
Stack: Laravel 11 + Bootstrap 5 + SQLite/MySQL.
Arquitectura: Servicios modulares (ConsumptionAnalysisService, ThermalScoreService, SolarWaterService).
Integraciones: Open-Meteo API para datos climáticos históricos.
6. El Futuro (Roadmap)
Cierra con lo que se viene para mostrar ambición:

Siguientes pasos: Análisis de consumo "Standby" (vampiros energéticos) y Módulo de Vacaciones.
Meta final: Integración con medidores inteligentes (IoT) para automatizar la carga de datos.
Tip para el Meet: Usa términos como "Trazabilidad" (sabemos qué equipo estaba instalado en qué fecha) y "Fricción de Usuario" (estamos trabajando en perfiles de ocupación para que el usuario no tenga que cargar datos manualmente todo el tiempo). Eso demuestra que el sistema es robusto y está pensado para escalar.

