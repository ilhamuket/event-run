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
                Race Results
            </h1>
            <p class="mt-3 text-base text-gray-600">
                {{ $event->name }}
            </p>
            <p class="mt-2 text-sm text-gray-500">
                Finisher: {{ $results->total() }} peserta
            </p>
        </div>

        {{-- Search --}}
        <div class="mb-10 overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl">
            <form method="GET" class="flex flex-col gap-4 p-6 md:flex-row">
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
                    <a href="{{ route('event.results', $event->slug) }}"
                       class="px-6 py-3 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Empty --}}
        @if($results->isEmpty())
            <div class="p-16 text-center bg-white border border-gray-200 shadow-sm rounded-2xl">
                <h3 class="text-lg font-semibold text-gray-900">
                    Belum ada hasil lomba
                </h3>
                <p class="mt-2 text-sm text-gray-600">
                    Peserta belum menyelesaikan lomba
                </p>
            </div>
        @else

        {{-- Table --}}
        <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b bg-gray-50">
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-600 uppercase">
                            <th class="px-6 py-4 text-center">Rank</th>
                            <th class="px-6 py-4">Peserta</th>
                            <th class="px-6 py-4">Kategori</th>
                            <th class="px-6 py-4 text-center">Cat Rank</th>
                            <th class="px-6 py-4 text-center">Waktu</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                    @foreach($results as $index => $p)
                        <tr class="transition hover:bg-gray-50">

                            {{-- GENERAL RANK --}}
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl
                                    {{ $p->general_position <= 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-700' }}
                                    font-bold">
                                    {{ $p->general_position ?? ($results->firstItem() + $index) }}
                                </div>
                            </td>

                            {{-- PESERTA --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center justify-center w-12 h-12 bg-gray-900 rounded-xl">
                                        <span class="text-sm font-bold text-white">
                                            {{ $p->bib }}
                                        </span>
                                    </div>

                                    <div>
                                        <div class="font-semibold text-gray-900">
                                            {{ $p->display_name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $p->gender === 'M' ? 'Pria' : 'Wanita' }}
                                            · {{ $p->age ?? '-' }} th
                                            · {{ $p->city ?? '-' }}
                                        </div>
                                    </div>
                                </div>
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

                            {{-- CATEGORY RANK --}}
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 text-xs font-semibold text-blue-700 bg-blue-100 rounded-full">
                                    #{{ $p->category_position ?? '-' }}
                                </span>
                            </td>

                            {{-- ELAPSED TIME --}}
                            <td class="px-6 py-4 text-center">
                                <div class="text-lg font-bold text-gray-900">
                                    {{ $p->formatted_elapsed_time }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Finish
                                </div>
                            </td>

                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($results->hasPages())
            <div class="mt-8">
                {{ $results->links() }}
            </div>
        @endif

        @endif

    </div>
</div>

@endsection
