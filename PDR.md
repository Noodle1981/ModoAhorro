# Registro de Desarrollo de Producto (PDR) — ModoAhorro

**Proyecto:** ModoAhorro  
**Versión Actual:** 2.1 (Soporte B2B Modular & Heladerías Artesanales)  
**Última Actualización:** 2026-08-17  

---

## 1. Historial de Decisiones de Producto (Changelog de Decisiones)

### [2026-08-17] — Implementación de Arquitectura Modular de Perfiles Comerciales
- **Contexto:** Necesidad de auditar y modelar comercios con perfiles térmicos y de proceso específicos (especialmente Heladerías Artesanales con frío negativo 24/7 y picos de mantecado).
- **Decisión Tomada:** Se eliminó el `match` y enum rígido `comercio_type`. Se creó el namespace `App\Domain\Commercial\` con un `CommercialProfileRegistry` extensible.
- **Sub-rubros incorporados:**
  1. `heladeria_artesanal` (Heladería Artesanal & Fábrica de Frío).
  2. `pizzeria` (Pizzería & Empanadas).
  3. `cafeteria` (Cafetería & Bar).
  4. `restaurante_general` (Restaurante integral).
  5. `retail_general` (Comercio al público).
  6. `oficina_servicios` (Oficinas profesionales).
- **Impacto en Frontend:** Selector en 2 niveles en [`Edit.vue`](file:///d:/ModoAhorro/resources/js/Pages/Entity/Edit.vue) con tarjeta interactiva de sensibilidad térmica.

### [2026-08-17] — Rediseño de la Landing Page para Beta Cerrada
- **Contexto:** La landing page no reflejaba adecuadamente el estado de Beta Privada ni la adaptabilidad comercial.
- **Decisiones Tomadas:**
  - Se configuró el botón de *Tu Comercio* con badge `BETA PRIVADA` y enlace a WhatsApp (`5492644533704`) con mensaje de consulta por rubro.
  - Se añadieron acentos y secciones oscuras/degradadas con el color de marca `#009966` en la sección Hero, capturas de pantalla y CTA final.
  - Se integró el banner oficial `modo_ahorro_banner.png` en la cabecera.

---

## 2. Mapa de Rutas y Navegación Clave

| Ruta | Nombre de Ruta | Controlador / Vista | Descripción |
| :--- | :--- | :--- | :--- |
| `/` | `welcome` | `welcome.blade.php` | Landing page institucional con capturas y CTAs |
| `/dashboard` | `dashboard` | `DashboardController@index` | Panel principal de métricas y resumen de entidad activa |
| `/gestion/entidad/perfil` | `gestion.entity.edit` | `EntityController@edit` (`Entity/Edit.vue`) | Configuración física y comercial de la entidad |
| `/gestion/infraestructura` | `gestion.infrastructure.index` | `InfrastructureController@index` | Gestión de ambientes y artefactos |
| `/gestion/contratos` | `gestion.contracts.index` | `ContractController@index` | Gestión de contratos y distribuidoras |
| `/gestion/facturas` | `gestion.invoices.index` | `InvoiceController@index` | Carga y gestión de facturas de luz |
| `/analisis/ajuste-uso` | `analisis.usage-adjustment.index` | `UsageAdjustmentController@index` | Sintonía fina y calibración de tanques |

---

## 3. Estado de la Suite de Tests

- **Tests Unitarios:** 8 pruebas (Cálculo de análisis, registros comerciales, física de perfiles).
- **Tests de Feature:** 43 pruebas (Gestión física, contratos, facturas, perfil entidad, unificación de cuotas, motor adaptativo).
- **Estado Global:** **51 tests pasando (100% éxito), 262 aserciones.**

---

## 4. Backlog / Próximos Pasos (Roadmap)

1. **Piloto de Heladería Artesanal:** Validar la carga de artefactos (Mantecadora, Pasteurizador, Pozos) y la conciliación con factura real en San Juan.
2. **Soporte Multi-Vector (Gas Natural):** Incorporar `EnergySource` en el catálogo de artefactos y permitir carga de facturas de distribuidoras de gas.
3. **Ingesta de Sensores IoT:** Endpoint API/Webhook para recibir telemetría de medidores de potencia y sensores de temperatura en cámaras de frío.
