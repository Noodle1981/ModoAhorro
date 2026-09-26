# 📋 Informe de Postulación y Estrategia: Hackatón Minero San Juan 2026
## *Proyecto: ModoAhorro Minería – Ecosistema Sanjuanino de Eficiencia Energética y Calificación de Proveedores Locales*

---

## 🎯 1. Ficha General de la Postulación

* **Nombre del Proyecto:** ModoAhorro Minería (*Smart Energy & Local Supplier Footprint*).
* **Desafío Oficial Seleccionado:** **Desafío 3: Mapeo y vinculación de proveedores locales (CASEMI / CASETIC)**.
* **Lema / Pitch Central:** 
  > *"Tecnología y sustentabilidad 100% sanjuanina para la minería: no hace falta ir a buscar a otros puertos lo que ya desarrollamos en nuestra provincia."*
* **Stack Tecnológico:** Laravel 13 (Backend auditado, 59 tests automatizados), Vue 3 + Tailwind CSS v4 (Frontend reactivo sin scroll), arquitectura modular por capas.

---

## ⚖️ 2. Alineación Matemática con la Rúbrica de Evaluación (100 Puntos)

| Criterio Oficial | Puntos | Qué se observará según las bases | Cómo lo cumple y gana nuestro proyecto |
| :--- | :---: | :--- | :--- |
| **Comprensión del Problema** | **20** | Proceso actual, usuarios, fricción y evidencia de la necesidad. | Mapeo exacto de la realidad de alta montaña (+3.500 msnm) y las compras mineras: no hay boletas convencionales ni splits en frío; hay calderas, traceado anticongelamiento, generadores diésel y dependencia de consultoras foráneas. Fricción de las PyMEs de CASEMI para certificar sustentabilidad ante operadoras internacionales. |
| **Impacto** | **20** | Valor para la minería o su cadena, métricas y efectos esperados. | Ahorro medible en litros de gasoil evitados puesto en cordillera (~1.50 USD/L), reducción de huella de carbono ($tCO_2e$) y retención de divisas dentro de la economía del conocimiento de San Juan (compre tecnológico local). |
| **Viabilidad** | **20** | Factibilidad técnica, operativa, regulatoria y de implementación. | Factibilidad inmediata: no requiere permisos de exploración ni conexión física de riesgo. Software ya programado y testeado bajo estándares de seguridad Laravel/OWASP. Alineado con las leyes sanjuaninas de compre local. |
| **Calidad e Innovación** | **15** | Coherencia, diferenciación y pertinencia del enfoque. | Enfoque disruptivo: en lugar de un "directorio estático tipo páginas amarillas", es una plataforma activa que califica energéticamente a los proveedores locales y audita sus instalaciones de montaña. |
| **Prototipo** | **10** | Nivel de demostración, funcionamiento y aprendizaje obtenido. | Sistema 100% funcional y navegable (no maquetas en Figma). Demostración en vivo de carga de auditoría, motor de balance de cargas de montaña y reporte de diagnóstico exportable. |
| **Datos, Seguridad y Uso Responsable de IA** | **10** | Privacidad, ciberseguridad, trazabilidad, riesgos y control humano. | Arquitectura backend auditada: Form Requests con validación estricta, prevención de N+1 y lazy loading, protección CSRF/XSS, almacenamiento seguro y trazabilidad de cálculos algorítmicos transparentes con control humano. |
| **Presentación y Equipo** | **5** | Claridad del pitch y capacidades para avanzar. | Pitch concreto enfocado en San Juan, economía del conocimiento y soberanía tecnológica local. Capacidad de ejecución demostrada con código real listo para producción. |

---

## 🏔️ 3. Comprensión Técnica del Terreno: La Matriz de Alta Montaña (+3.500 msnm)

Una de las principales debilidades de los proyectos que vienen de afuera es asumir consumos urbanos típicos. ModoAhorro Minería se diseña con la **matriz energética real de la cordillera sanjuanina**:

1. **Inexistencia de Climatización en Modo Frío:** 
   * A 4.000 msnm, con temperaturas entre 10 °C y -25 °C, el uso de splits de refrigeración es prácticamente nulo (excepto salas de servidores puntuales).
2. **Cargas Térmicas Críticas 24/7:**
   * **Paneles convectores y calderas de alta montaña:** Vitales para evitar hipotermia del personal y congelamiento estructural.
   * **Termotanques de alto salto térmico:** Agua de deshielo ingresando a 1 °C que debe elevarse a 45 °C para uso sanitario.
   * **Traceado Eléctrico (*Heat Tracing*):** Resistencia calefactora continua a lo largo de cañerías exteriores para evitar roturas por congelamiento.
3. **Talleres de Mantenimiento de Contratistas (CASEMI):**
   * Compresores de tornillo, soldadoras de alta potencia, bombas sumergibles de achique y grupos electrógenos de respaldo.

---

## 🔗 4. Cómo Vincula a la Oferta y la Demanda (Eje Proveedores CASEMI / CASETIC)

### Para las Operadoras Mineras (Demanda - Veladero, Josemaría, Los Azules):
* **Auditoría de Sustentabilidad de su Cadena:** Permite verificar con datos duros qué proveedores locales cumplen con estándares de eficiencia energética y menor huella de carbono.
* **Cumplimiento Real del Compre Local:** Demuestra ante el Ministerio de Minería que están contratando empresas de San Juan no solo en mano de obra básica, sino en servicios de ingeniería y software especializado.

### Para las PyMEs y Proveedores Sanjuaninos (Oferta - Socios CASEMI):
* **Herramienta de Diagnóstico Accesible:** Una PyME local de transporte, metalmecánica o campamento puede auditar sus instalaciones de faena sin pagar honorarios en dólares a consultoras de Buenos Aires.
* **Sello de Eficiencia y Competitividad:** El software emite un certificado digital de eficiencia de instalaciones que la PyME adjunta en sus pliegos de licitación minera para ganar puntos frente a competidores foráneos.

---

## 🛠️ 5. Próximos Pasos de Ejecución

1. **Creación de la Rama Git:** `feature/mineria-compre-local`.
2. **Adecuación de Catálogos de Equipos en el Backend:**
   * Incorporar equipos de alta montaña (convectores, traceado térmico, compresores, calderas).
   * Eliminar o restringir artefactos irrelevantes de refrigeración en contextos de cordillera.
3. **Formulario de Postulación Oficial:** Usar este informe para completar la inscripción en `ciclopilares.com.ar` antes del 9 de octubre.
