<div class="{{ request()->routeIs('filament.user.auth.*') ? '' : 'flex items-center gap-6 px-6 py-20' }}">
    <img
        src="{{ asset('images/logo.png') }}"
        alt="TRC Logo"
        class="{{ request()->routeIs('filament.user.auth.*') ? '' : 'h-30' }} w-auto object-contain"
        @if(request()->routeIs('filament.user.auth.*')) style="max-height: 5rem;" @endif
    >
</div>
