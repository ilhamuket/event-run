<x-filament-panels::page>
    <div wire:poll.5s>
        {{-- Event & Category Selection --}}
        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Event</label>
                <select wire:model.live="selectedEventId" class="w-full border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700">
                    <option value="">Select Event</option>
                    @foreach($this->events as $event)
                        <option value="{{ $event->id }}">{{ $event->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                <select wire:model.live="selectedCategoryId" class="w-full border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700">
                    <option value="">Select Category</option>
                    @foreach($this->categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->distance }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Gender Filter</label>
                <select wire:model.live="genderFilter" class="w-full border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700">
                    <option value="">All Genders</option>
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                </select>
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-2 gap-4 mb-6 md:grid-cols-5">
            <div class="p-4 bg-white border-l-4 border-blue-500 shadow dark:bg-gray-800 rounded-xl">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Participants</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->statistics['total_participants'] }}</div>
            </div>
            <div class="p-4 bg-white border-l-4 border-green-500 shadow dark:bg-gray-800 rounded-xl">
                <div class="text-sm text-gray-500 dark:text-gray-400">Started</div>
                <div class="text-2xl font-bold text-green-600">{{ $this->statistics['started'] }}</div>
            </div>
            <div class="p-4 bg-white border-l-4 border-yellow-500 shadow dark:bg-gray-800 rounded-xl">
                <div class="text-sm text-gray-500 dark:text-gray-400">On Course</div>
                <div class="text-2xl font-bold text-yellow-600">{{ $this->statistics['on_course'] }}</div>
            </div>
            <div class="p-4 bg-white border-l-4 border-purple-500 shadow dark:bg-gray-800 rounded-xl">
                <div class="text-sm text-gray-500 dark:text-gray-400">Finished</div>
                <div class="text-2xl font-bold text-purple-600">{{ $this->statistics['finished'] }}</div>
            </div>
            <div class="p-4 bg-white border-l-4 border-red-500 shadow dark:bg-gray-800 rounded-xl">
                <div class="text-sm text-gray-500 dark:text-gray-400">DNF</div>
                <div class="text-2xl font-bold text-red-600">{{ $this->statistics['dnf'] }}</div>
            </div>
        </div>

        {{-- Checkpoint Progress --}}
        @if($this->checkpoints->count() > 0)
        <div class="p-4 mb-6 bg-white shadow dark:bg-gray-800 rounded-xl">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Checkpoint Progress</h3>
            <div class="grid grid-cols-2 md:grid-cols-{{ min($this->checkpoints->count(), 6) }} gap-4">
                @foreach($this->checkpoints as $checkpoint)
                    <div class="text-center p-3 rounded-lg {{ $checkpoint->checkpoint_type === 'start' ? 'bg-green-50 dark:bg-green-900/20' : ($checkpoint->checkpoint_type === 'finish' ? 'bg-purple-50 dark:bg-purple-900/20' : 'bg-gray-50 dark:bg-gray-700') }}">
                        <div class="text-xs tracking-wide text-gray-500 uppercase dark:text-gray-400">
                            {{ $checkpoint->checkpoint_type === 'start' ? '🏁' : ($checkpoint->checkpoint_type === 'finish' ? '🎯' : '📍') }}
                            {{ $checkpoint->checkpoint_name }}
                        </div>
                        <div class="text-2xl font-bold {{ $checkpoint->checkpoint_type === 'start' ? 'text-green-600' : ($checkpoint->checkpoint_type === 'finish' ? 'text-purple-600' : 'text-gray-700 dark:text-gray-300') }}">
                            {{ $checkpoint->validated_times_count }}
                        </div>
                        @if($checkpoint->distance_km)
                            <div class="text-xs text-gray-400">{{ $checkpoint->distance_km }} km</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- Leaderboard --}}
            <div class="bg-white shadow dark:bg-gray-800 rounded-xl">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        🏆 Leaderboard {{ $genderFilter ? ($genderFilter === 'M' ? '(Male)' : '(Female)') : '' }}
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">#</th>
                                <th class="px-4 py-2 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">BIB</th>
                                <th class="px-4 py-2 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">Name</th>
                                <th class="px-4 py-2 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($this->leaderboard as $index => $participant)
                                <tr class="{{ $index < 3 ? 'bg-yellow-50 dark:bg-yellow-900/10' : '' }}">
                                    <td class="px-4 py-2 text-sm font-medium">
                                        @if($index === 0)
                                            🥇
                                        @elseif($index === 1)
                                            🥈
                                        @elseif($index === 2)
                                            🥉
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm font-bold text-gray-900 dark:text-white">{{ $participant->bib }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $participant->display_name }}
                                        <span class="text-xs text-gray-400">({{ $participant->gender }})</span>
                                    </td>
                                    <td class="px-4 py-2 font-mono text-sm text-gray-900 dark:text-white">
                                        {{ $participant->formatted_elapsed_time }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        No finishers yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Recent Scans --}}
            <div class="bg-white shadow dark:bg-gray-800 rounded-xl">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">📡 Recent Scans</h3>
                </div>
                <div class="overflow-x-auto max-h-96">
                    <table class="w-full">
                        <thead class="sticky top-0 bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">Time</th>
                                <th class="px-4 py-2 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">Checkpoint</th>
                                <th class="px-4 py-2 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">BIB</th>
                                <th class="px-4 py-2 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($this->recentScans as $scan)
                                <tr class="{{ $scan->is_valid ? '' : 'bg-red-50 dark:bg-red-900/10' }}">
                                    <td class="px-4 py-2 font-mono text-xs text-gray-600 dark:text-gray-400">
                                        {{ $scan->scanned_at->format('H:i:s') }}
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $scan->checkpoint?->checkpoint_name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $scan->bib ?? $scan->rfid_tag }}
                                    </td>
                                    <td class="px-4 py-2">
                                        @if($scan->is_valid)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                                ✓ Valid
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100" title="{{ $scan->notes }}">
                                                ✗ Invalid
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        No scans yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
