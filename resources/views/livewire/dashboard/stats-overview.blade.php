<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <x-stat-card 
        title="Entidades" 
        :value="$stats['entities']" 
        icon="bi-building" 
        color="purple"
        subtitle="Hogares, oficinas, comercios"
    />
    
    <x-stat-card 
        title="Consumo Total" 
        :value="$stats['consumption'] . ' kWh'" 
        icon="bi-lightning-charge" 
        color="emerald"
    />
    
    <x-stat-card 
        title="Gasto Acumulado" 
        :value="'$' . $stats['cost']" 
        icon="bi-currency-dollar" 
        color="blue"
    />
    
    <x-stat-card 
        title="Equipos" 
        :value="$stats['equipment']" 
        icon="bi-plug" 
        color="amber"
        subtitle="Registrados en tus entidades"
    />
</div>
