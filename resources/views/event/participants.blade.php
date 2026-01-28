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
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 md:text-4xl">
                Daftar Peserta
            </h1>
            <p class="mt-3 text-base text-gray-600">
                {{ $event->name }}
            </p>
            <p class="mt-2 text-sm text-gray-500">
                Total peserta: {{ $participants->total() }}
            </p>
        </div>

        {{-- Search --}}
        <div class="mb-10 overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl">
            <form method="GET" class="flex flex-col gap-4 p-6 md:flex-row">
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari BIB, nama, email, atau komunitas"
                    class="flex-1 px-4 py-3 text-sm border border-gray-300 rounded-lg focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10"
                >

                <button
                    type="submit"
                    class="px-6 py-3 text-sm font-semibold text-white bg-gray-900 rounded-lg hover:bg-gray-800">
                    Cari
                </button>

                @if(request('q'))
                    <a href="{{ route('event.participants', $event->slug) }}"
                       class="px-6 py-3 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Empty --}}
        @if($participants->isEmpty())
            <div class="p-16 text-center bg-white border border-gray-200 shadow-sm rounded-2xl">
                <h3 class="text-lg font-semibold text-gray-900">
                    Peserta tidak ditemukan
                </h3>
                <p class="mt-2 text-sm text-gray-600">
                    Coba kata kunci lain
                </p>
            </div>
        @else

        {{-- Table --}}
        <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b bg-gray-50">
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-600 uppercase">
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

                    <tbody class="divide-y divide-gray-200">
                        @foreach($participants as $p)
                        <tr class="transition hover:bg-gray-50">

                            {{-- BIB --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center w-12 h-12 bg-gray-900 rounded-xl">
                                    <span class="text-sm font-bold text-white">
                                        {{ $p->bib }}
                                    </span>
                                </div>
                            </td>

                            {{-- PESERTA --}}
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">
                                    {{ $p->name }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    BIB Name: {{ $p->bib_name ?? '-' }} · {{ $p->age ?? '-' }} th
                                </div>
                            </td>

                            {{-- GENDER --}}
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold
                                    {{ $p->gender === 'M'
                                        ? 'bg-blue-100 text-blue-700'
                                        : 'bg-pink-100 text-pink-700' }}">
                                    {{ $p->gender === 'M' ? 'Pria' : 'Wanita' }}
                                </span>
                            </td>

                            {{-- KATEGORI --}}
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">
                                    {{ $p->category?->name ?? '-' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $p->category?->distance ? $p->category->distance.' KM' : '' }}
                                </div>
                            </td>

                            {{-- KOTA --}}
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $p->city ?? '-' }}
                            </td>

                            {{-- KOMUNITAS --}}
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $p->community ?? '-' }}
                            </td>

                            {{-- KONTAK --}}
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <div>{{ $p->email ?? '-' }}</div>
                                <div class="text-xs text-gray-400">
                                    {{ $p->phone ?? '-' }}
                                </div>
                            </td>

                            {{-- MEDIS --}}
                            <td class="px-6 py-4 text-center">
                                @if($p->has_comorbid)
                                    <span class="px-3 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full">
                                        ⚠ Ada
                                    </span>
                                @else
                                    <span class="px-3 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
                                        ✔ Aman
                                    </span>
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
