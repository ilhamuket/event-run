@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-10">
            <a href="/" class="inline-flex items-center gap-2 text-gray-600 hover:text-blue-600 mb-6 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali ke Event
            </a>

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="inline-block px-4 py-2 bg-yellow-500 text-white rounded-full text-sm font-bold mb-3">
                        🏆 RACE RESULT
                    </div>
                    <h1 class="text-3xl md:text-4xl font-bold mb-2">Hasil Perlombaan</h1>
                    <p class="text-gray-600">{{ $event->name }}</p>
                </div>
                <div class="bg-blue-600 text-white px-6 py-3 rounded-lg font-bold">
                    Total Finisher: {{ $results->total() }}
                </div>
            </div>
        </div>

        {{-- Search Bar --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8 border">
            <form method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1 relative">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Cari berdasarkan BIB atau Nama Peserta..."
                        class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-600 focus:outline-none transition-colors"
                    >
                </div>
                <button type="submit" class="px-8 py-3 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition-all whitespace-nowrap">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Cari
                </button>
                @if(request('q'))
                    <a href="{{ route('event.results', $event->slug) }}"
                       class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg font-bold hover:bg-gray-50 transition-all whitespace-nowrap">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Podium Section (Top 3) --}}
        @if(!request('q') && $results->count() >= 3)
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-center mb-8">🏆 Top 3 Finisher</h2>
                <div class="grid md:grid-cols-3 gap-6">
                    @foreach($results->take(3) as $index => $winner)
                        <div class="bg-gradient-to-br {{ $index == 0 ? 'from-yellow-400 to-yellow-600' : ($index == 1 ? 'from-gray-300 to-gray-500' : 'from-orange-400 to-orange-600') }} rounded-lg p-6 text-white shadow-xl transform {{ $index == 0 ? 'md:-translate-y-4 md:scale-105' : '' }} transition-all hover:scale-105">
                            <div class="text-center">
                                <div class="text-6xl mb-4">
                                    {{ $index == 0 ? '🥇' : ($index == 1 ? '🥈' : '🥉') }}
                                </div>
                                <div class="text-4xl font-bold mb-2">{{ $winner->general_position }}</div>
                                <div class="text-sm opacity-90 mb-4">{{ $index == 0 ? 'JUARA 1' : ($index == 1 ? 'JUARA 2' : 'JUARA 3') }}</div>
                                <div class="bg-white/20 backdrop-blur-sm rounded-lg p-4 mb-4">
                                    <div class="font-bold text-lg mb-1">{{ $winner->name }}</div>
                                    <div class="text-sm opacity-90">BIB {{ $winner->bib }}</div>
                                </div>
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <div class="bg-white/20 backdrop-blur-sm rounded-lg p-2">
                                        <div class="opacity-90">Waktu</div>
                                        <div class="font-bold">{{ $winner->elapsed_time }}</div>
                                    </div>
                                    <div class="bg-white/20 backdrop-blur-sm rounded-lg p-2">
                                        <div class="opacity-90">Gender</div>
                                        <div class="font-bold">{{ $winner->gender == 'M' ? 'Pria' : 'Wanita' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Results Table --}}
        <div class="bg-white rounded-lg shadow-lg overflow-hidden border">
            <div class="bg-gray-50 p-6 border-b">
                <h2 class="text-xl font-bold text-gray-800">Hasil Lengkap</h2>
            </div>

            @if($results->isEmpty())
                <div class="p-16 text-center">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-700 mb-2">
                        @if(request('q'))
                            Hasil Tidak Ditemukan
                        @else
                            Hasil Belum Tersedia
                        @endif
                    </h3>
                    <p class="text-gray-500">
                        @if(request('q'))
                            Coba kata kunci pencarian yang berbeda
                        @else
                            Hasil akan diumumkan setelah event selesai
                        @endif
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-900 text-white">
                                <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">
                                    Pos
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">
                                    BIB
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">
                                    Nama Peserta
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">
                                    Gender
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">
                                    Waktu
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider">
                                    Cat Pos
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider">
                                    Kota
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($results as $row)
                                <tr class="hover:bg-gray-50 transition-colors {{ $row->general_position <= 3 && !request('q') ? 'bg-yellow-50' : '' }}">
                                    <td class="px-6 py-4 text-center">
                                        <div class="inline-flex items-center justify-center w-10 h-10
                                            {{ $row->general_position == 1 ? 'bg-yellow-100 text-yellow-800' :
                                               ($row->general_position == 2 ? 'bg-gray-200 text-gray-800' :
                                               ($row->general_position == 3 ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-600')) }}
                                            rounded-full font-bold text-sm">
                                            {{ $row->general_position }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-600 rounded-lg">
                                            <span class="text-white font-bold">{{ $row->bib }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">{{ $row->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                                            {{ $row->gender == 'M' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800' }}">
                                            {{ $row->gender == 'M' ? '👨 M' : '👩 F' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="font-bold text-blue-600 text-lg">{{ $row->elapsed_time }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                                            {{ $row->category_position }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2 text-sm text-gray-600">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            {{ $row->city ?: '-' }}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($results->hasPages())
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        {{ $results->links() }}
                    </div>
                @endif
            @endif
        </div>

        {{-- Legend --}}
        @if($results->isNotEmpty())
            <div class="mt-8 bg-white rounded-lg p-6 shadow-lg border">
                <h3 class="font-bold text-gray-800 mb-4">Keterangan:</h3>
                <div class="grid md:grid-cols-2 gap-4 text-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <span class="font-bold text-blue-800">M</span>
                        </div>
                        <span class="text-gray-700"><strong>M</strong> = Male (Pria)</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-pink-100 rounded-lg flex items-center justify-center">
                            <span class="font-bold text-pink-800">F</span>
                        </div>
                        <span class="text-gray-700"><strong>F</strong> = Female (Wanita)</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <span class="font-bold text-purple-800">CP</span>
                        </div>
                        <span class="text-gray-700"><strong>Cat Pos</strong> = Category Position (Posisi berdasarkan kategori)</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                            <span class="font-bold text-gray-800">GP</span>
                        </div>
                        <span class="text-gray-700"><strong>Gen Pos</strong> = General Position (Posisi keseluruhan)</span>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>

<style>
    /* Custom Pagination Styles */
    nav[role="navigation"] {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    nav[role="navigation"] a,
    nav[role="navigation"] span {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-weight: 600;
        transition: all 0.2s;
    }

    nav[role="navigation"] a:hover {
        background: #2563eb;
        color: white;
    }

    nav[role="navigation"] .active {
        background: #2563eb;
        color: white;
    }

    /* Input focus effect */
    input:focus {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* Smooth scroll for podium */
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .grid > div {
        animation: slideUp 0.6s ease-out forwards;
    }

    .grid > div:nth-child(2) { animation-delay: 0.1s; }
    .grid > div:nth-child(3) { animation-delay: 0.2s; }
</style>

@endsection
