# Etapas de Desarrollo

Este archivo servirá para documentar y coordinar las distintas etapas del desarrollo del proyecto ModoAhorro.

## ¿Cómo usar este archivo?

- Este es un proyecto que se llama MODO AHORRO, es una Saas, que permite al usuario entrar a la plataforma, elegir la entidad, y hacer una gestoría energética, la plataforma tendria las funcionalidades, que permitira al usuario registrar las caracteristicas de la vivienda, datos de las facturas del servicio electrico, el iventarios de elementos, un motor de calculos consumo, potencia, un centro de recomendaciones que tendra varias implementaciones de mejoras, recomendaciones, ec.t
-

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
    Usuario
    Entidades
    Medidores
    Facturas
    Equipos
    Otros elementos extras




- Controladores
 los controladores deben estar separados
 Controladores para manejar Autenticación
 Controladores para manejar vistas, relaciones, ect.
 Conttroladores para manejar motor de calculos
 Controladores para manejar otros elementos extras

- Vistas Blade lo basico
- Pruebas

### 4. Pruebas y ajustes
- Test unitarios
- Test funcionales
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
