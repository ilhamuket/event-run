@extends('layouts.app')

@section('content')

<div class="min-h-screen py-14 bg-gray-50">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

        {{-- Back --}}
        <a href="{{ route('home') }}#about"
           class="inline-flex items-center gap-2 mb-10 text-sm font-medium text-gray-600 hover:text-gray-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke halaman event
        </a>

        {{-- Header --}}
        <div class="mb-12 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 mb-4 text-xs font-semibold tracking-wider text-green-700 uppercase bg-green-100 rounded-full">
                <span class="relative flex w-2 h-2">
                    <span class="absolute inline-flex w-full h-full bg-green-500 rounded-full opacity-75 animate-ping"></span>
                    <span class="relative inline-flex w-2 h-2 bg-green-600 rounded-full"></span>
                </span>
                Live Tracking
            </div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 md:text-4xl">
                Live Race Tracking
            </h1>
            <p class="mt-3 text-base text-gray-600">
                {{ $event->name }}
            </p>
            <p class="mt-2 text-sm text-gray-500">
                Total peserta terdaftar: {{ $totalParticipants }}
            </p>
        </div>

        {{-- Summary Stats --}}
        <div class="grid grid-cols-2 gap-4 mb-10 md:grid-cols-4">
            <div class="p-5 bg-white border border-gray-200 shadow-sm rounded-2xl">
                <div class="text-xs font-semibold tracking-wide text-gray-500 uppercase">Belum Start</div>
                <div class="mt-1 text-2xl font-bold text-gray-900">{{ $summary['not_started'] }}</div>
            </div>
            <div class="p-5 bg-white border border-gray-200 shadow-sm rounded-2xl">
                <div class="text-xs font-semibold tracking-wide text-gray-500 uppercase">Sedang Berlari</div>
                <div class="mt-1 text-2xl font-bold text-blue-600">{{ $summary['on_course'] }}</div>
            </div>
            <div class="p-5 bg-white border border-gray-200 shadow-sm rounded-2xl">
                <div class="text-xs font-semibold tracking-wide text-green-600 uppercase">Finish</div>
                <div class="mt-1 text-2xl font-bold text-green-600">{{ $summary['finished'] }}</div>
            </div>
            <div class="p-5 bg-white border border-gray-200 shadow-sm rounded-2xl">
                <div class="text-xs font-semibold tracking-wide text-gray-500 uppercase">Total Start</div>
                <div class="mt-1 text-2xl font-bold text-gray-900">{{ $summary['started'] }}</div>
            </div>
        </div>

        {{-- Category Filter --}}
        @if($categories->count() > 1)
        <div class="flex flex-wrap gap-2 mb-8">
            <a href="{{ route('event.live', $event->slug) }}"
               class="px-4 py-2 text-sm font-medium rounded-lg transition
                   {{ !$selectedCategory ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
                Semua Kategori
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('event.live', ['event' => $event->slug, 'category' => $cat->slug]) }}"
                   class="px-4 py-2 text-sm font-medium rounded-lg transition
                       {{ $selectedCategory == $cat->slug ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>
        @endif

        {{-- Search --}}
        <div class="mb-10 overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl">
            <form method="GET" class="flex flex-col gap-4 p-6 md:flex-row">
                @if($selectedCategory)
                    <input type="hidden" name="category" value="{{ $selectedCategory }}">
                @endif
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari BIB atau nama peserta..."
                    class="flex-1 px-4 py-3 text-sm border border-gray-300 rounded-lg focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10"
                >
                <button
                    type="submit"
                    class="px-6 py-3 text-sm font-semibold text-white bg-gray-900 rounded-lg hover:bg-gray-800">
                    Cari
                </button>
                @if(request('q'))
                    <a href="{{ route('event.live', array_filter(['event' => $event->slug, 'category' => $selectedCategory])) }}"
                       class="px-6 py-3 text-sm font-medium text-center text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Auto Refresh Notice --}}
        <div class="flex items-center justify-between p-4 mb-8 bg-white border border-gray-200 shadow-sm rounded-xl">
            <div class="flex items-center gap-3 text-sm text-gray-600">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Halaman auto-refresh setiap 30 detik
            </div>
            <a href="{{ request()->fullUrl() }}"
               class="px-4 py-2 text-xs font-semibold text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                Refresh
            </a>
        </div>

        @if($checkpointGroups->isEmpty() && !$notStarted->count())
            <div class="p-16 text-center bg-white border border-gray-200 shadow-sm rounded-2xl">
                <h3 class="text-lg font-semibold text-gray-900">
                    Belum ada aktivitas
                </h3>
                <p class="mt-2 text-sm text-gray-600">
                    Race belum dimulai atau belum ada peserta yang terdeteksi
                </p>
            </div>
        @else

        {{-- ============================================== --}}
        {{-- FINISHED SECTION --}}
        {{-- ============================================== --}}
        @if(isset($checkpointGroups['finish']) && $checkpointGroups['finish']['participants']->count())
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex items-center justify-center w-10 h-10 bg-green-100 rounded-xl">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">🏁 Finish</h2>
                    <p class="text-xs text-gray-500">{{ $checkpointGroups['finish']['participants']->count() }} peserta</p>
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
                            <th class="px-6 py-4 text-center">Elapsed Time</th>
                            <th class="px-6 py-4 text-center">Finish At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($checkpointGroups['finish']['participants'] as $i => $item)
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex items-center justify-center w-10 h-10 font-bold rounded-xl
                                    {{ ($i + 1) <= 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $item['participant']->general_position ?? ($i + 1) }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center justify-center w-12 h-12 bg-gray-900 rounded-xl">
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
                                <div class="text-lg font-bold text-green-600">
                                    {{ $item['validated_time']->formatted_elapsed_time ?? $item['participant']->formatted_elapsed_time ?? '-' }}
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
                @foreach($checkpointGroups['finish']['participants'] as $i => $item)
                <div class="p-4 bg-white border border-gray-200 shadow-sm rounded-xl">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 font-bold text-white bg-gray-900 rounded-lg">
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
                            {{ ($i + 1) <= 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-700' }}">
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
                        <div class="col-span-2">
                            <div class="text-xs text-gray-500">Elapsed Time</div>
                            <div class="text-lg font-bold text-green-600">
                                {{ $item['validated_time']->formatted_elapsed_time ?? $item['participant']->formatted_elapsed_time ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ============================================== --}}
        {{-- INTERMEDIATE CHECKPOINTS (reverse order: latest first) --}}
        {{-- ============================================== --}}
        @foreach($checkpointGroups as $key => $group)
            @if($key === 'finish' || $key === 'start')
                @continue
            @endif
            @if($group['participants']->count())
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-xl">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
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
                                        <div class="flex items-center justify-center w-12 h-12 bg-gray-900 rounded-xl">
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
                                    <div class="text-base font-bold text-blue-600">
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
                                <div class="flex items-center justify-center w-10 h-10 font-bold text-white bg-gray-900 rounded-lg">
                                    {{ $item['participant']->bib }}
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $item['participant']->display_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $item['participant']->category?->name ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-base font-bold text-blue-600">{{ $item['validated_time']->formatted_elapsed_time ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $item['validated_time']->checkpoint_time?->format('H:i:s') ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        @endforeach

        {{-- ============================================== --}}
        {{-- STARTED (only passed start, no other checkpoint yet) --}}
        {{-- ============================================== --}}
        @if(isset($checkpointGroups['start']) && $checkpointGroups['start']['participants']->count())
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex items-center justify-center w-10 h-10 bg-orange-100 rounded-xl">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">🏃 Sedang Berlari</h2>
                    <p class="text-xs text-gray-500">{{ $checkpointGroups['start']['participants']->count() }} peserta · Sudah start, belum melewati checkpoint</p>
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
                        @foreach($checkpointGroups['start']['participants'] as $i => $item)
                        <tr class="transition hover:bg-gray-50">
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex items-center justify-center w-10 h-10 font-bold text-gray-700 bg-gray-100 rounded-xl">
                                    {{ $i + 1 }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center justify-center w-12 h-12 bg-gray-900 rounded-xl">
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
                @foreach($checkpointGroups['start']['participants'] as $i => $item)
                <div class="p-4 bg-white border border-gray-200 shadow-sm rounded-xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 font-bold text-white bg-gray-900 rounded-lg">
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
        @endif

        {{-- ============================================== --}}
        {{-- NOT YET STARTED --}}
        {{-- ============================================== --}}
        @if($notStarted->count())
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex items-center justify-center w-10 h-10 bg-gray-100 rounded-xl">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
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
        @endif

        @endif

    </div>
</div>

{{-- Auto-refresh every 30 seconds --}}
<script>
    setTimeout(function() {
        window.location.reload();
    }, 30000);
</script>

@endsection
