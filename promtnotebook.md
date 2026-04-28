Para lograr una estructura visual universal, escalable e intuitiva, debemos limpiar la tabla equipment_categories.csv
 y agrupar los equipos por su función o espacio de uso, independientemente de cómo el Motor v3 los calcule por detrás.
Aquí tienes la propuesta de Categorías Universales diseñada para escalar desde un Hogar hasta una Finca o Pyme:
Nueva Estructura Universal de Categorías (Solo Interfaz Visual)
Estas serán las categorías puras que verá el usuario en los menús desplegables de tu frontend en Vue.js. Se agrupan por "necesidad humana":
1. Climatización y Ambiente
Todo lo que modifica la temperatura o mueve el aire.
Ejemplos: Aires Acondicionados, Estufas, Radiadores, Ventiladores.
2. Refrigeración
Todo lo que conserva alimentos en frío constante.
Ejemplos: Heladeras, Freezers de pozo, Cavas de vino.
3. Cocina y Preparación
Equipos para cocinar o calentar alimentos y bebidas.
Ejemplos: Hornos eléctricos, Pavas eléctricas, Microondas, Cafeteras.
4. Lavado y Limpieza
Equipos orientados a la higiene, que suelen funcionar por "ciclos".
Ejemplos: Lavarropas, Secarropas, Lavavajillas, Aspiradoras.
5. Electrónica y Entretenimiento
Equipos de ocio y pantallas.
Ejemplos: TVs, Consolas de juegos, Equipos de audio.
6. Informática y Oficina
Herramientas de trabajo y computación.
Ejemplos: PCs de escritorio, Notebooks, Monitores, Impresoras.
7. Iluminación
Fuentes de luz de cualquier tipo.
Ejemplos: Lámparas LED, Tubos fluorescentes, Tiras LED, Iluminación exterior.
8. Agua y Bombeo
Sistemas hídricos (Clave para escalar a Pymes/Fincas).
Ejemplos: Termotanques eléctricos, Bombas de agua, Motores de riego.
9. Conectividad y Seguridad
Equipos de funcionamiento pasivo o infraestructura 24/7.
Ejemplos: Routers WiFi, Cámaras de seguridad, Alarmas, Servidores/Racks.

--------------------------------------------------------------------------------
¿Cómo se mantiene intacto el Motor v3? (El Desacoplamiento)
Esta nueva estructura reemplaza a los nombres con sufijos que tenías en tu equipment_categories.csv
, pero no rompe tu sistema.
La genialidad de tu arquitectura ("Logic Override") es que el Motor de Cálculo ignora esta categoría visual. Cuando el usuario elige una "Pava Eléctrica" dentro de la categoría Cocina y Preparación, el motor consulta la tabla equipment_types y ve que su consumption_logic es BASE_LOAD y su unidad es people_proportional
. Con eso, el motor sabe exactamente a qué Tanque enviarlo (Tanque 1 o Tanque 4) sin importar en qué menú estaba guardado el equipo
.

Copia el siguiente bloque de texto y pégalo directamente para reemplazar todo el contenido de tu archivo equipment_categories.csv
:
id,name,created_at,updated_at,description
1,"Climatización y Ambiente","2026-04-27 12:00:00","2026-04-27 12:00:00","Todo lo que modifica la temperatura o mueve el aire"
2,"Refrigeración","2026-04-27 12:00:00","2026-04-27 12:00:00","Todo lo que conserva alimentos en frío constante"
3,"Cocina y Preparación","2026-04-27 12:00:00","2026-04-27 12:00:00","Equipos para cocinar o calentar alimentos y bebidas"
4,"Lavado y Limpieza","2026-04-27 12:00:00","2026-04-27 12:00:00","Equipos orientados a la higiene, que suelen funcionar por ciclos"
5,"Electrónica y Entretenimiento","2026-04-27 12:00:00","2026-04-27 12:00:00","Equipos de ocio y pantallas"
6,"Informática y Oficina","2026-04-27 12:00:00","2026-04-27 12:00:00","Herramientas de trabajo y computación"
7,"Iluminación","2026-04-27 12:00:00","2026-04-27 12:00:00","Fuentes de luz de cualquier tipo"
8,"Agua y Bombeo","2026-04-27 12:00:00","2026-04-27 12:00:00","Sistemas hídricos y calentamiento de agua"
9,"Conectividad y Seguridad","2026-04-27 12:00:00","2026-04-27 12:00:00","Equipos de funcionamiento pasivo o infraestructura 24/7"
Al subir esta actualización, tu base de datos quedará con una estructura completamente amigable y escalable, eliminando la confusión visual de los "Tanques" en la vista del usuario, pero manteniendo la arquitectura intacta por detrás.

actualizar el archivo MasterCleanCatalogueSeeder.php para ejecutarlo 

