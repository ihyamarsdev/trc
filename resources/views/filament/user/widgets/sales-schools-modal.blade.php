<div class="space-y-4">
    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm bg-white dark:bg-gray-900">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead class="bg-gray-50 dark:bg-gray-950">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">No</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Sekolah</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Program</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Siswa</th>
                    <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estimasi Implementasi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse ($schools as $index => $school)
                    @php
                        $statusName = $school->status?->name ?? 'N/A';
                        $statusColorClass = match($school->status?->color) {
                            'red' => 'status-red font-semibold',
                            'yellow' => 'status-yellow font-semibold',
                            'blue' => 'status-blue font-semibold',
                            'green' => 'status-green font-semibold',
                            default => 'text-gray-500 dark:text-gray-400',
                        };

                        $program = \App\Filament\Enum\Program::tryFrom(strtolower($school->type));
                        $programLabel = $program ? $program->label() : strtoupper($school->type);
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">{{ $school->schools }}</td>
                        <td class="px-4 py-3 text-center whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 font-medium">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700">
                                {{ $programLabel }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap text-sm text-gray-900 dark:text-white font-medium">{{ number_format($school->student_count) }}</td>
                        <td class="px-4 py-3 text-center whitespace-nowrap text-sm">
                            <span class="{{ $statusColorClass }}">
                                {{ $statusName }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                            {{ $school->implementation_estimate ? \Illuminate\Support\Carbon::parse($school->implementation_estimate)->translatedFormat('d F Y') : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            Belum ada sekolah yang ditangani.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
