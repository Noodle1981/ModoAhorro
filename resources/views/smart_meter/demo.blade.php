@extends('layouts.app')

@section('content')
<div class="container-fluid bg-dark text-white p-4" style="min-height: 100vh;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-info"><i class="bi bi-speedometer"></i> Medidor Inteligente <span class="badge bg-danger">LIVE</span></h1>
            <p class="text-muted">Simulación IoT en Tiempo Real</p>
        </div>
        <a href="{{ route('entities.show', $entity->id) }}" class="btn btn-outline-light">Volver al Dashboard</a>
    </div>

    <!-- Digital Indicators -->
    <div class="row mb-4 text-center">
        <div class="col-md-4">
            <div class="card bg-secondary text-white border-info mb-3">
                <div class="card-body">
                    <h5 class="card-title text-info">VOLTAJE</h5>
                    <h2 class="display-4" id="voltage-display">220.0 V</h2>
                    <small id="voltage-status" class="text-success">ESTABLE</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-secondary text-white border-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title text-warning">POTENCIA</h5>
                    <h2 class="display-4" id="watts-display">0 W</h2>
                    <small>CONSUMO INSTANTÁNEO</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-secondary text-white border-success mb-3">
                <div class="card-body">
                    <h5 class="card-title text-success">COSTO / HORA</h5>
                    <h2 class="display-4" id="cost-display">$0</h2>
                    <small>PROYECCIÓN ACTUAL</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Real-time Chart -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-dark border-secondary">
                <div class="card-body">
                    <canvas id="smartMeterChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales / Action Cards -->
    <div class="row">
        <div class="col-md-4">
            <div class="card bg-dark border-danger h-100">
                <div class="card-body text-white">
                    <h5 class="card-title"><i class="bi bi-shield-check"></i> Protección de Equipos</h5>
                    <p class="card-text">Un medidor real cortaría la luz automáticamente si el voltaje baja de 190V para salvar tu heladera.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-dark border-primary h-100">
                <div class="card-body text-white">
                    <h5 class="card-title"><i class="bi bi-phone"></i> Control Remoto</h5>
                    <p class="card-text">Corta la luz de tu casa desde el celular si te fuiste de vacaciones.</p>
                    <button class="btn btn-outline-danger w-100 mt-2" onclick="simulateCut()">Simular Corte Remoto</button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary text-white h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">¿Te interesa tener esto real?</h5>
                    <p class="card-text">Instalación de Medidor Inteligente Certificado.</p>
                    <h3 class="mb-3">U$D 150</h3>
                    <button class="btn btn-light w-100 fw-bold">Solicitar Instalación</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const USER_TARIFF_PRICE = 150; // $/kWh
    const BASE_LOAD = {{ $baseLoad }};
    const WHALE_CAPACITY = {{ $whaleCapacity }};

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

            // 1. Simular Voltaje (Fluctuación normal)
            const noiseV = (Math.random() - 0.5) * 4; 
            this.voltage = 220 + noiseV;

            // 2. Simular Comportamiento (Brownian Noise)
            const change = (Math.random() - 0.5) * 200; 
            
            let potentialLoad = this.currentLoad + change;
            
            if (potentialLoad < this.baseLoad) potentialLoad = this.baseLoad;
            if (potentialLoad > (this.baseLoad + this.whaleCapacity)) potentialLoad = this.baseLoad + this.whaleCapacity;

            // Random "Whale" event (Spike)
            if (Math.random() > 0.95) {
                 potentialLoad += 1000; // Microwave or AC kick
            }
             // Random "Whale" off
            if (Math.random() > 0.95 && potentialLoad > this.baseLoad + 1000) {
                 potentialLoad -= 1000; 
            }

            this.currentLoad = potentialLoad;

            // 3. Power Factor
            const pf = (this.currentLoad > 1000) ? 0.85 : 0.95;

            // 4. Amperes
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

    // Chart Setup
    const ctx = document.getElementById('smartMeterChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Potencia (Watts)',
                data: [],
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.2)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: { display: false },
                y: { 
                    beginAtZero: true,
                    grid: { color: '#444' },
                    ticks: { color: '#fff' }
                }
            },
            plugins: {
                legend: { labels: { color: '#fff' } }
            },
            animation: { duration: 0 }
        }
    });

    // Update Loop
    setInterval(() => {
        const data = simulator.tick();

        // Update DOM
        document.getElementById('voltage-display').innerText = data.voltage + " V";
        document.getElementById('watts-display').innerText = data.watts + " W";
        document.getElementById('cost-display').innerText = "$" + Math.round(data.cost_per_hour);

        // Voltage Alert
        const vStatus = document.getElementById('voltage-status');
        if (data.voltage < 190 && data.voltage > 0) {
            vStatus.innerText = "BAJA TENSIÓN - PELIGRO";
            vStatus.className = "text-danger blink";
        } else if (data.voltage == 0) {
             vStatus.innerText = "CORTE DE ENERGÍA";
             vStatus.className = "text-danger";
        } else {
            vStatus.innerText = "ESTABLE";
            vStatus.className = "text-success";
        }

        // Update Chart
        const now = new Date();
        const timeLabel = now.getHours() + ":" + now.getMinutes() + ":" + now.getSeconds();
        
        chart.data.labels.push(timeLabel);
        chart.data.datasets[0].data.push(data.watts);

        // Keep last 60 seconds
        if (chart.data.labels.length > 60) {
            chart.data.labels.shift();
            chart.data.datasets[0].data.shift();
        }
        
        // Dynamic Color
        if (data.watts > 2000) {
            chart.data.datasets[0].borderColor = '#dc3545'; // Red
        } else if (data.watts > 1000) {
            chart.data.datasets[0].borderColor = '#fd7e14'; // Orange
        } else {
            chart.data.datasets[0].borderColor = '#198754'; // Green
        }

        chart.update();

    }, 1000);

    function simulateCut() {
        const isCut = simulator.cutPower();
        const btn = document.querySelector('button[onclick="simulateCut()"]');
        if (isCut) {
            btn.innerText = "Restaurar Energía";
            btn.classList.remove('btn-outline-danger');
            btn.classList.add('btn-success');
        } else {
            btn.innerText = "Simular Corte Remoto";
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-danger');
        }
    }
</script>

<style>
    .blink {
        animation: blinker 1s linear infinite;
    }
    @keyframes blinker {
        50% { opacity: 0; }
    }
</style>
@endsection
