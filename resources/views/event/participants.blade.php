@extends('layouts.app')

@section('content')

<div class="min-h-screen py-10 bg-gray-50">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

        {{-- Back --}}
        <a href="{{ route('home') }}#about"
           class="inline-flex items-center gap-2 mb-8 text-sm font-medium text-gray-600 hover:text-gray-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke halaman event
        </a>

        {{-- Header --}}
        <div class="mb-10 text-center">
            <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">
                Daftar Peserta
            </h1>
            <p class="mt-2 text-sm text-gray-600">
                {{ $event->name }}
            </p>
            <p class="mt-1 text-xs text-gray-500">
                Total peserta: {{ $participants->total() }}
            </p>
        </div>

        {{-- Search --}}
        <div class="mb-8 bg-white border border-gray-200 shadow-sm rounded-2xl">
            <form method="GET" class="flex flex-col gap-3 p-4 sm:flex-row">
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari BIB, nama, email, komunitas"
                    class="flex-1 px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-900/10"
                >

                <button type="submit"
                        class="px-6 py-3 text-sm font-semibold text-white bg-gray-900 rounded-lg">
                    Cari
                </button>

                @if(request('q'))
                    <a href="{{ route('event.participants', $event->slug) }}"
                       class="px-6 py-3 text-sm font-medium text-center text-gray-700 border border-gray-300 rounded-lg">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        @if($participants->isEmpty())
            <div class="p-12 text-center bg-white border border-gray-200 shadow-sm rounded-2xl">
                <h3 class="text-base font-semibold text-gray-900">
                    Peserta tidak ditemukan
                </h3>
                <p class="mt-1 text-sm text-gray-600">
                    Coba kata kunci lain
                </p>
            </div>
        @else

        {{-- ================= MOBILE VIEW (CARD) ================= --}}
        <div class="space-y-4 md:hidden">
            @foreach($participants as $p)
                <div class="p-4 bg-white border border-gray-200 shadow-sm rounded-xl">
                    <div class="flex items-start gap-4">
                        <div class="flex items-center justify-center w-12 h-12 text-sm font-bold text-white bg-gray-900 rounded-xl">
                            {{ $p->bib }}
                        </div>

                        <div class="flex-1">
                            <div class="font-semibold text-gray-900">
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
                    <thead class="border-b bg-gray-50">
                    <tr class="text-xs font-semibold text-gray-600 uppercase">
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
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center w-12 h-12 text-sm font-bold text-white bg-gray-900 rounded-xl">
                                    {{ $p->bib }}
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ $p->name }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $p->bib_name ?? '-' }} · {{ $p->age ?? '-' }} th
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
