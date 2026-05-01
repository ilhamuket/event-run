@extends('layouts.app')

@section('content')

<div class="min-h-screen py-14 bg-gray-50">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

        {{-- Back --}}
        <a href="{{ route('home') }}#about"
           class="inline-flex items-center gap-2 mb-10 text-sm font-medium text-gray-600 hover:text-red-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke halaman event
        </a>

        {{-- Header --}}
        <div class="mb-12 text-center">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 md:text-4xl">
                Race Results
            </h1>
            <p class="mt-3 text-base text-gray-600">
                {{ $event->name }}
            </p>
            <p class="mt-2 text-sm text-gray-500">
                Finisher: {{ $results->total() }} peserta
            </p>
            {{-- Label metode ranking --}}
            @if($hasGunTime)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 mt-3 text-xs font-semibold text-red-800 bg-red-100 rounded-full">
                    🏁 Ranking berdasarkan Gun Time
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 mt-3 text-xs font-semibold text-gray-600 bg-gray-100 rounded-full">
                    ⏱ Ranking berdasarkan Chip Time
                </span>
            @endif
        </div>

        {{-- Search & Filters --}}
        <div class="mb-10 overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl">
            <form method="GET" class="p-6 space-y-4">

                <div class="flex flex-col gap-4 md:flex-row">
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Cari BIB atau nama peserta..."
                        class="flex-1 px-4 py-3 text-sm border border-gray-300 rounded-lg focus:border-red-800 focus:ring-2 focus:ring-red-800/10"
                    >
                    <button type="submit"
                            class="px-6 py-3 text-sm font-semibold text-white bg-red-800 rounded-lg hover:bg-red-700">
                        Cari
                    </button>
                    @if($activeFilters > 0)
                        <a href="{{ route('event.results', $event->slug) }}"
                           class="px-6 py-3 text-sm font-medium text-center text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                            Reset Filter
                        </a>
                    @endif
                </div>

                <div>
                    <button type="button"
                            onclick="document.getElementById('filterPanel').classList.toggle('hidden')"
                            class="inline-flex items-center gap-2 text-sm font-medium text-red-800 hover:text-red-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filter Lanjutan
                        @if($activeFilters > 0)
                            <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-800 rounded-full">
                                {{ $activeFilters }}
                            </span>
                        @endif
                    </button>
                </div>

                <div id="filterPanel" class="{{ $activeFilters > 0 ? '' : 'hidden' }}">
                    <div class="grid grid-cols-1 gap-3 pt-4 border-t border-gray-200 sm:grid-cols-3">

                        <div>
                            <label class="block mb-1 text-xs font-semibold text-gray-500 uppercase">Kategori</label>
                            <select name="category"
                                    class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800/10 focus:border-red-800">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->slug }}" {{ request('category') == $cat->slug ? 'selected' : '' }}>
                                        {{ $cat->name }} ({{ $cat->distance }} KM)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 text-xs font-semibold text-gray-500 uppercase">Gender</label>
                            <select name="gender"
                                    class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800/10 focus:border-red-800">
                                <option value="">Semua Gender</option>
                                <option value="M" {{ request('gender') == 'M' ? 'selected' : '' }}>Pria</option>
                                <option value="F" {{ request('gender') == 'F' ? 'selected' : '' }}>Wanita</option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-1 text-xs font-semibold text-gray-500 uppercase">Kota</label>
                            <select name="city"
                                    class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800/10 focus:border-red-800">
                                <option value="">Semua Kota</option>
                                @foreach($filterCities as $city)
                                    <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>
                                        {{ $city }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                    <div class="pt-3 mt-3 border-t border-gray-200 sm:hidden">
                        <button type="submit"
                                class="w-full px-6 py-3 text-sm font-semibold text-white bg-red-800 rounded-lg hover:bg-red-700">
                            Terapkan Filter
                        </button>
                    </div>
                </div>

            </form>
        </div>

        {{-- Active Filter Tags --}}
        @if($activeFilters > 0)
        <div class="flex flex-wrap items-center gap-2 mb-6">
            <span class="text-xs font-semibold text-gray-500">Filter aktif:</span>
            @if(request('category'))
                <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-red-800 bg-red-100 rounded-full">
                    Kategori: {{ $categories->firstWhere('slug', request('category'))?->name ?? request('category') }}
                    <a href="{{ request()->fullUrlWithQuery(['category' => null, 'page' => null]) }}" class="ml-1 hover:text-red-600">&times;</a>
                </span>
            @endif
            @if(request('gender'))
                <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-red-800 bg-red-100 rounded-full">
                    Gender: {{ request('gender') == 'M' ? 'Pria' : 'Wanita' }}
                    <a href="{{ request()->fullUrlWithQuery(['gender' => null, 'page' => null]) }}" class="ml-1 hover:text-red-600">&times;</a>
                </span>
            @endif
            @if(request('city'))
                <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-red-800 bg-red-100 rounded-full">
                    Kota: {{ request('city') }}
                    <a href="{{ request()->fullUrlWithQuery(['city' => null, 'page' => null]) }}" class="ml-1 hover:text-red-600">&times;</a>
                </span>
            @endif
            @if(request('q'))
                <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-red-800 bg-red-100 rounded-full">
                    Cari: "{{ request('q') }}"
                    <a href="{{ request()->fullUrlWithQuery(['q' => null, 'page' => null]) }}" class="ml-1 hover:text-red-600">&times;</a>
                </span>
            @endif
        </div>
        @endif

        {{-- Empty --}}
        @if($results->isEmpty())
            <div class="p-16 text-center bg-white border border-gray-200 shadow-sm rounded-2xl">
                <h3 class="text-lg font-semibold text-gray-900">Belum ada hasil lomba</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Peserta belum menyelesaikan lomba atau tidak sesuai filter
                </p>
            </div>
        @else

        {{-- DESKTOP TABLE --}}
        <div class="hidden overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl md:block">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b bg-gray-50">
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-600 uppercase">
                            <th class="px-6 py-4 text-center">Rank</th>
                            <th class="px-6 py-4">Peserta</th>
                            <th class="px-6 py-4">Kategori</th>
                            <th class="px-6 py-4 text-center">Cat Rank</th>
                            @if($hasGunTime)
                                {{-- Kalau gun time aktif: tampilkan dua kolom waktu --}}
                                <th class="px-6 py-4 text-center">
                                    Gun Time
                                    <span class="block font-normal text-gray-400 normal-case">gun start → finish</span>
                                </th>
                                <th class="px-6 py-4 text-center">
                                    Chip Time
                                    <span class="block font-normal text-gray-400 normal-case">rfid start → finish</span>
                                </th>
                            @else
                                {{-- Pure chip time --}}
                                <th class="px-6 py-4 text-center">Waktu</th>
                            @endif
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                    @foreach($results as $index => $p)
                        <tr class="transition hover:bg-gray-50">

                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl
                                    {{ $p->general_position <= 3 ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-700' }}
                                    font-bold">
                                    {{ $p->general_position ?? ($results->firstItem() + $index) }}
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center justify-center w-12 h-12 bg-red-800 rounded-xl">
                                        <span class="text-sm font-bold text-white">{{ $p->bib }}</span>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ $p->display_name }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $p->gender === 'M' ? 'Pria' : 'Wanita' }}
                                            · {{ $p->age ?? '-' }} th
                                            · {{ $p->city ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $p->category?->name ?? '-' }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $p->category?->distance ? $p->category->distance.' KM' : '' }}
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">
                                    #{{ $p->category_position ?? '-' }}
                                </span>
                            </td>

                            @if($hasGunTime)
                                {{-- Gun time — kolom utama, dipakai untuk ranking --}}
                                <td class="px-6 py-4 text-center">
                                    <div class="text-lg font-bold text-gray-900">
                                        {{ $p->formatted_gun_elapsed_time ?? '-' }}
                                    </div>
                                    <div class="text-xs text-gray-500">Gun</div>
                                </td>
                                {{-- Chip time — kolom sekunder, informatif --}}
                                <td class="px-6 py-4 text-center">
                                    <div class="text-base font-medium text-gray-500">
                                        {{ $p->formatted_elapsed_time ?? '-' }}
                                    </div>
                                    <div class="text-xs text-gray-400">Chip</div>
                                </td>
                            @else
                                <td class="px-6 py-4 text-center">
                                    <div class="text-lg font-bold text-gray-900">
                                        {{ $p->formatted_elapsed_time }}
                                    </div>
                                    <div class="text-xs text-gray-500">Finish</div>
                                </td>
                            @endif

                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- MOBILE CARDS --}}
        <div class="space-y-4 md:hidden">
        @foreach($results as $index => $p)
            <div class="p-4 bg-white border border-gray-200 shadow-sm rounded-xl">

                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 font-bold text-white bg-red-800 rounded-lg">
                            {{ $p->bib }}
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">{{ $p->display_name }}</div>
                            <div class="text-xs text-gray-500">
                                {{ $p->gender === 'M' ? 'Pria' : 'Wanita' }}
                                · {{ $p->age ?? '-' }} th
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-center w-10 h-10 font-bold rounded-lg
                        {{ $p->general_position <= 3 ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-700' }}">
                        {{ $p->general_position ?? ($results->firstItem() + $index) }}
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <div class="text-xs text-gray-500">Kategori</div>
                        <div class="font-medium text-gray-900">{{ $p->category?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Cat Rank</div>
                        <span class="inline-block px-3 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">
                            #{{ $p->category_position ?? '-' }}
                        </span>
                    </div>

                    @if($hasGunTime)
                        <div>
                            <div class="text-xs text-gray-500">Gun Time</div>
                            <div class="text-lg font-bold text-gray-900">
                                {{ $p->formatted_gun_elapsed_time ?? '-' }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Chip Time</div>
                            <div class="text-base font-medium text-gray-500">
                                {{ $p->formatted_elapsed_time ?? '-' }}
                            </div>
                        </div>
                    @else
                        <div class="col-span-2">
                            <div class="text-xs text-gray-500">Waktu Finish</div>
                            <div class="text-lg font-bold text-gray-900">
                                {{ $p->formatted_elapsed_time }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
        </div>

        @if($results->hasPages())
            <div class="mt-8">{{ $results->links() }}</div>
        @endif

        @endif

    </div>
</div>

@endsection
