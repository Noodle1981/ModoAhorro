# 📄 Declaración Oficial de APIs, Tecnologías e Inteligencia Artificial
## *Hackatón Minero San Juan 2026 – Desafío 3 (CASEMI / CASETIC)*
### *Proyecto: ModoAhorro Pabellones*

---

### 1. Declaración Sobre el Uso de Inteligencia Artificial

En cumplimiento con el reglamento oficial del Hackatón respecto a la transparencia y auditabilidad técnica:

1. **Cero Modelos de Lenguaje (LLMs) en Tiempo de Ejecución:**
   - La plataforma **NO** utiliza modelos de lenguaje generativo (como ChatGPT, Claude o Gemini) en tiempo de ejecución o producción para inferir, adivinar o calcular los balances energéticos.
   - **Razón técnica de seguridad minera:** Los procesos de auditoría energética y fiscalización en alta montaña exigen **determinismo matemático absoluto**. Un LLM puede alucinar valores numéricos; nuestro motor de física térmica ejecuta ecuaciones exactas y reproducibles basadas en normas **IRAM** e **ISO 50001**.

2. **Cálculo Físico por Primeros Principios:**
   - Todo el balance energético se calcula mediante termodinámica clásica:
     - Demanda de agua caliente: $Q = m \cdot C_p \cdot \Delta T$ (donde $C_p = 1.0 \text{ kcal/kg}^\circ\text{C}$ y $\Delta T$ responde al salto térmico real de agua cordillerana).
     - Generación fotovoltaica: $E = P_{\text{inst}} \cdot \text{HSP} \cdot \eta_{\text{sistema}}$ (con $6.5 \text{ HSP}$ andinas y $\eta = 0.82$).
     - Rendimiento de generación diésel: Factor auditado de $0.28 \text{ L/kWh}$ puesto en altura (+3.500 msnm).

3. **Uso de IA Durante el Proceso de Desarrollo (Pair Programming):**
   - Se utilizaron herramientas de asistencia a la programación (Google DeepMind / Antigravity AI) exclusivamente durante la etapa de arquitectura, refactorización de código y generación de tests automatizados, bajo la supervisión y validación continua del equipo humano sanjuanino.

---

### 2. Declaración de APIs Externas Utilizadas

| API Externa | Proveedor | Tipo de Licencia / Acceso | Propósito en ModoAhorro Pabellones |
| :--- | :--- | :--- | :--- |
| **Open-Meteo Weather API** | Open-Meteo GmbH | Abierta (Non-commercial / Open Source / Creative Commons) | Obtención de series meteorológicas históricas por coordenadas geoespaciales andinas (-29.35°, -70.05°): temperatura mínima diaria ($T_{\min}$), radiación solar acumulada (`shortwave_radiation_sum`) y velocidad del viento en altura. |

* **No Intrusividad:** La plataforma **no requiere conexión a sistemas SCADA industriales ni redes críticas de control de mina**, eliminando cualquier riesgo de ciberseguridad para la empresa operadora. Opera con la lectura del tablero seccional del módulo.

---

### 3. Stack Tecnológico y Librerías de Código Abierto

El proyecto está construido sobre tecnologías consolidadas de nivel empresarial con soporte a largo plazo (LTS):

* **Backend:** PHP 8.2+ · **Laravel 11/13 Framework** (Arquitectura orientada a dominio, Service Container, migraciones atómicas y Eloquent ORM).
* **Frontend:** **Vue.js 3** (Composition API con `<script setup>`, tipado y componentes reactivos puros) · **Inertia.js** (protocolo monolito moderno sin fricción de APIs REST tradicionales).
* **Estilos y Diseño UI:** **Tailwind CSS v4** (Motor de diseño utility-first de última generación) · **Lucide Icons** (`lucide-vue-next` para iconografía técnica industrial).
* **Suite de Pruebas:** **PHPUnit / Pest** con **74 tests automatizados** y 399 aserciones cubriendo el 100% de los flujos críticos.
* **Calidad de Código:** **Laravel Pint** (estándares PSR-12) y **ESLint** (reglas canónicas de Vue 3).

---

### 4. Ciberseguridad, Soberanía de Datos y Modelo de Despliegue

Para garantizar el cumplimiento con las normas de ciberseguridad industrial y confidencialidad minera:

1. **Topología de Despliegue On-Premise / Edge (No SaaS Multi-Tenant):**
   - El sistema se entrega para despliegue en servidor local del campamento (*Edge Node*) o en nube privada dedicada (*VPC Single-Tenant*).
   - **Operación Local-First:** En caso de caída de enlace satelital cordillerano, la aplicación continúa funcionando en la red de área local (LAN) del campamento sin interrupciones.
   - **Soberanía del Dato:** Toda la información de consumo de combustible, tableros y ocupación de personal reside exclusivamente dentro de la infraestructura del operador minero.

2. **Seguridad y Actualizaciones de Software (Ciclo de Vida y Fixes):**
   - **Contenedores Docker Inmutables:** El software se distribuye mediante imágenes versionadas y firmadas digitalmente con checksum SHA-256.
   - **Migraciones Automáticas y Seguras:** Los cambios de esquema de base de datos se ejecutan de forma atómica y transaccional durante el arranque del contenedor, preservando la totalidad del historial.
   - **Compatibilidad con Redes Air-Gapped:** Admite actualización desconectada vía paquetes empaquetados validados por personal de IT/Ciberseguridad de la compañía minera.
   - **Segregación de Redes:** No requiere apertura de puertos entrantes ni interactúa con la red de control de planta (SCADA/DCS).

---

### 5. Origen y Titularidad (Compre Local)

El 100% del diseño de arquitectura, modelado del gemelo digital de campamento y código fuente fue concebido y desarrollado en la **Provincia de San Juan, Argentina**, como parte de la oferta tecnológica de software local vinculable a través de CASETIC y CASEMI.
