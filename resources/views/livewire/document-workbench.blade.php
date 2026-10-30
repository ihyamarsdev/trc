<div>
    {{ $this->form }}

    <div class="flex flex-wrap items-center gap-3" style="margin-top: 1rem">
        @if (($data['document'] ?? 'spk') !== 'spk')
            <x-filament::button wire:click="save" color="gray" icon="heroicon-m-check" wire:loading.attr="disabled" wire:target="save,download">
                Simpan
            </x-filament::button>
        @endif

        <x-filament::button wire:click="download" icon="heroicon-m-arrow-down-tray" wire:loading.attr="disabled" wire:target="save,download">
            Unduh PDF
        </x-filament::button>
    </div>
</div>
