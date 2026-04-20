# Integración Comercial: Mercado Libre Affiliates

## ¿Qué es?

El programa **Mercado Libre Affiliates** permite generar links especiales hacia productos de MeLi. Cuando un usuario hace una compra a través de ese link, ModoAhorro recibe una **comisión automática** sin inventario ni logística.

> Documentación oficial: [https://www.mercadolibre.com.ar/afiliados](https://www.mercadolibre.com.ar/afiliados)

---

## Flujo de negocio

```
Usuario ve recomendación en ModoAhorro
         ↓
Hace clic en "Comprar"
         ↓
Va a MeLi con link de afiliado
         ↓
Compra el producto
         ↓
ModoAhorro recibe comisión automáticamente
```

---

## Cómo activarlo (sin API, sin producción)

Esto se puede hacer **hoy mismo** con el MVP:

1. Registrarse en el programa de afiliados (gratuito)
2. Buscar en MeLi el producto recomendado (ej: "Aire Acondicionado Inverter 3500 frigorías")
3. Generar el link de afiliado para ese producto o búsqueda
4. Pegarlo en el panel admin: `/efficiency-benchmarks` → Editar → campo **"Link de Afiliado"**
5. El botón **"Comprar"** en las tarjetas de recomendación ya apunta a ese campo — sin cambiar código

---

## Comisiones de referencia por categoría

| Categoría | Comisión aprox. |
|---|---|
| Electrodomésticos (heladera, lavarropas) | 5–7% |
| Climatización (aires acondicionados) | 5–7% |
| Iluminación (LEDs, tubos) | 8–10% |
| Tecnología (TVs, monitores, PCs) | 3–5% |

*Las comisiones exactas se confirman al registrarse en el programa.*

---

## Arquitectura técnica (ya implementada)

El campo `affiliate_link` en la tabla `efficiency_benchmarks` almacena el link de afiliado por tipo de equipo. El botón "Comprar" en `/replacements` ya lo usa:

```php
// EfficiencyBenchmark model
'affiliate_link' => nullable string  // Link de afiliado de MeLi
```

```blade
{{-- replacements/index.blade.php --}}
<x-button href="{{ $op['affiliate_link'] ?? '#' }}" target="_blank">
    Comprar
</x-button>
```

---

## Evolución futura (con API)

Cuando el proyecto esté en producción, se puede integrar la **API de MeLi** para:
- Traer precios reales y actualizados automáticamente
- Mostrar fotos del producto recomendado
- Generar links de afiliado dinámicamente por búsqueda

El campo `meli_search_term` de cada benchmark ya está preparado para esto.

Ver: `docs/implementacionesfuturas.md` → "Integración Mercado Libre API"
