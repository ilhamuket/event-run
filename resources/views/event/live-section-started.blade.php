{{--
    event/_live-section-started.blade.php
    Peserta yang sudah melewati start gate tapi belum ada checkpoint lain.
    @param array $group  ['checkpoint' => object, 'participants' => Collection]
--}}
<div class="mb-8">
    <div class="flex items-center gap-3 mb-4">
        <div class="flex items-center justify-center w-10 h-10 bg-red-100 rounded-xl">
            <svg class="w-5 h-5 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </div>
        <div>
            <h2 class="text-lg font-bold text-gray-900">🏃 Sedang Berlari</h2>
            <p class="text-xs text-gray-500">
                {{ $group['participants']->count() }} peserta · Sudah start, belum melewati checkpoint
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
                    <th class="px-6 py-4 text-center">Start At</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($group['participants'] as $i => $item)
                <tr class="transition hover:bg-gray-50">
                    <td class="px-6 py-4 text-center">
                        <div class="inline-flex items-center justify-center w-10 h-10 font-bold text-gray-700 bg-gray-100 rounded-xl">
                            {{ $i + 1 }}
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
                    <td class="px-6 py-4 text-sm text-center text-gray-700">
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
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 font-bold text-white bg-red-800 rounded-lg">
                        {{ $item['participant']->bib }}
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">{{ $item['participant']->display_name }}</div>
                        <div class="text-xs text-gray-500">{{ $item['participant']->category?->name ?? '-' }}</div>
                    </div>
                </div>
                <div class="text-sm text-gray-700">
                    {{ $item['validated_time']->checkpoint_time?->format('H:i:s') ?? '-' }}
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
