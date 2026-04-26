{{--
    event/_live-section-checkpoint.blade.php
    Intermediate checkpoint section (bukan start / finish).
    @param array $group  ['checkpoint' => object, 'participants' => Collection]
--}}
<div class="mb-8">
    <div class="flex items-center gap-3 mb-4">
        <div class="flex items-center justify-center w-10 h-10 bg-red-100 rounded-xl">
            <svg class="w-5 h-5 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <div>
            <h2 class="text-lg font-bold text-gray-900">📍 {{ $group['checkpoint']->checkpoint_name }}</h2>
            <p class="text-xs text-gray-500">
                {{ $group['participants']->count() }} peserta
                @if($group['checkpoint']->distance_km)
                    · KM {{ $group['checkpoint']->distance_km }}
                @endif
            </p>
        </div>
    </div>

    {{-- Desktop --}}
    <div class="hidden overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl md:block">
        <table class="w-full text-sm">
            <thead class="border-b bg-gray-50">
                <tr class="text-xs font-semibold tracking-wide text-left text-gray-600 uppercase">
                    <th class="px-6 py-4 text-center">#</th>
                    <th class="px-6 py-4">Peserta</th>
                    <th class="px-6 py-4">Kategori</th>
                    <th class="px-6 py-4 text-center">Elapsed Time</th>
                    <th class="px-6 py-4 text-center">Passed At</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($group['participants'] as $i => $item)
                <tr class="transition hover:bg-gray-50">
                    <td class="px-6 py-4 text-center">
                        <div class="inline-flex items-center justify-center w-10 h-10 font-bold text-gray-700 bg-gray-100 rounded-xl">
                            {{ $item['validated_time']->position_at_checkpoint ?? ($i + 1) }}
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
                    <td class="px-6 py-4 text-center">
                        <div class="text-base font-bold text-red-700">
                            {{ $item['validated_time']->formatted_elapsed_time ?? '-' }}
                        </div>
                    </td>
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
                        <div class="text-xs text-gray-500">{{ $item['participant']->category?->name ?? '-' }}</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-base font-bold text-red-700">{{ $item['validated_time']->formatted_elapsed_time ?? '-' }}</div>
                    <div class="text-xs text-gray-500">{{ $item['validated_time']->checkpoint_time?->format('H:i:s') ?? '-' }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
