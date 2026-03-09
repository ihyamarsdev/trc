@php
    $chartData = $this->getChartData();
    $lineData = $this->getLineChartData();
    $lineChartId = 'line-chart-' . md5(json_encode([$lineData, $this->period]));
    $hasActiveFilters = $this->education_level || $this->years;
    $totalStudents = $chartData['totals']['students'] ?? 0;
    $totalPrograms = $chartData['totals']['programs'] ?? 0;
    $period = $this->period;
@endphp

<x-filament-widgets::widget wire:poll.10s>
    <x-filament::section class="overflow-visible">
        {{-- ========================================
            HEADER: Title & Filter
            ======================================== --}}
        <div class="flex items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-xl shadow-lg bg-gradient-to-br from-gray-600 to-gray-500 dark:from-[#0096d2] dark:to-[#0082a0]">
                    <x-heroicon-m-presentation-chart-line class="w-5 h-5 text-white" />
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-[#f0f9ff]">Rekap Program</h3>
                    <p class="text-xs text-gray-600 dark:text-[#7dd3fc]">Tren penambahan data registrasi</p>
                </div>
            </div>

            {{-- Filter Dropdown --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                    class="relative inline-flex items-center justify-center gap-1.5 rounded-xl py-2 px-4 text-sm font-medium transition-all duration-200 outline-none focus:-translate-y-0.5 active:translate-y-0 disabled:pointer-events-none disabled:opacity-50"
                    :class="$hasActiveFilters ? 'bg-gradient-to-r from-gray-600 to-gray-500 dark:from-[#0096d2] dark:to-[#0082a0] text-white shadow-lg' : 'bg-gray-100 dark:bg-[#002a35]/80 text-gray-700 dark:text-[#bae6fd] border border-gray-300 dark:border-[#0096d2]/30 hover:border-gray-400 dark:hover:border-[#0096d2] hover:bg-gray-200 dark:hover:bg-[#0096d2]/20'">

                    <x-heroicon-m-funnel class="w-4 h-4" />
                    <span>Filter</span>

                    @if($hasActiveFilters)
                        <span class="absolute -top-1.5 -right-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-amber-600 dark:bg-[#a0c80a] text-white dark:text-[#001a22] text-xs font-bold">
                            {{ ($this->education_level ? 1 : 0) + ($this->years ? 1 : 0) }}
                        </span>
                    @endif
                </button>

                {{-- Dropdown Panel --}}
                <div x-show="open" @click.outside="open = false"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                    class="absolute z-50 mt-2 right-0 w-80 rounded-2xl shadow-2xl bg-white dark:bg-gradient-to-br dark:from-[#002a35] dark:to-[#001a22] border border-gray-200 dark:border-[#0096d2]/30 backdrop-blur-xl"
                    style="display: none;">

                    <div class="p-6">
                        {{-- Header --}}
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-base font-bold text-gray-900 dark:text-[#f0f9ff]">Filter Data</h4>
                            @if($hasActiveFilters)
                                <button wire:click="resetFilters" class="text-sm text-lime-600 dark:text-[#a0c80a] hover:text-lime-700 dark:hover:text-[#b4d81a] transition-colors font-medium">
                                    Reset Semua
                                </button>
                            @endif
                        </div>

                        {{-- Filters Form --}}
                        {{ $this->form }}
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================
            SUMMARY CARDS
            ======================================== --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {{-- Total Programs --}}
            <div class="relative overflow-hidden rounded-2xl p-4 transition-all duration-300 hover:scale-[1.02] hover:shadow-xl bg-gradient-to-br from-gray-50 to-gray-100 dark:from-[rgba(0,150,210,0.15)] dark:to-[rgba(0,130,160,0.1)] border border-gray-200 dark:border-[#0096d2]/25">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider mb-1 text-gray-600 dark:text-[#7dd3fc]">Program</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-[#f0f9ff]">{{ number_format($totalPrograms) }}</p>
                    </div>
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gray-200 dark:bg-[rgba(0,150,210,0.2)]">
                        <x-heroicon-m-academic-cap class="w-5 h-5 text-gray-600 dark:text-[#0096d2]" />
                    </div>
                </div>
            </div>

            {{-- Total Schools --}}
            <div class="relative overflow-hidden rounded-2xl p-4 transition-all duration-300 hover:scale-[1.02] hover:shadow-xl bg-gradient-to-br from-gray-50 to-gray-100 dark:from-[rgba(160,200,10,0.15)] dark:to-[rgba(140,180,40,0.1)] border border-gray-200 dark:border-[#a0c80a]/25">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider mb-1 text-gray-600 dark:text-[#b4d81a]">Sekolah</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-[#f0f9ff]">{{ number_format($chartData['totals']['schools'] ?? 0) }}</p>
                    </div>
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gray-200 dark:bg-[rgba(160,200,10,0.2)]">
                        <x-heroicon-m-building-office-2 class="w-5 h-5 text-gray-600 dark:text-[#a0c80a]" />
                    </div>
                </div>
            </div>

            {{-- Total Students --}}
            <div class="relative overflow-hidden rounded-2xl p-4 transition-all duration-300 hover:scale-[1.02] hover:shadow-xl bg-gradient-to-br from-gray-100 to-gray-150 dark:from-[rgba(0,150,210,0.2)] dark:to-[rgba(0,130,160,0.15)] border border-gray-300 dark:border-[#0096d2]/30">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider mb-1 text-gray-600 dark:text-[#7dd3fc]">Siswa</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-[#f0f9ff]">{{ number_format($totalStudents) }}</p>
                    </div>
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gray-300 dark:bg-[rgba(0,150,210,0.25)]">
                        <x-heroicon-m-users class="w-5 h-5 text-gray-700 dark:text-[#0096d2]" />
                    </div>
                </div>
            </div>

            {{-- Avg per School --}}
            <div class="relative overflow-hidden rounded-2xl p-4 transition-all duration-300 hover:scale-[1.02] hover:shadow-xl bg-gradient-to-br from-gray-100 to-gray-150 dark:from-[rgba(160,200,10,0.2)] dark:to-[rgba(140,180,40,0.15)] border border-gray-300 dark:border-[#a0c80a]/30">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider mb-1 text-gray-600 dark:text-[#b4d81a]">Rata-rata</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-[#f0f9ff]">{{ number_format($chartData['totals']['avg_students_per_school'] ?? 0) }}</p>
                    </div>
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gray-300 dark:bg-[rgba(160,200,10,0.25)]">
                        <x-heroicon-m-chart-bar class="w-5 h-5 text-gray-700 dark:text-[#a0c80a]" />
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================
            ACTIVE FILTERS BADGES
            ======================================== --}}
        @if($hasActiveFilters)
            <div class="flex flex-wrap items-center gap-2 mb-6 pb-4 border-b border-gray-200 dark:border-[#0096d2]/20">
                <span class="text-xs font-medium text-gray-600 dark:text-[#7dd3fc]">Filter Aktif:</span>
                @if($this->education_level)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition-all duration-200 hover:scale-105 bg-gray-100 dark:bg-gradient-to-br dark:from-[rgba(0,150,210,0.25)] dark:to-[rgba(0,130,160,0.2)] border border-gray-300 dark:border-[#0096d2]/40 text-gray-700 dark:text-[#bae6fd]">
                        <x-heroicon-m-academic-cap class="w-3.5 h-3.5" />
                        Jenjang: {{ $this->education_level }}
                        <button type="button" wire:click="$set('education_level', null)" class="ml-1 hover:text-gray-900 dark:hover:text-white transition-colors">
                            <x-heroicon-m-x-mark class="w-3.5 h-3.5" />
                        </button>
                    </span>
                @endif
                @if($this->years)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition-all duration-200 hover:scale-105 bg-gray-100 dark:bg-gradient-to-br dark:from-[rgba(160,200,10,0.25)] dark:to-[rgba(140,180,40,0.2)] border border-gray-300 dark:border-[#a0c80a]/40 text-gray-700 dark:text-[#b4d81a]">
                        <x-heroicon-m-calendar class="w-3.5 h-3.5" />
                        Tahun: {{ $this->years }}
                        <button type="button" wire:click="$set('years', null)" class="ml-1 hover:text-gray-900 dark:hover:text-white transition-colors">
                            <x-heroicon-m-x-mark class="w-3.5 h-3.5" />
                        </button>
                    </span>
                @endif
            </div>
        @endif

        {{-- ========================================
            LINE CHART + PERIOD TOGGLE
            ======================================== --}}
        <div class="mb-6 rounded-2xl overflow-hidden bg-gray-50 dark:bg-gradient-to-br dark:from-[#000a0e] dark:to-[#00141a] border border-gray-200 dark:border-[#0096d2]/20 shadow-xl dark:shadow-2xl dark:shadow-black">

            {{-- Chart Header with Period Toggle --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 border-b border-gray-200 dark:border-[#0096d2]/20">
                <div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-[#f0f9ff]">Tren Pendaftaran Sekolah</h4>
                    <p class="text-xs text-gray-600 dark:text-[#7dd3fc]">Jumlah sekolah terdaftar per periode</p>
                </div>

                {{-- Period Toggle Buttons --}}
                <div class="flex items-center gap-1 p-1 rounded-xl bg-gray-200 dark:bg-[#001a22] border border-gray-300 dark:border-[#0096d2]/20">
                    @foreach(['day' => 'Hari', 'week' => 'Minggu', 'month' => 'Bulan'] as $key => $label)
                        <button
                            wire:click="setPeriod('{{ $key }}')"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 {{ $period === $key ? 'bg-white dark:bg-[#0096d2] text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-[#7dd3fc] hover:text-gray-900 dark:hover:text-white' }}"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Chart Canvas --}}
            <div class="px-5 pb-3" wire:key="line-chart-{{ $lineChartId }}" wire:ignore>
                @if(count($lineData['datasets']) > 0)
                    <canvas id="{{ $lineChartId }}" height="200"
                        x-data="{
                            chart: null,
                            init() {
                                this.loadChart();
                                this.$watch('$wire.period', () => this.destroyAndReload());
                            },
                            destroyAndReload() {
                                if (this.chart) {
                                    this.chart.destroy();
                                    this.chart = null;
                                }
                                this.$nextTick(() => this.createChart());
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
                                const ctx = document.getElementById('{{ $lineChartId }}');
                                if (!ctx) return;
                                if (this.chart) { this.chart.destroy(); }

                                const lineData = {{ Js::from($lineData) }};
                                const isDark = document.documentElement.classList.contains('dark');

                                this.chart = new Chart(ctx, {
                                    type: 'line',
                                    data: {
                                        labels: lineData.labels,
                                        datasets: lineData.datasets
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: true,
                                        interaction: {
                                            mode: 'index',
                                            intersect: false
                                        },
                                        scales: {
                                            x: {
                                                grid: {
                                                    color: isDark ? 'rgba(0,150,210,0.08)' : 'rgba(0,0,0,0.06)',
                                                    drawTicks: false
                                                },
                                                ticks: {
                                                    color: isDark ? '#7dd3fc' : '#6b7280',
                                                    font: { size: 11 },
                                                    maxRotation: 45,
                                                    autoSkip: true,
                                                    maxTicksLimit: 12
                                                },
                                                border: { display: false }
                                            },
                                            y: {
                                                grid: {
                                                    color: isDark ? 'rgba(0,150,210,0.08)' : 'rgba(0,0,0,0.06)',
                                                    drawTicks: false
                                                },
                                                ticks: {
                                                    color: isDark ? '#7dd3fc' : '#6b7280',
                                                    font: { size: 11 },
                                                    callback: function(value) {
                                                        return value.toLocaleString('id-ID');
                                                    }
                                                },
                                                border: { display: false },
                                                beginAtZero: true
                                            }
                                        },
                                        plugins: {
                                            legend: {
                                                display: true,
                                                position: 'top',
                                                align: 'end',
                                                labels: {
                                                    color: isDark ? '#bae6fd' : '#374151',
                                                    boxWidth: 10,
                                                    boxHeight: 10,
                                                    borderRadius: 3,
                                                    padding: 16,
                                                    font: { size: 11, weight: '600' }
                                                }
                                            },
                                            tooltip: {
                                                backgroundColor: isDark ? 'rgba(0,26,34,0.95)' : 'rgba(255,255,255,0.97)',
                                                titleColor: isDark ? '#f0f9ff' : '#111827',
                                                titleFont: { size: 12, weight: 'bold' },
                                                bodyColor: isDark ? '#bae6fd' : '#4b5563',
                                                bodyFont: { size: 12 },
                                                padding: 14,
                                                cornerRadius: 12,
                                                displayColors: true,
                                                boxWidth: 10,
                                                boxHeight: 10,
                                                boxPadding: 4,
                                                borderColor: isDark ? 'rgba(0,150,210,0.3)' : 'rgba(0,0,0,0.08)',
                                                borderWidth: 1,
                                                callbacks: {
                                                    label: function(context) {
                                                        return ' ' + context.dataset.label + ': ' + context.parsed.y.toLocaleString('id-ID') + ' sekolah';
                                                    }
                                                }
                                            }
                                        },
                                        animation: {
                                            duration: 600,
                                            easing: 'easeOutQuart'
                                        }
                                    }
                                });
                            }
                        }">
                    </canvas>
                @else
                    <div class="flex flex-col items-center justify-center py-16">
                        <div class="w-20 h-20 rounded-full flex items-center justify-center mb-4 bg-gray-100 dark:bg-[rgba(0,150,210,0.1)] border-2 border-dashed border-gray-300 dark:border-[rgba(0,150,210,0.3)]">
                            <x-heroicon-o-presentation-chart-line class="w-10 h-10 text-gray-400 dark:text-[#7dd3fc]" />
                        </div>
                        <p class="text-gray-600 dark:text-[#7dd3fc] font-medium">Tidak ada data</p>
                        <p class="text-xs text-gray-400 dark:text-[#7dd3fc]/70 mt-1">Coba sesuaikan filter</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ========================================
            DETAIL TABLE — Program Breakdown
            ======================================== --}}
        <div class="rounded-2xl overflow-hidden bg-gray-50 dark:bg-gradient-to-br dark:from-[#000a0e] dark:to-[#00141a] border border-gray-200 dark:border-[#0096d2]/20 shadow-xl dark:shadow-2xl dark:shadow-black">

            {{-- Table Header --}}
            <div class="px-5 py-4 border-b border-gray-200 dark:border-[#0096d2]/20">
                <h4 class="text-sm font-bold text-gray-900 dark:text-[#f0f9ff]">Detail Per Program</h4>
                <p class="text-xs text-gray-600 dark:text-[#7dd3fc]">Breakdown jumlah sekolah dan siswa</p>
            </div>

            {{-- Table Content --}}
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-200 dark:bg-[#001a22]">
                            <th class="py-3 px-4 text-left text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-[#bae6fd]">Program</th>
                            <th class="py-3 px-4 text-center text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-[#bae6fd]">Sekolah</th>
                            <th class="py-3 px-4 text-center text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-[#bae6fd]">Siswa</th>
                            <th class="py-3 px-4 text-center text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-[#bae6fd]">Porsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($chartData['details'] as $detail)
                            <tr class="border-b transition-all duration-200 hover:scale-[1.01] border-gray-200 dark:border-[#0096d2]/10 hover:bg-gray-50 dark:hover:bg-[rgba(0,150,210,0.08)]">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <span class="w-3 h-3 rounded-full flex-shrink-0 shadow-sm" style="background-color: {{ $detail['color_dark'] }}; box-shadow: 0 0 8px {{ $detail['color_dark'] }}66;"></span>
                                        <div>
                                            <p class="font-semibold text-sm text-gray-900 dark:text-[#f0f9ff]">{{ $detail['label'] }}</p>
                                            <p class="text-xs text-gray-600 dark:text-[#7dd3fc]">~{{ number_format($detail['avg_students_per_school']) }}/sekolah</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg text-sm font-bold bg-gray-200 dark:bg-[rgba(0,150,210,0.25)] text-gray-800 dark:text-white border border-gray-300 dark:border-[#0096d2]/30">
                                        {{ number_format($detail['school_count']) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg text-sm font-bold bg-gray-200 dark:bg-[rgba(160,200,10,0.25)] text-gray-800 dark:text-white border border-gray-300 dark:border-[#a0c80a]/30">
                                        {{ number_format($detail['student_count']) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-2 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700">
                                            <div class="h-full rounded-full transition-all duration-500" style="width: {{ $detail['percentage'] }}%; background-color: {{ $detail['color_dark'] }};"></div>
                                        </div>
                                        <span class="text-xs font-bold w-12 text-right !text-black dark:!text-white">{{ $detail['percentage'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <x-heroicon-o-document-text class="w-12 h-12 text-gray-400 dark:text-[#7dd3fc]/50 mb-3" />
                                        <p class="text-gray-600 dark:text-[#7dd3fc]">Tidak ada data untuk filter yang dipilih</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($chartData['details']) > 0)
                        <tfoot>
                            <tr class="bg-gradient-to-r from-gray-200 to-gray-100 dark:from-[#000f14] dark:to-[#001a22]">
                                <td class="py-3 px-4 font-bold text-gray-900 dark:text-[#f0f9ff]">Total</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg text-sm font-bold bg-gradient-to-r from-gray-600 to-gray-500 dark:from-[#0096d2] dark:to-[#0082a0] text-white shadow-md">
                                        {{ number_format($chartData['totals']['schools']) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg text-sm font-bold bg-gradient-to-r from-gray-500 to-gray-400 dark:from-[#a0c80a] dark:to-[#8cb428] text-white shadow-md">
                                        {{ number_format($chartData['totals']['students']) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-xs font-bold text-gray-600 dark:text-[#b4d81a]">100%</span>
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
