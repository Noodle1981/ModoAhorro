@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10" 
     id="smart-meter-container" 
     data-base-load="{{ $baseLoad }}" 
     data-whale-capacity="{{ $whaleCapacity }}">
    
    {{-- High-Tech Header --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-6">
        <div>
            <div class="flex items-center gap-2 text-rose-500 mb-2">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                </span>
                <span class="text-[10px] font-black uppercase tracking-widest">En Tiempo Real • IoT Live</span>
            </div>
            <h1 class="text-4xl font-black text-gray-900 tracking-tighter uppercase">Monitor <span class="text-indigo-600">Inteligente</span></h1>
            <p class="text-gray-500 mt-2 font-medium">Simulación de telemetría para <span class="text-gray-900 font-bold">{{ $entity->name }}</span>.</p>
        </div>
        <div class="shrink-0">
             <x-button variant="secondary" size="sm" href="{{ route('entities.house.show', $entity->id) }}">
                <i class="bi bi-arrow-left mr-2"></i> Volver al Panel
            </x-button>
        </div>
    </div>

    {{-- Digital Indicators Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10">
        {{-- Voltage --}}
        <div class="bg-gray-900 rounded-3xl p-8 border border-gray-800 shadow-2xl relative overflow-hidden group">
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-indigo-500/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
            <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-4">Tensión de Red</p>
            <div class="flex items-baseline gap-2">
                <span class="text-5xl font-black text-indigo-400 tracking-tighter" id="voltage-display">220.0</span>
                <span class="text-xl font-bold text-gray-600 uppercase">Volts</span>
            </div>
            <div class="mt-6 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500" id="voltage-dot"></span>
                <span class="text-[10px] font-black uppercase tracking-widest text-emerald-500" id="voltage-status">Suministro Estable</span>
            </div>
        </div>

        {{-- Power --}}
        <div class="bg-gray-900 rounded-3xl p-8 border border-gray-800 shadow-2xl relative overflow-hidden group">
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-amber-500/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
            <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-4">Potencia Instantánea</p>
            <div class="flex items-baseline gap-2">
                <span class="text-5xl font-black text-amber-400 tracking-tighter" id="watts-display">0</span>
                <span class="text-xl font-bold text-gray-600 uppercase">Watts</span>
            </div>
            <div class="mt-6">
                 <div class="h-1 w-full bg-gray-800 rounded-full overflow-hidden">
                    <div class="h-full bg-amber-500 transition-all duration-300" id="power-progress" style="width: 0%"></div>
                 </div>
            </div>
        </div>

        {{-- Cost --}}
        <div class="bg-gray-900 rounded-3xl p-8 border border-gray-800 shadow-2xl relative overflow-hidden group">
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-emerald-500/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>
            <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-4">Costo Estimado / Hora</p>
            <div class="flex items-baseline gap-2">
                <span class="text-gray-600 font-bold text-2xl tracking-tighter">$</span>
                <span class="text-5xl font-black text-emerald-400 tracking-tighter" id="cost-display">0</span>
            </div>
            <p class="mt-6 text-[10px] font-black text-gray-600 uppercase tracking-widest">Basado en Tarifa Vigente</p>
        </div>
    </div>

    {{-- Chart Section --}}
    <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-xs mb-10 overflow-hidden relative">
        <div class="flex items-center justify-between mb-8">
            <h3 class="text-sm font-black text-gray-900 uppercase tracking-tight">Curva de Carga Instantánea</h3>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-indigo-500"></span>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Consumo (W)</span>
                </div>
            </div>
        </div>
        <div class="h-80 w-full">
            <canvas id="smartMeterChart"></canvas>
        </div>
    </div>

    {{-- Info Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-xs">
            <div class="w-12 h-12 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-600 mb-6">
                <i class="bi bi-shield-check text-xl"></i>
            </div>
            <h4 class="text-sm font-black text-gray-900 uppercase tracking-tight mb-2">Protección de Equipos</h4>
            <p class="text-xs text-gray-500 leading-relaxed">El medidor detecta caídas de tensión por debajo de 190V y actúa como protector integral de todos tus electrodomésticos.</p>
        </div>

        <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-xs">
            <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 mb-6">
                <i class="bi bi-phone text-xl"></i>
            </div>
            <h4 class="text-sm font-black text-gray-900 uppercase tracking-tight mb-2">Control de Carga</h4>
            <p class="text-xs text-gray-500 leading-relaxed mb-6">Podés desvincular cargas de forma remota para evitar excesos de potencia contratada.</p>
            <button class="w-full py-4 bg-gray-900 hover:bg-black text-white rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all shadow-xl" id="btn-simulate-cut">
                Simular Corte Remoto
            </button>
        </div>

        <div class="bg-linear-to-br from-indigo-600 to-indigo-800 rounded-3xl p-8 text-white shadow-xl shadow-indigo-100 relative overflow-hidden">
            <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
            <h4 class="text-lg font-black uppercase tracking-tighter mb-4">¿Querés este panel real?</h4>
            <p class="text-sm text-white/80 mb-8 leading-relaxed font-medium">Instalá nuestro medidor inteligente certificado por solo <span class="text-white font-black">U$D 150</span>.</p>
            <button class="w-full py-4 bg-white text-indigo-600 font-black uppercase tracking-widest rounded-2xl shadow-lg hover:scale-[1.02] transition-all">
                Solicitar Instalación
            </button>
        </div>
    </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('smart-meter-container');
    const USER_TARIFF_PRICE = 150; // $/kWh
    const BASE_LOAD = parseFloat(container.dataset.baseLoad);
    const WHALE_CAPACITY = parseFloat(container.dataset.whaleCapacity);

    class SmartMeterSimulator {
        constructor(baseLoad, whaleCapacity) {
            this.baseLoad = baseLoad; 
            this.whaleCapacity = whaleCapacity;
            this.currentLoad = baseLoad;
            this.voltage = 220;
            this.isCut = false;
        }

        tick() {
            if (this.isCut) {
                return {
                    timestamp: new Date(),
                    watts: 0,
                    voltage: 0,
                    amperes: 0,
                    cost_per_hour: 0
                };
            }

            const noiseV = (Math.random() - 0.5) * 4; 
            this.voltage = 220 + noiseV;
            const change = (Math.random() - 0.5) * 200; 
            
            let potentialLoad = this.currentLoad + change;
            
            if (potentialLoad < this.baseLoad) potentialLoad = this.baseLoad;
            if (potentialLoad > (this.baseLoad + this.whaleCapacity)) potentialLoad = this.baseLoad + this.whaleCapacity;

            if (Math.random() > 0.95) potentialLoad += 1000;
            if (Math.random() > 0.95 && potentialLoad > this.baseLoad + 1000) potentialLoad -= 1000; 

            this.currentLoad = potentialLoad;
            const pf = (this.currentLoad > 1000) ? 0.85 : 0.95;
            const amperes = this.currentLoad / (this.voltage * pf);

            return {
                timestamp: new Date(),
                watts: Math.round(this.currentLoad),
                voltage: this.voltage.toFixed(1),
                amperes: amperes.toFixed(2),
                cost_per_hour: (this.currentLoad / 1000) * USER_TARIFF_PRICE
            };
        }
        
        cutPower() {
            this.isCut = !this.isCut;
            return this.isCut;
        }
    }

    const simulator = new SmartMeterSimulator(BASE_LOAD, WHALE_CAPACITY);
    const ctx = document.getElementById('smartMeterChart').getContext('2d');
    
    // Create Chart
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Carga instantánea (W)',
                data: [],
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { display: false },
                y: { 
                    beginAtZero: true,
                    grid: { color: '#f3f4f6' },
                    ticks: { 
                        color: '#9ca3af',
                        font: { weight: 'bold', size: 10 }
                    }
                }
            },
            plugins: {
                legend: { display: false }
            },
            animation: { duration: 0 }
        }
    });

    // Update Loop
    setInterval(() => {
        const data = simulator.tick();

        // Update DOM Indicators
        document.getElementById('voltage-display').innerText = data.voltage;
        document.getElementById('watts-display').innerText = data.watts;
        document.getElementById('cost-display').innerText = Math.round(data.cost_per_hour);

        // Progress bar simulation
        const maxCapacity = BASE_LOAD + WHALE_CAPACITY + 1000;
        const powerPercent = (data.watts / maxCapacity) * 100;
        document.getElementById('power-progress').style.width = powerPercent + "%";

        // Voltage Alert
        const vStatus = document.getElementById('voltage-status');
        const vDot = document.getElementById('voltage-dot');
        
        if (data.voltage < 190 && data.voltage > 0) {
            vStatus.innerText = "TENSIÓN CRÍTICA";
            vStatus.className = "text-[10px] font-black uppercase tracking-widest text-rose-500 animate-pulse";
            vDot.className = "w-2 h-2 rounded-full bg-rose-500";
        } else if (data.voltage == 0) {
             vStatus.innerText = "SUMINISTRO INTERRUMPIDO";
             vStatus.className = "text-[10px] font-black uppercase tracking-widest text-gray-500";
             vDot.className = "w-2 h-2 rounded-full bg-gray-500";
        } else {
            vStatus.innerText = "SUMINISTRO ESTABLE";
            vStatus.className = "text-[10px] font-black uppercase tracking-widest text-emerald-500";
            vDot.className = "w-2 h-2 rounded-full bg-emerald-500";
        }

        // Update Chart
        const now = new Date();
        const timeLabel = now.getHours() + ":" + now.getMinutes() + ":" + now.getSeconds();
        
        chart.data.labels.push(timeLabel);
        chart.data.datasets[0].data.push(data.watts);

        if (chart.data.labels.length > 60) {
            chart.data.labels.shift();
            chart.data.datasets[0].data.shift();
        }
        
        // Dynamic Chart Color
        if (data.watts > 2500) {
            chart.data.datasets[0].borderColor = '#f43f5e';
            chart.data.datasets[0].backgroundColor = 'rgba(244, 63, 94, 0.1)';
        } else {
            chart.data.datasets[0].borderColor = '#6366f1';
            chart.data.datasets[0].backgroundColor = 'rgba(99, 102, 241, 0.1)';
        }

        chart.update();
    }, 1000);

    // Simulator Controls
    const btnCut = document.getElementById('btn-simulate-cut');
    btnCut.addEventListener('click', () => {
        const isCut = simulator.cutPower();
        if (isCut) {
            btnCut.innerText = "Restaurar Suministro";
            btnCut.className = "w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all shadow-xl";
        } else {
            btnCut.innerText = "Simular Corte Remoto";
            btnCut.className = "w-full py-4 bg-gray-900 hover:bg-black text-white rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all shadow-xl";
        }
    });
});
</script>

<style>
    @font-face {
        font-family: 'Digital';
        src: url('https://fonts.cdnfonts.com/s/14101/DIGITALDREAM.woff') format('woff');
    }
    .digital-font {
        font-family: 'Digital', monospace;
    }
</style>
@endsection
