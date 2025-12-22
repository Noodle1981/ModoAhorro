<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach($links as $link)
        @php
            $colors = [
                'emerald' => 'from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700',
                'blue' => 'from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700',
                'purple' => 'from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700',
                'amber' => 'from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700',
            ];
            $gradient = $colors[$link['color']] ?? $colors['emerald'];
        @endphp
        
        <a href="{{ route($link['route']) }}" 
           class="group relative overflow-hidden rounded-xl bg-gradient-to-br {{ $gradient }} p-6 text-white shadow-lg transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
            
            {{-- Background Pattern --}}
            <div class="absolute inset-0 opacity-10">
                <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full bg-white"></div>
                <div class="absolute -right-2 bottom-0 w-16 h-16 rounded-full bg-white"></div>
            </div>
            
            {{-- Content --}}
            <div class="relative">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i class="{{ $link['icon'] }} text-2xl"></i>
                </div>
                <h3 class="font-semibold text-lg mb-1">{{ $link['label'] }}</h3>
                <p class="text-sm text-white/80">{{ $link['description'] }}</p>
            </div>
            
            {{-- Arrow --}}
            <div class="absolute right-4 bottom-4 opacity-0 group-hover:opacity-100 transition-opacity">
                <i class="bi bi-arrow-right text-xl"></i>
            </div>
        </a>
    @endforeach
</div>
