@extends('layouts.app')

@section('content')

{{-- HERO SECTION --}}
<section class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800">
    {{-- Animated Background Shapes --}}
    <div class="absolute inset-0 overflow-hidden opacity-20">
        <div class="absolute -top-1/2 -right-1/2 w-full h-full bg-blue-400 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-1/2 -left-1/2 w-full h-full bg-indigo-400 rounded-full blur-3xl"></div>
    </div>

    <div class="relative max-w-6xl mx-auto px-4 py-20 md:py-32">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            {{-- Text Content --}}
            <div class="text-white space-y-6">
                <div class="inline-block px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full text-sm font-medium border border-white/20">
                    <svg class="inline w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                    {{ \Carbon\Carbon::parse($event->start_time)->translatedFormat('d F Y') }}
                </div>

                <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold leading-tight">
                    {{ $event->name }}
                </h1>

                <div class="flex flex-wrap items-center gap-4 text-base md:text-lg text-gray-200">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ \Carbon\Carbon::parse($event->start_time)->format('H:i') }} WIB
                    </div>
                    <span class="text-gray-400 hidden sm:inline">•</span>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="line-clamp-1">{{ $event->location_name }}</span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 pt-6">
                    <a href="#register"
                       class="px-8 py-4 bg-white text-blue-600 rounded-lg font-bold tracking-wide text-center hover:bg-gray-100 transition-all shadow-lg">
                        DAFTAR SEKARANG
                    </a>
                    <a href="#about"
                       class="px-8 py-4 bg-white/10 backdrop-blur-sm text-white rounded-lg font-bold tracking-wide border-2 border-white/30 hover:bg-white/20 transition-all text-center">
                        PELAJARI LEBIH LANJUT
                    </a>
                </div>
            </div>

            {{-- Poster Image --}}
            <div class="relative order-first md:order-last">
                @if($event->poster)
                    <div class="relative group">
                        <div class="absolute -inset-4 bg-gradient-to-r from-blue-400 to-indigo-400 rounded-2xl blur-2xl opacity-50 group-hover:opacity-75 transition-opacity"></div>
                        <img src="{{ asset('storage/'.$event->poster) }}"
                             alt="{{ $event->name }}"
                             class="relative rounded-2xl shadow-2xl w-full object-cover aspect-[3/4] border-4 border-white/10">
                    </div>
                @else
                    <div class="aspect-[3/4] bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center">
                        <svg class="w-32 h-32 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Wave Divider --}}
    <div class="absolute bottom-0 left-0 w-full">
        <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
            <path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="#F9FAFB"/>
        </svg>
    </div>
</section>

{{-- STICKY NAVIGATION --}}
<nav class="sticky top-[64px] z-40 bg-white shadow-sm border-b">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex justify-center gap-1 overflow-x-auto py-3 no-scrollbar">
            <a href="#about" class="px-4 md:px-6 py-2 text-sm font-medium text-gray-600 hover:text-blue-600 hover:bg-gray-50 rounded-lg transition-all whitespace-nowrap">
                Tentang
            </a>
            <a href="#categories" class="px-4 md:px-6 py-2 text-sm font-medium text-gray-600 hover:text-blue-600 hover:bg-gray-50 rounded-lg transition-all whitespace-nowrap">
                Kategori
            </a>
            <a href="#route" class="px-4 md:px-6 py-2 text-sm font-medium text-gray-600 hover:text-blue-600 hover:bg-gray-50 rounded-lg transition-all whitespace-nowrap">
                Rute
            </a>
            <a href="#location" class="px-4 md:px-6 py-2 text-sm font-medium text-gray-600 hover:text-blue-600 hover:bg-gray-50 rounded-lg transition-all whitespace-nowrap">
                Lokasi
            </a>
            <a href="#register" class="px-4 md:px-6 py-2 text-sm font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all whitespace-nowrap">
                Daftar
            </a>
        </div>
    </div>
</nav>

{{-- ABOUT SECTION --}}
<section id="about" class="py-16 md:py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4">
        <div class="text-center mb-12">
            <span class="text-blue-600 font-bold text-sm tracking-wider uppercase">Tentang Event</span>
            <h2 class="text-3xl md:text-4xl font-bold mt-3 mb-6">Informasi Lengkap</h2>
            <div class="w-20 h-1 bg-blue-600 mx-auto rounded-full"></div>
        </div>

        <div class="bg-gray-50 rounded-2xl p-6 md:p-12 border">
            <p class="text-base md:text-lg leading-relaxed text-gray-700">
                {{ $event->description }}
            </p>
        </div>
    </div>
</section>

