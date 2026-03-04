<div class="{{ request()->routeIs('filament.user.auth.*') ? '' : 'flex items-center gap-6 px-6 py-20' }}">
    <img 
        src="{{ asset('images/logo.png') }}" 
        alt="TRC Logo" 
        class="{{ request()->routeIs('filament.user.auth.*') ? 'h-10' : 'h-30' }} w-auto object-contain"
    >
</div>
