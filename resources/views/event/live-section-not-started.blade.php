{{--
    event/_live-section-not-started.blade.php
    Peserta yang belum terdeteksi di checkpoint manapun.
    @param \Illuminate\Support\Collection $notStarted
--}}
<div class="mb-8">
    <div class="flex items-center gap-3 mb-4">
        <div class="flex items-center justify-center w-10 h-10 bg-gray-100 rounded-xl">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <h2 class="text-lg font-bold text-gray-900">⏳ Belum Start</h2>
            <p class="text-xs text-gray-500">{{ $notStarted->count() }} peserta</p>
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
                    <th class="px-6 py-4 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($notStarted as $i => $p)
                <tr class="transition hover:bg-gray-50">
                    <td class="px-6 py-4 text-center">
                        <div class="inline-flex items-center justify-center w-10 h-10 font-bold text-gray-400 bg-gray-100 rounded-xl">
                            {{ $i + 1 }}
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center justify-center w-12 h-12 bg-gray-300 rounded-xl">
                                <span class="text-sm font-bold text-white">{{ $p->bib }}</span>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-500">{{ $p->display_name }}</div>
                                <div class="text-xs text-gray-400">
                                    {{ $p->gender === 'M' ? 'Pria' : 'Wanita' }}
                                    · {{ $p->age ?? '-' }} th
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-500">{{ $p->category?->name ?? '-' }}</div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 text-xs font-semibold text-gray-500 bg-gray-100 rounded-full">
                            Menunggu
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Mobile --}}
    <div class="space-y-3 md:hidden">
        @foreach($notStarted as $i => $p)
        <div class="p-4 bg-white border border-gray-200 shadow-sm rounded-xl opacity-60">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 font-bold text-white bg-gray-300 rounded-lg">
                        {{ $p->bib }}
                    </div>
                    <div>
                        <div class="font-semibold text-gray-500">{{ $p->display_name }}</div>
                        <div class="text-xs text-gray-400">{{ $p->category?->name ?? '-' }}</div>
                    </div>
                </div>
                <span class="px-3 py-1 text-xs font-semibold text-gray-500 bg-gray-100 rounded-full">
                    Menunggu
                </span>
            </div>
        </div>
        @endforeach
    </div>
</div>
