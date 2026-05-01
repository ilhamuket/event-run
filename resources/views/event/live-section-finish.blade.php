{{--
    event/_live-section-finish.blade.php
    @param array $group       ['checkpoint' => object, 'participants' => Collection]
    @param bool  $hasGunTime  true kalau event ini pakai gun time
--}}
<div class="mb-8">
    <div class="flex items-center gap-3 mb-4">
        <div class="flex items-center justify-center w-10 h-10 bg-red-100 rounded-xl">
            <svg class="w-5 h-5 text-red-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <h2 class="text-lg font-bold text-gray-900">🏁 Finish</h2>
            <p class="text-xs text-gray-500">
                {{ $group['participants']->count() }} peserta
                @if($hasGunTime)
                    · Ranking by Gun Time
                @endif
            </p>
        </div>
    </div>

    {{-- Desktop --}}
    <div class="hidden overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl md:block">
        <table class="w-full text-sm">
            <thead class="border-b bg-gray-50">
                <tr class="text-xs font-semibold tracking-wide text-left text-gray-600 uppercase">
                    <th class="px-6 py-4 text-center">Rank</th>
                    <th class="px-6 py-4">Peserta</th>
                    <th class="px-6 py-4">Kategori</th>
                    @if($hasGunTime)
                        <th class="px-6 py-4 text-center">
                            Gun Time
                            <span class="block font-normal text-gray-400 normal-case">gun start → finish</span>
                        </th>
                        <th class="px-6 py-4 text-center">
                            Chip Time
                            <span class="block font-normal text-gray-400 normal-case">rfid start → finish</span>
                        </th>
                    @else
                        <th class="px-6 py-4 text-center">Elapsed Time</th>
                    @endif
                    <th class="px-6 py-4 text-center">Finish At</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($group['participants'] as $i => $item)
                <tr class="transition hover:bg-gray-50">
                    <td class="px-6 py-4 text-center">
                        <div class="inline-flex items-center justify-center w-10 h-10 font-bold rounded-xl
                            {{ ($i + 1) <= 3 ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-700' }}">
                            {{ $item['participant']->general_position ?? ($i + 1) }}
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center justify-center w-12 h-12 bg-red-800 rounded-xl">
                                <span class="text-sm font-bold text-white">{{ $item['participant']->bib }}</span>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900">{{ $item['participant']->display_name }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $item['participant']->gender === 'M' ? 'Pria' : 'Wanita' }}
                                    · {{ $item['participant']->age ?? '-' }} th
                                    · {{ $item['participant']->city ?? '-' }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900">{{ $item['participant']->category?->name ?? '-' }}</div>
                    </td>

                    @if($hasGunTime)
                        {{-- Gun time — angka utama --}}
                        <td class="px-6 py-4 text-center">
                            <div class="text-lg font-bold text-red-800">
                                {{ $item['participant']->formatted_gun_elapsed_time ?? '-' }}
                            </div>
                        </td>
                        {{-- Chip time — sekunder --}}
                        <td class="px-6 py-4 text-center">
                            <div class="text-base font-medium text-gray-400">
                                {{ $item['validated_time']->formatted_elapsed_time
                                    ?? $item['participant']->formatted_elapsed_time
                                    ?? '-' }}
                            </div>
                        </td>
                    @else
                        <td class="px-6 py-4 text-center">
                            <div class="text-lg font-bold text-red-800">
                                {{ $item['validated_time']->formatted_elapsed_time
                                    ?? $item['participant']->formatted_elapsed_time
                                    ?? '-' }}
                            </div>
                        </td>
                    @endif

                    <td class="px-6 py-4 text-xs text-center text-gray-500">
                        {{ $item['validated_time']->checkpoint_time?->format('H:i:s') ?? '-' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Mobile --}}
    <div class="space-y-3 md:hidden">
        @foreach($group['participants'] as $i => $item)
        <div class="p-4 bg-white border border-gray-200 shadow-sm rounded-xl">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 font-bold text-white bg-red-800 rounded-lg">
                        {{ $item['participant']->bib }}
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">{{ $item['participant']->display_name }}</div>
                        <div class="text-xs text-gray-500">
                            {{ $item['participant']->gender === 'M' ? 'Pria' : 'Wanita' }}
                            · {{ $item['participant']->age ?? '-' }} th
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-center w-10 h-10 font-bold rounded-lg
                    {{ ($i + 1) <= 3 ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-700' }}">
                    {{ $item['participant']->general_position ?? ($i + 1) }}
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <div class="text-xs text-gray-500">Kategori</div>
                    <div class="font-medium text-gray-900">{{ $item['participant']->category?->name ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Finish At</div>
                    <div class="text-xs text-gray-700">{{ $item['validated_time']->checkpoint_time?->format('H:i:s') ?? '-' }}</div>
                </div>

                @if($hasGunTime)
                    <div>
                        <div class="text-xs text-gray-500">Gun Time</div>
                        <div class="text-lg font-bold text-red-800">
                            {{ $item['participant']->formatted_gun_elapsed_time ?? '-' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Chip Time</div>
                        <div class="text-base font-medium text-gray-400">
                            {{ $item['validated_time']->formatted_elapsed_time
                                ?? $item['participant']->formatted_elapsed_time
                                ?? '-' }}
                        </div>
                    </div>
                @else
                    <div class="col-span-2">
                        <div class="text-xs text-gray-500">Elapsed Time</div>
                        <div class="text-lg font-bold text-red-800">
                            {{ $item['validated_time']->formatted_elapsed_time
                                ?? $item['participant']->formatted_elapsed_time
                                ?? '-' }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
