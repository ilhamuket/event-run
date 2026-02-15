@extends('layouts.app')

@section('content')

<div class="min-h-screen py-10 bg-red-50">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

        {{-- Back --}}
        <a href="{{ route('home') }}#about"
           class="inline-flex items-center gap-2 mb-8 text-sm font-medium text-gray-600 hover:text-red-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke halaman event
        </a>

        {{-- Header --}}
        <div class="mb-10 text-center">
            <h1 class="text-2xl font-bold text-red-900 sm:text-3xl">
                Daftar Peserta
            </h1>
            <p class="mt-2 text-sm text-gray-600">
                {{ $event->name }}
            </p>
            <p class="mt-1 text-xs text-gray-500">
                Total peserta: {{ $participants->total() }}
            </p>
        </div>

        {{-- Search & Filters --}}
        <div class="mb-8 bg-white border border-gray-200 shadow-sm rounded-2xl">
            <form method="GET" class="p-4 space-y-4">

                {{-- Search Row --}}
                <div class="flex flex-col gap-3 sm:flex-row">
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Cari BIB, nama, email, komunitas"
                        class="flex-1 px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800/10 focus:border-red-800"
                    >
                    <button type="submit"
                            class="px-6 py-3 text-sm font-semibold text-white bg-red-800 rounded-lg hover:bg-red-700">
                        Cari
                    </button>
                    @if($activeFilters > 0)
                        <a href="{{ route('event.participants', $event->slug) }}"
                           class="px-6 py-3 text-sm font-medium text-center text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                            Reset Filter
                        </a>
                    @endif
                </div>

                {{-- Filter Toggle --}}
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

                {{-- Filter Panel --}}
                <div id="filterPanel" class="{{ $activeFilters > 0 ? '' : 'hidden' }}">
                    <div class="grid grid-cols-1 gap-3 pt-4 border-t border-gray-200 sm:grid-cols-2 lg:grid-cols-5">

                        {{-- Kategori --}}
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

                        {{-- Gender --}}
                        <div>
                            <label class="block mb-1 text-xs font-semibold text-gray-500 uppercase">Gender</label>
                            <select name="gender"
                                    class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800/10 focus:border-red-800">
                                <option value="">Semua Gender</option>
                                <option value="M" {{ request('gender') == 'M' ? 'selected' : '' }}>Pria</option>
                                <option value="F" {{ request('gender') == 'F' ? 'selected' : '' }}>Wanita</option>
                            </select>
                        </div>

                        {{-- Kota --}}
                        <div>
                            <label class="block mb-1 text-xs font-semibold text-gray-500 uppercase">Kota</label>
                            <select name="city"
                                    class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800/10 focus:border-red-800">
                                <option value="">Semua Kota</option>
                                @foreach($filterOptions['cities'] as $city)
                                    <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>
                                        {{ $city }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Komunitas --}}
                        <div>
                            <label class="block mb-1 text-xs font-semibold text-gray-500 uppercase">Komunitas</label>
                            <select name="community"
                                    class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800/10 focus:border-red-800">
                                <option value="">Semua Komunitas</option>
                                @foreach($filterOptions['communities'] as $community)
                                    <option value="{{ $community }}" {{ request('community') == $community ? 'selected' : '' }}>
                                        {{ $community }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Jersey Size --}}
                        <div>
                            <label class="block mb-1 text-xs font-semibold text-gray-500 uppercase">Ukuran Jersey</label>
                            <select name="jersey_size"
                                    class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800/10 focus:border-red-800">
                                <option value="">Semua Ukuran</option>
                                @foreach($filterOptions['jersey_sizes'] as $size)
                                    <option value="{{ $size }}" {{ request('jersey_size') == $size ? 'selected' : '' }}>
                                        {{ $size }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    {{-- Apply button for mobile --}}
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
            @if(request('community'))
                <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-red-800 bg-red-100 rounded-full">
                    Komunitas: {{ request('community') }}
                    <a href="{{ request()->fullUrlWithQuery(['community' => null, 'page' => null]) }}" class="ml-1 hover:text-red-600">&times;</a>
                </span>
            @endif
            @if(request('jersey_size'))
                <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-red-800 bg-red-100 rounded-full">
                    Jersey: {{ request('jersey_size') }}
                    <a href="{{ request()->fullUrlWithQuery(['jersey_size' => null, 'page' => null]) }}" class="ml-1 hover:text-red-600">&times;</a>
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

        @if($participants->isEmpty())
            <div class="p-12 text-center bg-white border border-gray-200 shadow-sm rounded-2xl">
                <h3 class="text-base font-semibold text-red-900">
                    Peserta tidak ditemukan
                </h3>
                <p class="mt-1 text-sm text-gray-600">
                    Coba ubah filter atau kata kunci lain
                </p>
            </div>
        @else

        {{-- ================= MOBILE VIEW (CARD) ================= --}}
        <div class="space-y-4 md:hidden">
            @foreach($participants as $p)
                <div class="p-4 bg-white border border-gray-200 shadow-sm rounded-xl">
                    <div class="flex items-start gap-4">
                        <div class="flex items-center justify-center w-12 h-12 text-sm font-bold text-white bg-red-800 rounded-xl">
                            {{ $p->bib }}
                        </div>

                        <div class="flex-1">
                            <div class="font-semibold text-red-900">
                                {{ $p->name }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $p->category?->name ?? '-' }}
                                {{ $p->category?->distance ? '· '.$p->category->distance.' KM' : '' }}
                            </div>

                            <div class="flex flex-wrap gap-2 mt-2">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $p->gender === 'M'
                                        ? 'bg-blue-100 text-blue-700'
                                        : 'bg-pink-100 text-pink-700' }}">
                                    {{ $p->gender === 'M' ? 'Pria' : 'Wanita' }}
                                </span>

                                @if($p->jersey_size)
                                    <span class="px-2 py-1 text-xs font-semibold text-gray-700 bg-gray-100 rounded-full">
                                        {{ $p->jersey_size }}
                                    </span>
                                @endif

                                @if($p->has_comorbid)
                                    <span class="px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full">
                                        ⚠ Medis
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
                                        ✔ Aman
                                    </span>
                                @endif
                            </div>

                            <div class="mt-3 text-xs text-gray-500">
                                <div>{{ $p->email ?? '-' }}</div>
                                <div>{{ $p->phone ?? '-' }}</div>
                                <div>{{ $p->city ?? '-' }}</div>
                                <div>{{ $p->community ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ================= DESKTOP VIEW (TABLE) ================= --}}
        <div class="hidden bg-white border border-gray-200 shadow-sm md:block rounded-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b bg-red-50">
                    <tr class="text-xs font-semibold text-red-800 uppercase">
                        <th class="px-6 py-4">BIB</th>
                        <th class="px-6 py-4">Peserta</th>
                        <th class="px-6 py-4 text-center">Gender</th>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4">Kota</th>
                        <th class="px-6 py-4">Komunitas</th>
                        <th class="px-6 py-4">Kontak</th>
                        <th class="px-6 py-4 text-center">Medis</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y">
                    @foreach($participants as $p)
                        <tr class="hover:bg-red-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center w-12 h-12 text-sm font-bold text-white bg-red-800 rounded-xl">
                                    {{ $p->bib }}
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-semibold text-red-900">{{ $p->name }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $p->bib_name ?? '-' }} · {{ $p->age ?? '-' }} th
                                    @if($p->jersey_size)
                                        · Jersey: {{ $p->jersey_size }}
                                    @endif
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full
                                    {{ $p->gender === 'M'
                                        ? 'bg-blue-100 text-blue-700'
                                        : 'bg-pink-100 text-pink-700' }}">
                                    {{ $p->gender === 'M' ? 'Pria' : 'Wanita' }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                {{ $p->category?->name ?? '-' }}
                            </td>

                            <td class="px-6 py-4">{{ $p->city ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $p->community ?? '-' }}</td>

                            <td class="px-6 py-4 text-xs">
                                <div>{{ $p->email ?? '-' }}</div>
                                <div class="text-gray-400">{{ $p->phone ?? '-' }}</div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                @if($p->has_comorbid)
                                    <span class="px-3 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full">⚠ Ada</span>
                                @else
                                    <span class="px-3 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">✔ Aman</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($participants->hasPages())
            <div class="mt-8">
                {{ $participants->links() }}
            </div>
        @endif

        @endif

    </div>
</div>

@endsection
