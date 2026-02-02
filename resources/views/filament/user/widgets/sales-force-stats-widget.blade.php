@php
    $chartData = $this->getChartData();
    $chartId = 'pie-chart-' . md5(json_encode($chartData));
    $hasActiveFilters = $this->education_level || $this->years;
@endphp

<x-filament-widgets::widget wire:poll.10s>
    <x-filament::section>
        {{-- Header with Title and Filter --}}
        <div class="flex items-center justify-between gap-4 mb-4">
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
                Rekap Program
            </h3>

            {{-- Filter Dropdown - Filament Style --}}
            <div x-data="{ open: false }" class="relative">
                <x-filament::icon-button icon="heroicon-m-funnel" @click="open = !open" :badge="$hasActiveFilters ? (($this->education_level ? 1 : 0) + ($this->years ? 1 : 0)) : null" badge-color="danger"
                    :color="$hasActiveFilters ? 'primary' : 'gray'" label="Filter" />

                {{-- Dropdown Panel - Filament Style --}}
                <div x-show="open" @click.outside="open = false" x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="fi-dropdown-panel absolute z-10 mt-2 rounded-xl bg-white shadow-lg ring-1 ring-gray-950/5 transition dark:bg-gray-900 dark:ring-white/10"
                    style="display: none; right: 0; width: 20rem;">

                    <div class="fi-ta-filters grid gap-y-4 p-6">
                        {{-- Header --}}
                        <div class="flex items-center justify-between">
                            <h4 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
                                Filter
                            </h4>
                            @if($hasActiveFilters)
                                <x-filament::link wire:click="resetFilters" color="danger" tag="button" size="sm">
                                    Atur ulang filter
                                </x-filament::link>
                            @endif
                        </div>

                        {{-- Filters Form --}}
                        {{ $this->form }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Active Filters Badges --}}
        @if($hasActiveFilters)
            <div class="flex flex-wrap items-center gap-2 mb-4 pb-4 border-b border-gray-200 dark:border-white/10">
                @if($this->education_level)
                    <x-filament::badge color="primary" class="gap-x-1">
                        Jenjang: {{ $this->education_level }}
                        <button type="button" wire:click="$set('education_level', null)"
                            class="ml-1 -mr-1 hover:text-primary-300">
                            <x-heroicon-m-x-mark class="h-3 w-3" />
                        </button>
                    </x-filament::badge>
                @endif
                @if($this->years)
                    <x-filament::badge color="primary" class="gap-x-1">
                        Tahun: {{ $this->years }}
                        <button type="button" wire:click="$set('years', null)" class="ml-1 -mr-1 hover:text-primary-300">
                            <x-heroicon-m-x-mark class="h-3 w-3" />
                        </button>
                    </x-filament::badge>
                @endif
            </div>
        @endif

        <div class="flex flex-col lg:flex-row gap-6" wire:key="chart-container-{{ $chartId }}" wire:ignore.self>
            {{-- Pie Chart --}}
            <div class="w-full lg:w-1/2 flex items-center justify-center">
                <div class="relative" style="width: 320px; height: 320px;">
                    @if(count($chartData['labels']) > 0)
                        <div wire:ignore>
                            <canvas id="{{ $chartId }}" width="320" height="320" x-data="{
                                        chart: null,
                                        init() {
                                            this.loadChart();
                                        },
                                        loadChart() {
                                            if (typeof Chart === 'undefined') {
                                                const script = document.createElement('script');
                                                script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js';
                                                script.onload = () => this.createChart();
                                                document.head.appendChild(script);
                                            } else {
                                                this.createChart();
                                            }
                                        },
                                        createChart() {
                                            const ctx = document.getElementById('{{ $chartId }}');
                                            if (!ctx) return;
                                            if (this.chart) {
                                                this.chart.destroy();
                                            }
                                            this.chart = new Chart(ctx, {
                                                type: 'doughnut',
                                                data: {
                                                    labels: {{ Js::from($chartData['labels']) }},
                                                    datasets: {{ Js::from($chartData['datasets']) }}
                                                },
                                                options: {
                                                    responsive: false,
                                                    maintainAspectRatio: false,
                                                    cutout: '55%',
                                                    plugins: {
                                                        legend: {
                                                            display: false
                                                        },
                                                        tooltip: {
                                                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                                            titleColor: '#fff',
                                                            bodyColor: '#fff',
                                                            padding: 12,
                                                            cornerRadius: 8,
                                                            displayColors: true,
                                                            callbacks: {
                                                                label: function(context) {
                                                                    const label = context.label || '';
                                                                    const value = context.parsed || 0;
                                                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                                    const percentage = ((value / total) * 100).toFixed(1);
                                                                    return label + ': ' + value.toLocaleString() + ' siswa (' + percentage + '%)';
                                                                }
                                                            }
                                                        }
                                                    },
                                                    animation: {
                                                        animateScale: true,
                                                        animateRotate: true
                                                    }
                                                }
                                            });
                                        }
                                    }"></canvas>
                        </div>
                    @else
                        <div class="flex items-center justify-center h-64 text-gray-500 dark:text-gray-400">
                            <div class="text-center">
                                <x-heroicon-o-chart-pie class="w-16 h-16 mx-auto mb-2 opacity-50" />
                                <p>Tidak ada data</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Details Table --}}
            <div class="w-full lg:w-1/2">
                <div class="overflow-x-auto rounded-lg">
                    <table class="w-full text-sm dark:bg-gray-800">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-600 dark:bg-gray-700">
                                <th class="py-3 px-4 text-left font-semibold text-gray-900 dark:text-white">Program</th>
                                <th class="py-3 px-4 text-center font-semibold text-gray-900 dark:text-white">Jumlah
                                    Sekolah</th>
                                <th class="py-3 px-4 text-center font-semibold text-gray-900 dark:text-white">Jumlah
                                    Siswa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($chartData['details'] as $detail)
                                <tr
                                    class="border-b border-gray-100 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-3">
                                            <span class="w-3 h-3 rounded-full flex-shrink-0"
                                                style="background-color: {{ $detail['color'] }};"></span>
                                            <span
                                                class="font-medium text-gray-900 dark:text-white">{{ $detail['label'] }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-center text-gray-700 dark:text-white">
                                        {{ number_format($detail['school_count']) }}
                                    </td>
                                    <td class="py-3 px-4 text-center text-gray-700 dark:text-white">
                                        {{ number_format($detail['student_count']) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-gray-500 dark:text-gray-300">
                                        Tidak ada data untuk filter yang dipilih
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($chartData['details']) > 0)
                            <tfoot>
                                <tr class="bg-gray-50 dark:bg-gray-700">
                                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">Total</td>
                                    <td class="py-3 px-4 text-center font-bold text-gray-900 dark:text-white">
                                        {{ number_format($chartData['totals']['schools']) }}
                                    </td>
                                    <td class="py-3 px-4 text-center font-bold text-gray-900 dark:text-white">
                                        {{ number_format($chartData['totals']['students']) }}
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>