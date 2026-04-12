@props(['href', 'icon', 'active' => false])

<a href="{{ $href }}" 
   class="group flex items-center gap-3 px-4 py-3 rounded-2xl transition-all duration-300 {{ $active ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-gray-500 hover:bg-gray-50 hover:text-indigo-600' }}">
    <div class="flex items-center justify-center {{ $active ? 'text-white' : 'text-gray-400 group-hover:text-indigo-600' }}">
        <i class="bi {{ $icon }} text-lg"></i>
    </div>
    <span class="text-xs font-black uppercase tracking-tight">{{ $slot }}</span>
    
    @if($active)
        <div class="ml-auto w-1.5 h-1.5 rounded-full bg-white shadow-sm ring-4 ring-white/20"></div>
    @endif
</a>
