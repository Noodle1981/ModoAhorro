# Etapas de Desarrollo
Este archivo servirá para documentar y coordinar las distintas etapas del desarrollo del proyecto ModoAhorro.




- Utilizar una API gratuita de clima (ejemplo: OpenWeatherMap, WeatherAPI, Meteostat) para obtener temperaturas históricas del periodo y localidad del usuario.
- Para cada día del periodo de facturación, consultar la temperatura máxima.
- Definir un umbral (ejemplo: 24°C) para considerar días de uso probable del equipo.
- Ajustar automáticamente la cantidad de días de uso del equipo según los días que superen el umbral.
- Mostrar al usuario la estimación y permitir que la acepte o la ajuste manualmente.

- Este es un proyecto que se llama MODO AHORRO, es una Saas, que permite al usuario entrar a la plataforma, elegir la entidad, y hacer una gestoría energética, la plataforma tendria las funcionalidades, que permitira al usuario registrar las caracteristicas de la vivienda, datos de las facturas del servicio electrico, el iventarios de elementos, un motor de calculos consumo, potencia, un centro de recomendaciones que tendra varias implementaciones de mejoras, recomendaciones, ec.t
- OpenWeatherMap (https://openweathermap.org/api)
- WeatherAPI (https://www.weatherapi.com/)
- Meteostat (https://meteostat.net/en/api)

-
- Reduce la carga de memoria del usuario.
- Mejora la precisión del análisis energético.
- Permite recomendaciones más inteligentes y personalizadas.


- Investigar documentación y límites de cada API.
- Prototipar integración y lógica de ajuste automático.
- Validar resultados y experiencia de usuario.
## Ejemplo de estructura

### 1. Planificación
- Objetivo general
la plataforma debe permitir al usuario, elegir la entidad, y hacer una gestoría energética, la plataforma tendria las funcionalidades que podrá elegir, esto estara determinado por el tipo de susbcripción
- Funcionalidades principales
Carga de Facturas
Carga de Entidades que son 3 (hogar, Oficina y Comercio), todos tendran plan gratuito para el hogar, y para las otras entidades un plan premium
Carga de Inventario
Motor de Calculo
Centro de Mejoras y rendimiento
- Requerimientos técnicos
La base de datos debe ser estrictamente escalable, bien pensada porque contiene mucha logica de calculos

### 2. Configuración inicial
- Instalación de dependencias
- Configuración de base de datos
- Primer commit

### 3. Desarrollo de funcionalidades
- Modelos y migraciones
    Entidades

    Medidores

    Facturas



 Controladores para manejar otros elementos extras


- Corrección de errores

### 5. Despliegue

## Avances recientes (13/11/2025)

- Implementación de gestión de equipos exclusivamente por habitación (room).
- Eliminación de rutas y vistas generales de equipos para evitar duplicidad y errores.
- Agregado campo "Cantidad" en el formulario de equipos, permitiendo carga múltiple (se crean registros individuales).
- Visualización de cantidad de equipos por habitación en la vista principal.
- Validación de migraciones y seeders para catálogo de equipos y categorías.
- Adaptación de vistas y controladores para el nuevo flujo UX.
- Preparado para la etapa de historial, bajas y reemplazos de equipos.

---

Escribe debajo de esta línea para comenzar:

---
implementacion de nabvar descriptiva 