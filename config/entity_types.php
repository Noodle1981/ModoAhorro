<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Entity Types Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración centralizada para cada tipo de entidad.
    | Permite personalizar iconos, labels, features y comportamiento
    | sin duplicar código en controllers o vistas.
    |
    */

    'hogar' => [
        'label' => 'Hogar',
        'label_plural' => 'Hogares',
        'icon' => 'bi-house-heart',
        'icon_secondary' => 'bi-house-door',
        'color' => 'primary',
        'tailwind_gradient' => 'from-emerald-500 to-emerald-600',
        'tailwind_bg' => 'bg-emerald-100',
        'tailwind_text' => 'text-emerald-600',
        'route_prefix' => 'entities.home',
        
        // Labels específicos
        'rooms_label' => 'Áreas',
        'rooms_icon' => 'bi-door-open',
        'people_label' => 'Personas',
        'people_icon' => 'bi-people',
        
        // Comportamiento
        'has_business_hours' => false,
        'default_rooms' => ['Portátiles', 'Temporales'],
        
        // Módulos de recomendaciones habilitados
        'recommendations' => [
            'solar_panels' => [
                'enabled' => true,
                'label' => 'Paneles Solares',
                'icon' => 'bi-sun',
                'color' => 'warning',
                'description' => 'Calcula el potencial de energía solar para tu hogar y solicita un presupuesto.',
            ],
            'solar_water_heater' => [
                'enabled' => true,
                'label' => 'Calefones Solares',
                'icon' => 'bi-droplet-half',
                'color' => 'danger',
                'description' => 'Ahorra gas o electricidad calentando agua con energía solar.',
            ],
            'replacements' => [
                'enabled' => true,
                'label' => 'Reemplazos',
                'icon' => 'bi-arrow-repeat',
                'color' => 'primary',
                'description' => 'Descubre qué equipos conviene renovar por eficiencia energética.',
            ],
            'standby_analysis' => [
                'enabled' => true,
                'label' => 'Consumo Fantasma',
                'icon' => 'bi-power',
                'color' => 'secondary',
                'description' => 'Detecta y reduce el consumo de equipos en modo espera (Stand By).',
            ],
            'maintenance' => [
                'enabled' => true,
                'label' => 'Mantenimiento',
                'icon' => 'bi-tools',
                'color' => 'info',
                'description' => 'Gestiona el mantenimiento de tus aires, lavarropas y heladeras.',
            ],
            'vacation' => [
                'enabled' => true,
                'label' => 'Vacaciones',
                'icon' => 'bi-airplane',
                'color' => 'success',
                'description' => 'Recomendaciones para ahorrar energía cuando no estás en casa.',
            ],
            'thermal' => [
                'enabled' => true,
                'label' => 'Salud Térmica',
                'icon' => 'bi-thermometer-half',
                'color' => 'danger',
                'description' => 'Diagnostica la aislación de tu hogar y recibe recomendaciones.',
            ],
            'smart_meter' => [
                'enabled' => true,
                'label' => 'Medidor Inteligente',
                'icon' => 'bi-speedometer2',
                'color' => 'primary',
                'description' => 'Conoce los beneficios de la medición inteligente y solicítalo.',
            ],
            'grid_optimization' => [
                'enabled' => false,
            ],
            'dynamic_pricing' => [
                'enabled' => false,
            ],
        ],
    ],

    'oficina' => [
        'label' => 'Oficina',
        'label_plural' => 'Oficinas',
        'icon' => 'bi-building',
        'icon_secondary' => 'bi-building-gear',
        'color' => 'info',
        'tailwind_gradient' => 'from-blue-500 to-blue-600',
        'tailwind_bg' => 'bg-blue-100',
        'tailwind_text' => 'text-blue-600',
        'route_prefix' => 'entities.office',
        
        // Labels específicos
        'rooms_label' => 'Áreas',
        'rooms_icon' => 'bi-grid',
        'people_label' => 'Empleados',
        'people_icon' => 'bi-person-badge',
        
        // Comportamiento
        'has_business_hours' => true,
        'default_rooms' => ['Recepción', 'Área de trabajo', 'Portátiles', 'Temporales'],
        
        // Módulos de recomendaciones habilitados
        'recommendations' => [
            'solar_panels' => [
                'enabled' => true,
                'label' => 'Paneles Solares',
                'icon' => 'bi-sun',
                'color' => 'warning',
                'description' => 'Calcula el potencial de energía solar para tu oficina.',
            ],
            'solar_water_heater' => [
                'enabled' => false,
            ],
            'replacements' => [
                'enabled' => true,
                'label' => 'Reemplazos',
                'icon' => 'bi-arrow-repeat',
                'color' => 'primary',
                'description' => 'Identifica equipos de oficina que conviene renovar.',
            ],
            'standby_analysis' => [
                'enabled' => true,
                'label' => 'Consumo Fantasma',
                'icon' => 'bi-power',
                'color' => 'secondary',
                'description' => 'Reduce el consumo de PCs, monitores e impresoras en standby.',
            ],
            'maintenance' => [
                'enabled' => true,
                'label' => 'Mantenimiento',
                'icon' => 'bi-tools',
                'color' => 'info',
                'description' => 'Gestiona el mantenimiento de aires acondicionados.',
            ],
            'vacation' => [
                'enabled' => false,
            ],
            'thermal' => [
                'enabled' => false,
            ],
            'smart_meter' => [
                'enabled' => true,
                'label' => 'Medidor Inteligente',
                'icon' => 'bi-speedometer2',
                'color' => 'primary',
                'description' => 'Monitoreo en tiempo real del consumo de la oficina.',
            ],
            'grid_optimization' => [
                'enabled' => true,
                'label' => 'Optimización Horaria',
                'icon' => 'bi-clock-history',
                'color' => 'success',
                'description' => 'Optimiza el uso de equipos según tarifas horarias.',
            ],
            'dynamic_pricing' => [
                'enabled' => false,
            ],
        ],
    ],

    'comercio' => [
        'label' => 'Comercio',
        'label_plural' => 'Comercios',
        'icon' => 'bi-shop',
        'icon_secondary' => 'bi-shop-window',
        'color' => 'success',
        'tailwind_gradient' => 'from-purple-500 to-purple-600',
        'tailwind_bg' => 'bg-purple-100',
        'tailwind_text' => 'text-purple-600',
        'route_prefix' => 'entities.trade',
        
        // Labels específicos
        'rooms_label' => 'Áreas',
        'rooms_icon' => 'bi-layout-split',
        'people_label' => 'Capacidad',
        'people_icon' => 'bi-person-standing',
        
        // Comportamiento
        'has_business_hours' => true,
        'default_rooms' => ['Salón Principal', 'Depósito', 'Portátiles', 'Temporales'],
        
        // Módulos de recomendaciones habilitados
        'recommendations' => [
            'solar_panels' => [
                'enabled' => true,
                'label' => 'Paneles Solares',
                'icon' => 'bi-sun',
                'color' => 'warning',
                'description' => 'Reduce costos operativos con energía solar.',
            ],
            'solar_water_heater' => [
                'enabled' => false,
            ],
            'replacements' => [
                'enabled' => true,
                'label' => 'Reemplazos',
                'icon' => 'bi-arrow-repeat',
                'color' => 'primary',
                'description' => 'Equipos comerciales que conviene renovar.',
            ],
            'standby_analysis' => [
                'enabled' => true,
                'label' => 'Consumo Fantasma',
                'icon' => 'bi-power',
                'color' => 'secondary',
                'description' => 'Reduce el consumo nocturno de equipos comerciales.',
            ],
            'maintenance' => [
                'enabled' => true,
                'label' => 'Mantenimiento',
                'icon' => 'bi-tools',
                'color' => 'info',
                'description' => 'Gestiona heladeras comerciales y aires.',
            ],
            'vacation' => [
                'enabled' => false,
            ],
            'thermal' => [
                'enabled' => false,
            ],
            'smart_meter' => [
                'enabled' => true,
                'label' => 'Medidor Inteligente',
                'icon' => 'bi-speedometer2',
                'color' => 'primary',
                'description' => 'Control en tiempo real para comercios.',
            ],
            'grid_optimization' => [
                'enabled' => true,
                'label' => 'Optimización Horaria',
                'icon' => 'bi-clock-history',
                'color' => 'success',
                'description' => 'Programa equipos para tarifas económicas.',
            ],
            'dynamic_pricing' => [
                'enabled' => true,
                'label' => 'Tarifas Dinámicas',
                'icon' => 'bi-graph-up-arrow',
                'color' => 'warning',
                'description' => 'Aprovecha las variaciones de precio de la energía.',
            ],
        ],
    ],
];
