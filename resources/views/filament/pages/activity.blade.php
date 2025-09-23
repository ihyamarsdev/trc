<x-filament-panels::page>
    <x-filament-panels::header :actions="$this->getHeaderActions()">
        <x-filament-panels::header.heading>
            {{ $this->getTitle() }}
        </x-filament-panels::header.heading>
    </x-filament-panels::header>
    
    @if($record)
        <div class="space-y-6">
            <!-- School Information -->
            <x-filament::card>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-lg font-medium">School Information</h3>
                        <p class="text-sm text-gray-500">Details about the school</p>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="font-medium">School:</span>
                            <span>{{ $record->schools }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Education Level:</span>
                            <span>{{ $record->education_level }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Student Count:</span>
                            <span>{{ $record->student_count }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Period:</span>
                            <span>{{ $record->periode }}</span>
                        </div>
                    </div>
                </div>
            </x-filament::card>
            
            <!-- Activity Timeline -->
            <x-filament::card>
                <div class="space-y-4">
                    <h3 class="text-lg font-medium">Activity Timeline</h3>
                    <p class="text-sm text-gray-500">Track the progress of activities</p>
                    
                    <div class="relative">
                        <!-- Timeline line -->
                        <div class="absolute left-4 top-0 h-full w-0.5 bg-gray-200"></div>
                        
                        <div class="space-y-6">
                            @foreach($statuses as $status)
                                <div class="relative flex items-start">
                                    <!-- Timeline dot -->
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 
                                        {{ $this->isStatusCompleted($status) ? 'border-' . $this->getStatusColor($status) . '-500 bg-' . $this->getStatusColor($status) . '-100' : 'border-gray-300 bg-white' }}">
                                        @if($this->isStatusCompleted($status))
                                            <x-filament::icon 
                                                :icon="$this->getStatusIcon($status)" 
                                                :color="$this->getStatusColor($status)"
                                                class="h-4 w-4"
                                            />
                                        @else
                                            <div class="h-2 w-2 rounded-full bg-gray-400"></div>
                                        @endif
                                    </div>
                                    
                                    <!-- Content -->
                                    <div class="ml-4 min-w-0 flex-1">
                                        <div class="rounded-lg border p-4 
                                            {{ $this->currentStatus && $this->currentStatus->id == $status->id ? 'border-' . $this->getStatusColor($status) . '-500 bg-' . $this->getStatusColor($status) . '-50' : 'border-gray-200 bg-white' }}">
                                            <div class="flex items-center justify-between">
                                                <h4 class="text-sm font-medium {{ $this->isStatusCompleted($status) ? 'text-' . $this->getStatusColor($status) . '-700' : 'text-gray-900' }}">
                                                    {{ $status->name }}
                                                </h4>
                                                @if($this->currentStatus && $this->currentStatus->id == $status->id)
                                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-{{ $this->getStatusColor($status) }}-100 text-{{ $this->getStatusColor($status) }}-800">
                                                        Current
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="mt-1 text-sm text-gray-500">{{ $status->description }}</p>
                                            
                                            <!-- Status specific information -->
                                            @if($this->currentStatus && $this->currentStatus->id == $status->id)
                                                <div class="mt-3 space-y-2">
                                                    @if($status->name == 'Input Data')
                                                        <div class="text-sm">
                                                            <span class="font-medium">Registration Date:</span> 
                                                            {{ $record->date_register ? $record->date_register->format('d M Y') : 'Not set' }}
                                                        </div>
                                                    @endif
                                                    
                                                    @if($status->name == 'Membuat Grup WA')
                                                        <div class="text-sm">
                                                            <span class="font-medium">Group Created:</span> 
                                                            {{ $record->group ? $record->group->format('d M Y') : 'Not set' }}
                                                        </div>
                                                    @endif
                                                    
                                                    @if($status->name == 'Bimtek')
                                                        <div class="text-sm">
                                                            <span class="font-medium">Bimtek Date:</span> 
                                                            {{ $record->bimtek ? $record->bimtek->format('d M Y') : 'Not set' }}
                                                        </div>
                                                    @endif
                                                    
                                                    @if($status->name == 'Pelaksanaan')
                                                        <div class="text-sm">
                                                            <span class="font-medium">Implementation Date:</span> 
                                                            {{ $record->implementation_estimate ? $record->implementation_estimate->format('d M Y') : 'Not set' }}
                                                        </div>
                                                    @endif
                                                    
                                                    @if($status->name == 'Pembayaran')
                                                        <div class="text-sm">
                                                            <span class="font-medium">Payment Date:</span> 
                                                            {{ $record->payment_date ? $record->payment_date->format('d M Y') : 'Not set' }}
                                                        </div>
                                                        <div class="text-sm">
                                                            <span class="font-medium">Payment Status:</span> 
                                                            {{ $record->payment ?: 'Not set' }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </x-filament::card>
        </div>
    @else
        <x-filament::card>
            <div class="text-center py-12">
                <x-filament::icon icon="heroicon-o-exclamation-circle" class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">No record selected</h3>
                <p class="mt-1 text-sm text-gray-500">Please select a registration record to view activity timeline.</p>
            </div>
        </x-filament::card>
    @endif
</x-filament-panels::page>