{{-- CATEGORIES SECTION --}}
<section id="categories" class="py-16 md:py-20 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-12 md:mb-16">
            <span class="text-blue-600 font-bold text-sm tracking-wider uppercase">Pilihan Lomba</span>
            <h2 class="text-3xl md:text-4xl font-bold mt-3 mb-6">Kategori Event</h2>
            <div class="w-20 h-1 bg-blue-600 mx-auto rounded-full"></div>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- 5K --}}
            <div class="group bg-white rounded-2xl p-6 md:p-8 border hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                <div class="w-16 h-16 bg-gradient-to-br from-green-400 to-green-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <span class="text-3xl font-bold text-white">5</span>
                </div>
                <h3 class="text-2xl md:text-3xl font-bold mb-3">5K Run</h3>
                <p class="text-gray-600 mb-6 text-sm md:text-base">Kategori untuk pelari pemula yang ingin merasakan pengalaman lomba lari.</p>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs md:text-sm text-gray-500">
                    <span>• Jarak: 5 Km</span>
                    <span>• Semua Usia</span>
                </div>
            </div>

            {{-- 10K --}}
            <div class="group bg-white rounded-2xl p-6 md:p-8 border hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <span class="text-3xl font-bold text-white">10</span>
                </div>
                <h3 class="text-2xl md:text-3xl font-bold mb-3">10K Run</h3>
                <p class="text-gray-600 mb-6 text-sm md:text-base">Kategori untuk pelari dengan pengalaman menengah yang mencari tantangan.</p>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs md:text-sm text-gray-500">
                    <span>• Jarak: 10 Km</span>
                    <span>• Intermediate</span>
                </div>
            </div>

            {{-- Half Marathon --}}
            <div class="group bg-white rounded-2xl p-6 md:p-8 border hover:shadow-lg transition-all duration-300 hover:-translate-y-1 sm:col-span-2 lg:col-span-1">
                <div class="w-16 h-16 bg-gradient-to-br from-orange-400 to-red-500 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <span class="text-3xl font-bold text-white">21</span>
                </div>
                <h3 class="text-2xl md:text-3xl font-bold mb-3">Half Marathon</h3>
                <p class="text-gray-600 mb-6 text-sm md:text-base">Kategori untuk pelari advanced yang siap menghadapi tantangan jarak jauh.</p>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs md:text-sm text-gray-500">
                    <span>• Jarak: 21 Km</span>
                    <span>• Advanced</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ACTION BUTTONS --}}
<section class="py-12 md:py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <div class="bg-gradient-to-r from-gray-900 to-gray-800 rounded-2xl p-6 md:p-12">
            <div class="grid md:grid-cols-2 gap-8 items-center">
                <div class="text-white text-center md:text-left">
                    <h3 class="text-2xl md:text-3xl font-bold mb-4">Siap Untuk Berlari?</h3>
                    <p class="text-gray-300 text-base md:text-lg">
                        Bergabunglah dengan ribuan pelari lainnya dalam event lari skala nasional ini!
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-3 md:gap-4">
                    <a href="{{ route('event.participants', $event->slug) }}"
                       class="bg-white text-gray-900 px-4 py-4 rounded-xl font-bold text-center hover:bg-gray-100 transition-all hover:-translate-y-1 shadow-lg text-sm md:text-base">
                        <svg class="w-5 h-5 md:w-6 md:h-6 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span class="text-xs md:text-sm">Daftar Peserta</span>
                    </a>

                    <a href="{{ route('event.results', $event->slug) }}"
                       class="bg-yellow-500 text-white px-4 py-4 rounded-xl font-bold text-center hover:bg-yellow-600 transition-all hover:-translate-y-1 shadow-lg text-sm md:text-base">
                        <svg class="w-5 h-5 md:w-6 md:h-6 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                        <span class="text-xs md:text-sm">Race Result</span>
                    </a>

                    @if($event->strava_route_url)
                    <a href="{{ $event->strava_route_url }}" target="_blank"
                       class="bg-orange-500 text-white px-4 py-4 rounded-xl font-bold text-center hover:bg-orange-600 transition-all hover:-translate-y-1 shadow-lg text-sm md:text-base">
                        <svg class="w-5 h-5 md:w-6 md:h-6 mx-auto mb-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-5.599l2.836 5.598h4.172L10.463 0L0 23.62h6.121"/>
                        </svg>
                        <span class="text-xs md:text-sm">Rute Strava</span>
                    </a>
                    @endif

                    @if($event->instagram_url)
                    <a href="{{ $event->instagram_url }}" target="_blank"
                       class="bg-gradient-to-br from-purple-500 via-pink-500 to-orange-500 text-white px-4 py-4 rounded-xl font-bold text-center hover:shadow-xl transition-all hover:-translate-y-1 text-sm md:text-base">
                        <svg class="w-5 h-5 md:w-6 md:h-6 mx-auto mb-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                        <span class="text-xs md:text-sm">Instagram</span>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ROUTE SECTION --}}
