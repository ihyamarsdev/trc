<x-filament-widgets::widget>
    <div
        x-data="{
            userMap: @js($this->getUserRegistrationMap()),
        }"
        x-on:click="
            let cell = $event.target.closest('.fi-ta-group-header-cell, .fi-ta-cell');
            if (!cell) return;

            {{-- Cek apakah ini kolom pertama (nama sales) --}}
            let row = cell.closest('tr');
            if (!row) return;
            let cells = [...row.querySelectorAll('td, th')];
            if (cells.indexOf(cell) > 0) return;

            let name = cell.textContent.trim();
            {{-- Hapus prefix label jika ada --}}
            if (name.startsWith('Sales:')) name = name.substring(6).trim();

            let regId = userMap[name];
            if (regId) {
                $wire.openSalesSchools(regId);
            }
        "
    >
        <style>
            /* Buat nama sales di kolom pertama terlihat bisa diklik */
            .fi-ta-group-header-cell:first-child,
            tr td:first-child .fi-ta-text {
                cursor: pointer;
            }
            .fi-ta-group-header-cell:first-child:hover,
            tr td:first-child .fi-ta-text:hover {
                text-decoration: underline;
                color: #0284c7;
            }
            :is(.dark) .fi-ta-group-header-cell:first-child:hover,
            :is(.dark) tr td:first-child .fi-ta-text:hover {
                color: #38bdf8;
            }
        </style>

        {{ $this->table }}
    </div>

    <x-filament::modal id="sales-schools-modal" width="5xl">
        <x-slot name="heading">
            Sekolah yang ditangani oleh {{ $this->selectedUserId ? (\App\Models\User::find($this->selectedUserId)?->name ?? 'Sales') : 'Sales' }}
        </x-slot>

        @if($this->selectedUserId)
            @php
                $schools = \App\Models\RegistrationData::query()
                    ->where('users_id', $this->selectedUserId)
                    ->with(['status'])
                    ->get();
            @endphp
            @include('filament.user.widgets.sales-schools-modal', ['schools' => $schools])
        @else
            <div class="py-6 text-center text-sm text-gray-500">
                Memuat data...
            </div>
        @endif
    </x-filament::modal>
</x-filament-widgets::widget>
