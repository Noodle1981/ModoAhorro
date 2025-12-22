@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-6xl">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">🎨 UI Kit - Componentes ModoAhorro</h1>

    <!-- Buttons -->
    <section class="mb-12">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b">Buttons</h2>
        <div class="flex flex-wrap gap-3">
            <x-button variant="primary">Primary</x-button>
            <x-button variant="secondary">Secondary</x-button>
            <x-button variant="success">Success</x-button>
            <x-button variant="warning">Warning</x-button>
            <x-button variant="danger">Danger</x-button>
            <x-button variant="outline">Outline</x-button>
            <x-button variant="ghost">Ghost</x-button>
        </div>
        <div class="flex flex-wrap gap-3 mt-4">
            <x-button size="xs">Extra Small</x-button>
            <x-button size="sm">Small</x-button>
            <x-button size="md">Medium</x-button>
            <x-button size="lg">Large</x-button>
            <x-button size="xl">Extra Large</x-button>
        </div>
    </section>

    <!-- Cards -->
    <section class="mb-12">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b">Cards</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-card>
                <h3 class="font-semibold text-gray-900 mb-2">Card Simple</h3>
                <p class="text-gray-600 text-sm">Contenido de la card básica.</p>
            </x-card>

            <x-card hover>
                <x-slot:header>
                    <h3 class="font-semibold text-gray-900">Card con Header</h3>
                </x-slot:header>
                <p class="text-gray-600 text-sm">Esta card tiene header y efecto hover.</p>
            </x-card>

            <x-card>
                <x-slot:header>
                    <h3 class="font-semibold text-gray-900">Card Completa</h3>
                </x-slot:header>
                <p class="text-gray-600 text-sm">Card con header y footer.</p>
                <x-slot:footer>
                    <x-button size="sm">Acción</x-button>
                </x-slot:footer>
            </x-card>
        </div>
    </section>

    <!-- Stat Cards -->
    <section class="mb-12">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b">Stat Cards</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <x-stat-card title="Consumo Total" value="1,234 kWh" icon="bi-lightning-charge" color="emerald" trend="+12%" :trendUp="true" />
            <x-stat-card title="Ahorro Mensual" value="$15,500" icon="bi-piggy-bank" color="blue" trend="-5%" :trendUp="false" />
            <x-stat-card title="Entidades" value="8" icon="bi-house" color="purple" subtitle="3 hogares, 5 comercios" />
            <x-stat-card title="Alertas" value="2" icon="bi-exclamation-triangle" color="amber" />
        </div>
    </section>

    <!-- Alerts -->
    <section class="mb-12">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b">Alerts</h2>
        <div class="space-y-4">
            <x-alert type="info" title="Información">Este es un mensaje informativo.</x-alert>
            <x-alert type="success" title="Éxito">La operación se completó correctamente.</x-alert>
            <x-alert type="warning" title="Advertencia">Hay algo que requiere tu atención.</x-alert>
            <x-alert type="danger" title="Error" dismissible>Ocurrió un error. Este es dismissible.</x-alert>
        </div>
    </section>

    <!-- Badges -->
    <section class="mb-12">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b">Badges</h2>
        <div class="flex flex-wrap gap-3">
            <x-badge>Default</x-badge>
            <x-badge variant="primary">Primary</x-badge>
            <x-badge variant="success">Success</x-badge>
            <x-badge variant="warning">Warning</x-badge>
            <x-badge variant="danger">Danger</x-badge>
            <x-badge variant="info">Info</x-badge>
            <x-badge variant="purple">Purple</x-badge>
        </div>
        <div class="flex flex-wrap gap-3 mt-4">
            <x-badge variant="success" dot>Con Dot</x-badge>
            <x-badge variant="danger" dot>Activo</x-badge>
            <x-badge size="lg" variant="primary">Large Badge</x-badge>
        </div>
    </section>

    <!-- Inputs -->
    <section class="mb-12">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b">Form Inputs</h2>
        <x-card>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-input name="demo_name" label="Nombre" placeholder="Ingresá tu nombre" required />
                <x-input name="demo_email" label="Email" type="email" placeholder="email@ejemplo.com" helper="Usaremos tu email para notificaciones." />
                <x-select name="demo_select" label="Tipo de Entidad" :options="['hogar' => 'Hogar', 'oficina' => 'Oficina', 'comercio' => 'Comercio']" required />
                <x-input name="demo_error" label="Campo con Error" error="Este campo tiene un error de validación." />
            </div>
            <div class="mt-6">
                <x-textarea name="demo_notes" label="Notas" placeholder="Escribe tus notas aquí..." rows="3" />
            </div>
        </x-card>
    </section>

    <!-- Table -->
    <section class="mb-12">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b">Table</h2>
        <x-table striped>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3">Entidad</th>
                    <th class="px-4 py-3">Tipo</th>
                    <th class="px-4 py-3">Consumo</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3">Acciones</th>
                </tr>
            </x-slot:head>
            <tr>
                <td class="px-4 py-3 font-medium">Mi Casa</td>
                <td class="px-4 py-3"><x-badge variant="primary">Hogar</x-badge></td>
                <td class="px-4 py-3">450 kWh</td>
                <td class="px-4 py-3"><x-badge variant="success" dot>Activo</x-badge></td>
                <td class="px-4 py-3">
                    <x-button size="xs" variant="ghost">Ver</x-button>
                    <x-button size="xs" variant="ghost">Editar</x-button>
                </td>
            </tr>
            <tr>
                <td class="px-4 py-3 font-medium">Oficina Central</td>
                <td class="px-4 py-3"><x-badge variant="info">Oficina</x-badge></td>
                <td class="px-4 py-3">1,200 kWh</td>
                <td class="px-4 py-3"><x-badge variant="warning" dot>Revisar</x-badge></td>
                <td class="px-4 py-3">
                    <x-button size="xs" variant="ghost">Ver</x-button>
                    <x-button size="xs" variant="ghost">Editar</x-button>
                </td>
            </tr>
        </x-table>
    </section>

    <!-- Modal -->
    <section class="mb-12">
        <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b">Modal</h2>
        <x-modal name="demo-modal" title="Modal de Ejemplo">
            <x-slot:trigger>
                <x-button>Abrir Modal</x-button>
            </x-slot:trigger>

            <p class="text-gray-600">Este es el contenido del modal. Podés agregar cualquier contenido aquí.</p>
            <div class="mt-4">
                <x-input name="modal_input" label="Campo en Modal" placeholder="Escribe algo..." />
            </div>

            <x-slot:footer>
                <x-button variant="secondary" @click="open = false">Cancelar</x-button>
                <x-button>Confirmar</x-button>
            </x-slot:footer>
        </x-modal>
    </section>

</div>
@endsection