<section id="route" class="py-16 md:py-20 bg-gradient-to-br from-gray-900 to-gray-800 text-white">
    <div class="max-w-5xl mx-auto px-4 text-center">
        <span class="text-yellow-400 font-bold text-sm tracking-wider uppercase">Jalur Lomba</span>
        <h2 class="text-3xl md:text-4xl font-bold mt-3 mb-6">Peta Rute</h2>
        <div class="w-20 h-1 bg-blue-600 mx-auto rounded-full mb-8"></div>

        <p class="text-lg md:text-xl text-gray-300 mb-8 max-w-2xl mx-auto">
            Pelajari rute lomba melalui Strava untuk persiapan yang lebih baik. Ketahui setiap tikungan, tanjakan, dan turunan!
        </p>

        @if($event->strava_route_url)
            <a href="{{ $event->strava_route_url }}" target="_blank"
               class="inline-flex items-center gap-3 px-6 md:px-8 py-4 bg-orange-500 text-white rounded-lg font-bold hover:bg-orange-600 transition-all hover:shadow-lg text-sm md:text-base">
                <svg class="w-5 h-5 md:w-6 md:h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-5.599l2.836 5.598h4.172L10.463 0L0 23.62h6.121"/>
                </svg>
                Lihat Rute di Strava
            </a>
        @else
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 border border-white/20">
                <p class="text-gray-300">Rute akan segera diumumkan. Stay tuned!</p>
            </div>
        @endif
    </div>
</section>

{{-- REGISTRATION CTA --}}
<section id="register" class="py-16 md:py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4">
        <div class="relative bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl overflow-hidden shadow-xl">
            {{-- Decorative Elements --}}
            <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-10 rounded-full -mr-32 -mt-32"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-black opacity-10 rounded-full -ml-48 -mb-48"></div>

            <div class="relative px-6 md:px-16 py-12 md:py-16 text-center text-white">
                <div class="inline-block px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full text-xs md:text-sm font-bold mb-6">
                    🎉 PENDAFTARAN DIBUKA
                </div>

                <h2 class="text-3xl md:text-4xl font-bold mb-6">
                    Daftarkan Dirimu Sekarang!
                </h2>

                <p class="text-lg md:text-xl text-white/90 mb-8 md:mb-10 max-w-2xl mx-auto leading-relaxed">
                    Jangan lewatkan kesempatan untuk menjadi bagian dari <strong>{{ $event->name }}</strong>. Slot terbatas!
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="{{ route('event.register', $event->slug) }}"
                       class="w-full sm:w-auto px-8 md:px-10 py-4 md:py-5 bg-white text-blue-600 rounded-lg font-bold text-base md:text-lg tracking-wide hover:bg-gray-100 shadow-lg transition-all">
                        DAFTAR SEKARANG
                    </a>
                    <a href="#about"
                       class="w-full sm:w-auto px-8 md:px-10 py-4 md:py-5 bg-transparent text-white rounded-lg font-bold text-base md:text-lg tracking-wide border-2 border-white hover:bg-white/10 transition-all">
                        PELAJARI DULU
                    </a>
                </div>

                <div class="mt-8 md:mt-12 flex flex-col sm:flex-row flex-wrap justify-center gap-4 md:gap-8 text-xs md:text-sm">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 md:w-5 md:h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Sertifikat Digital
                    </div>
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 md:w-5 md:h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Race Pack Menarik
                    </div>
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 md:w-5 md:h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Medali Finisher
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- LOCATION SECTION --}}
<section id="location" class="py-16 md:py-20 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-12">
            <span class="text-blue-600 font-bold text-sm tracking-wider uppercase">Temukan Kami</span>
            <h2 class="text-3xl md:text-4xl font-bold mt-3 mb-6">Lokasi Event</h2>
            <div class="w-20 h-1 bg-blue-600 mx-auto rounded-full"></div>
        </div>

        <div class="bg-white rounded-2xl overflow-hidden shadow-lg border">
            <div class="aspect-video sm:aspect-[16/9] w-full">
                <iframe
                    width="100%"
                    height="100%"
                    frameborder="0"
                    style="border:0"
                    src="https://maps.google.com/maps?q={{ $event->latitude }},{{ $event->longitude }}&z=15&output=embed"
                    allowfullscreen>
                </iframe>
            </div>
            <div class="p-6 md:p-8 bg-gray-50">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg md:text-xl font-bold mb-2">{{ $event->location_name }}</h3>
                        <p class="text-sm md:text-base text-gray-600">
                            Pastikan kamu tiba 30 menit sebelum waktu start untuk pengambilan race pack dan pemanasan.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    /* Hide scrollbar for Chrome, Safari and Opera */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    /* Hide scrollbar for IE, Edge and Firefox */
    .no-scrollbar {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
    }

    /* Line clamp utility */
    .line-clamp-1 {
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

@endsection
