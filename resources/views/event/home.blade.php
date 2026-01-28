@extends('layouts.app')

@section('content')

{{-- HERO SECTION WITH FULL WIDTH SLIDER --}}
<section class="relative overflow-hidden">
    {{-- Full Width Slider Container --}}
    <div class="hero-slider">
        @forelse($event->heroImages as $index => $heroImage)
        <div class="hero-slide {{ $index === 0 ? 'active' : '' }}">
            <img src="{{ asset('storage/' . $heroImage->image_path) }}"
                 alt="{{ $event->name }} - Slide {{ $index + 1 }}"
                 class="slide-image">
            <div class="slide-overlay"></div>
        </div>
        @empty
        {{-- Fallback jika tidak ada hero images --}}
        <div class="hero-slide active">
            <img src="https://images.unsplash.com/photo-1452626038306-9aae5e071dd3?w=1920&h=1080&fit=crop"
                 alt="{{ $event->name }}"
                 class="slide-image">
            <div class="slide-overlay"></div>
        </div>
        @endforelse
    </div>

    {{-- Navigation Arrows --}}
    @if($event->heroImages->count() > 1)
    <button class="hero-nav-btn prev-btn">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>
    <button class="hero-nav-btn next-btn">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </button>

    {{-- Dots Indicator --}}
    <div class="hero-dots">
        @foreach($event->heroImages as $index => $heroImage)
        <span class="hero-dot {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}"></span>
        @endforeach
    </div>
    @endif
</section>

{{-- ABOUT SECTION - IMPROVED --}}
<section id="about" class="py-16 bg-white md:py-20">
    <div class="max-w-6xl px-4 mx-auto">
        <div class="mb-12 text-center">
            <span class="text-sm font-bold tracking-wider text-gray-600 uppercase">Tentang Event</span>
            <h2 class="mt-3 mb-6 text-3xl font-bold text-gray-900 md:text-4xl">Informasi Lengkap</h2>
            <div class="w-20 h-1 mx-auto bg-gray-900 rounded-full"></div>
        </div>

        {{-- Banner & Description Grid --}}
        <div class="mb-16 about-grid">
            {{-- Left: Banner Image --}}
            @if($event->banner_image)
            <div class="about-image-container">
                <img src="{{ asset('storage/' . $event->banner_image) }}"
                     alt="{{ $event->name }} Banner">
            </div>
            @endif

            {{-- Right: Description --}}
            <div class="about-content">
                <div>
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 mb-4 text-xs font-semibold text-gray-700 bg-gray-100 rounded-full uppercase tracking-wide">
                        {{ $event->name }}
                    </span>

                    <div class="prose-custom">
                        <div class="about-text">
                            {!! nl2br(e($event->description)) !!}
                        </div>
                    </div>
                </div>


            </div>
        </div>

       {{-- YouTube Video Section --}}
        @php
            function youtubeEmbedUrl(?string $url): ?string
            {
                if (!$url) {
                    return null;
                }

                preg_match(
                    '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i',
                    $url,
                    $matches
                );

                return isset($matches[1])
                    ? 'https://www.youtube.com/embed/' . $matches[1]
                    : null;
            }

            $embedUrl = youtubeEmbedUrl($event->youtube_url);
        @endphp

        @if($embedUrl)
        <div class="overflow-hidden shadow-xl bg-gradient-to-br from-gray-900 to-gray-800 rounded-2xl">
            <div class="p-6 md:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="flex items-center justify-center w-12 h-12 bg-red-600 rounded-xl">
                        <svg class="text-white w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-400">Tonton Highlight Event</p>
                        <h3 class="text-xl font-bold text-white md:text-2xl">Video Dokumentasi</h3>
                    </div>
                </div>

                <div class="relative overflow-hidden shadow-2xl aspect-video rounded-xl">
                    <iframe
                        class="absolute inset-0 w-full h-full"
                        src="{{ $embedUrl }}"
                        title="YouTube video player"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen>
                    </iframe>
                </div>
            </div>
        </div>
        @endif
    </div>
</section>

{{-- CATEGORIES SECTION --}}
@if($event->categories->count() > 0)
<section id="categories" class="py-16 md:py-20 bg-gray-50">
    <div class="max-w-6xl px-4 mx-auto">
        <div class="mb-12 text-center md:mb-16">
            <span class="text-sm font-bold tracking-wider text-gray-600 uppercase">Pilihan Lomba</span>
            <h2 class="mt-3 mb-6 text-3xl font-bold text-gray-900 md:text-4xl">Kategori Event</h2>
            <div class="w-20 h-1 mx-auto bg-gray-900 rounded-full"></div>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($event->categories as $category)
            <div class="p-6 transition-all duration-300 bg-white border border-gray-200 group rounded-2xl md:p-8 hover:shadow-lg hover:-translate-y-1 hover:border-gray-900 {{ $loop->count == 3 && $loop->index == 2 ? 'sm:col-span-2 lg:col-span-1' : '' }}">
                <div class="flex items-center justify-center w-16 h-16 mb-6 transition-transform bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl group-hover:scale-110">
                    <span class="text-3xl font-bold text-white">{{ explode(' ', $category->distance)[0] }}</span>
                </div>
                <h3 class="mb-3 text-2xl font-bold text-gray-900 md:text-3xl">{{ $category->name }}</h3>
                <p class="mb-6 text-sm text-gray-600 md:text-base">{{ $category->description }}</p>
                <div class="flex flex-col gap-2 text-xs text-gray-500 sm:flex-row sm:items-center sm:justify-between md:text-sm">
                    <span>• Jarak: {{ $category->distance }}</span>
                    <span>• {{ $category->level }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ACTION BUTTONS WITH COUNTDOWN --}}
<section id="schedule" class="py-12 md:py-16 bg-gradient-to-b from-white to-gray-50">
    <div class="max-w-6xl px-4 mx-auto">
        {{-- Countdown Timer --}}
        <div class="mb-8">
            <div class="relative p-8 overflow-hidden shadow-2xl bg-gradient-to-br from-gray-800 via-gray-900 to-black rounded-2xl md:p-12">
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute top-0 right-0 w-64 h-64 -mt-32 -mr-32 bg-white rounded-full"></div>
                    <div class="absolute bottom-0 left-0 -mb-48 -ml-48 bg-white rounded-full w-96 h-96"></div>
                </div>

                <div class="relative z-10">
                    <div class="mb-8 text-center">
                        <div class="inline-flex items-center gap-2 px-4 py-2 mb-4 text-sm font-bold text-white rounded-full bg-white/20 backdrop-blur-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            EVENT DIMULAI DALAM
                        </div>
                        <h3 class="mb-2 text-2xl font-bold text-white md:text-3xl">Hitung Mundur Event</h3>
                        <p class="text-gray-300">{{ \Carbon\Carbon::parse($event->start_time)->translatedFormat('l, d F Y - H:i') }} WIB</p>
                    </div>

                    <div class="grid max-w-4xl grid-cols-2 gap-4 mx-auto md:grid-cols-4">
                        <div class="countdown-item">
                            <div class="countdown-box">
                                <span id="days" class="countdown-number">00</span>
                            </div>
                            <span class="countdown-label">Hari</span>
                        </div>
                        <div class="countdown-item">
                            <div class="countdown-box">
                                <span id="hours" class="countdown-number">00</span>
                            </div>
                            <span class="countdown-label">Jam</span>
                        </div>
                        <div class="countdown-item">
                            <div class="countdown-box">
                                <span id="minutes" class="countdown-number">00</span>
                            </div>
                            <span class="countdown-label">Menit</span>
                        </div>
                        <div class="countdown-item">
                            <div class="countdown-box">
                                <span id="seconds" class="countdown-number">00</span>
                            </div>
                            <span class="countdown-label">Detik</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="overflow-hidden bg-white border border-gray-100 shadow-xl rounded-2xl">
            <div class="p-6 md:p-10">
                <div class="grid items-center gap-8 mb-8 md:grid-cols-2">
                    <div class="text-center md:text-left">
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 md:text-3xl">
                            Siap Untuk Berlari?
                        </h3>
                        <p class="text-base leading-relaxed text-gray-600 md:text-lg">
                            Bergabunglah dengan <span class="font-bold text-gray-900">ribuan pelari</span> lainnya dalam event lari skala nasional ini!
                        </p>
                    </div>

                    <div class="flex flex-col justify-center gap-3 sm:flex-row md:justify-end">
                        <a href="#register"
                           class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-900 text-white rounded-xl font-bold hover:bg-gray-800 transition-all hover:shadow-lg hover:-translate-y-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Daftar Sekarang
                        </a>
                        <a href="#about"
                           class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200 transition-all hover:shadow-lg hover:-translate-y-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Info Lengkap
                        </a>
                    </div>
                </div>

                {{-- Action Grid --}}
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <a href="{{ route('event.participants', $event->slug) }}" class="action-card group">
                        <div class="action-icon bg-gradient-to-br from-gray-700 to-gray-900">
                            <svg class="w-6 h-6 md:w-7 md:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <h4 class="action-title">Daftar Peserta</h4>
                        <p class="action-desc">Lihat peserta terdaftar</p>
                    </a>

                    <a href="{{ route('event.results', $event->slug) }}" class="action-card group">
                        <div class="action-icon bg-gradient-to-br from-yellow-500 to-orange-500">
                            <svg class="w-6 h-6 md:w-7 md:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                        </div>
                        <h4 class="action-title">Race Result</h4>
                        <p class="action-desc">Hasil & ranking lomba</p>
                    </a>

                    @if($event->strava_route_url)
                    <a href="{{ $event->strava_route_url }}" target="_blank" class="action-card group">
                        <div class="action-icon bg-gradient-to-br from-orange-500 to-red-500">
                            <svg class="w-6 h-6 md:w-7 md:h-7" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-5.599l2.836 5.598h4.172L10.463 0L0 23.62h6.121"/>
                            </svg>
                        </div>
                        <h4 class="action-title">Rute Strava</h4>
                        <p class="action-desc">Pelajari jalur lomba</p>
                    </a>
                    @endif

                    @if($event->instagram_url)
                    <a href="{{ $event->instagram_url }}" target="_blank" class="action-card group">
                        <div class="action-icon bg-gradient-to-br from-purple-500 via-pink-500 to-orange-500">
                            <svg class="w-6 h-6 md:w-7 md:h-7" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </div>
                        <h4 class="action-title">Instagram</h4>
                        <p class="action-desc">Follow untuk update</p>
                    </a>
                    @endif
                </div>
            </div>

            {{-- Stats Bar --}}
            <div class="px-6 py-6 border-t border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                <div class="grid max-w-3xl grid-cols-3 gap-4 mx-auto">
                    <div class="text-center">
                        <div class="flex items-center justify-center gap-2 mb-1">
                            <svg class="w-5 h-5 text-gray-900" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-xs font-semibold text-gray-600 md:text-sm">Sertifikat Digital</p>
                        </div>
                    </div>
                    <div class="text-center border-gray-300 border-x">
                        <div class="flex items-center justify-center gap-2 mb-1">
                            <svg class="w-5 h-5 text-gray-900" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-xs font-semibold text-gray-600 md:text-sm">Race Pack</p>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="flex items-center justify-center gap-2 mb-1">
                            <svg class="w-5 h-5 text-gray-900" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-xs font-semibold text-gray-600 md:text-sm">Medali Finisher</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ROUTE SECTION --}}
@if($event->strava_route_url)
<section id="route" class="py-16 text-white md:py-20 bg-gradient-to-br from-gray-900 to-gray-800">
    <div class="max-w-5xl px-4 mx-auto text-center">
        <span class="text-sm font-bold tracking-wider text-gray-400 uppercase">Jalur Lomba</span>
        <h2 class="mt-3 mb-6 text-3xl font-bold md:text-4xl">Peta Rute</h2>
        <div class="w-20 h-1 mx-auto mb-8 rounded-full bg-gray-50"></div>

        <p class="max-w-2xl mx-auto mb-8 text-lg text-gray-300 md:text-xl">
            Pelajari rute lomba melalui Strava untuk persiapan yang lebih baik. Ketahui setiap tikungan, tanjakan, dan turunan!
        </p>

        <a href="{{ $event->strava_route_url }}" target="_blank"
           class="inline-flex items-center gap-3 px-6 py-4 text-sm font-bold text-white transition-all bg-orange-500 rounded-lg md:px-8 hover:bg-orange-600 hover:shadow-lg md:text-base">
            <svg class="w-5 h-5 md:w-6 md:h-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-5.599l2.836 5.598h4.172L10.463 0L0 23.62h6.121"/>
            </svg>
            Lihat Rute di Strava
        </a>
    </div>
</section>
@endif

{{-- RACE PACK SLIDER SECTION --}}
@if($event->racepackItems->count() > 0)
<section class="py-16 md:py-20 bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="max-w-6xl px-4 mx-auto">
        <div class="mb-12 text-center">
            <span class="text-sm font-bold tracking-wider text-gray-600 uppercase">Hadiah Untuk Peserta</span>
            <h2 class="mt-3 mb-6 text-3xl font-bold text-gray-900 md:text-4xl">Race Pack Eksklusif</h2>
            <div class="w-20 h-1 mx-auto mb-4 bg-gray-900 rounded-full"></div>
            <p class="max-w-2xl mx-auto text-gray-600">
                Setiap peserta akan mendapatkan race pack menarik yang berisi berbagai item eksklusif
            </p>
        </div>

        <div class="relative">
            <div class="overflow-hidden racepack-slider-wrapper rounded-2xl">
                <div class="flex transition-transform duration-500 ease-out racepack-slider">
                    @foreach($event->racepackItems as $item)
                    <div class="flex-shrink-0 w-full racepack-slide">
                        <div class="grid items-center gap-8 p-8 bg-white border border-gray-200 shadow-xl md:grid-cols-2 rounded-2xl md:p-12">
                            <div class="order-2 md:order-1">
                                <div class="inline-block px-4 py-2 mb-4 text-sm font-bold text-gray-900 bg-gray-100 rounded-full">
                                    {{ $item->item_number }}
                                </div>
                                <h3 class="mb-4 text-2xl font-bold text-gray-900 md:text-3xl">{{ $item->item_name }}</h3>
                                <p class="mb-6 leading-relaxed text-gray-600">
                                    {{ $item->description }}
                                </p>
                                @if($item->features && count($item->features) > 0)
                                <ul class="space-y-3">
                                    @foreach($item->features as $feature)
                                    <li class="flex items-center gap-3 text-gray-700">
                                        <svg class="flex-shrink-0 w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $feature }}
                                    </li>
                                    @endforeach
                                </ul>
                                @endif
                            </div>
                            <div class="order-1 md:order-2">
                                <div class="relative group">
                                    <div class="absolute transition-opacity -inset-4 bg-gradient-to-r from-gray-200 to-gray-300 rounded-2xl blur-xl opacity-30 group-hover:opacity-50"></div>
                                    <img src="{{ asset('storage/' . $item->image_path) }}"
                                         alt="{{ $item->item_name }}"
                                         class="relative object-cover w-full border border-gray-200 shadow-xl rounded-2xl aspect-square">
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            @if($event->racepackItems->count() > 1)
            <button class="racepack-nav-btn racepack-prev-btn">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <button class="racepack-nav-btn racepack-next-btn">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

            <div class="racepack-dots">
                @foreach($event->racepackItems as $index => $item)
                <span class="racepack-dot {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}"></span>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</section>
@endif

{{-- COURSE MAP SECTION --}}
@if($event->categories->count() > 0)
<section id="course-map" class="py-16 bg-white md:py-20">
    <div class="max-w-6xl px-4 mx-auto">
        <div class="mb-12 text-center">
            <span class="text-sm font-bold tracking-wider text-gray-600 uppercase">Peta Rute Lengkap</span>
            <h2 class="mt-3 mb-6 text-3xl font-bold text-gray-900 md:text-4xl">Course Map</h2>
            <div class="w-20 h-1 mx-auto mb-4 bg-gray-900 rounded-full"></div>
            <p class="max-w-2xl mx-auto text-gray-600">
                Pelajari rute lomba untuk setiap kategori. Ketahui medan, elevasi, dan titik-titik penting sepanjang jalur
            </p>
        </div>

        {{-- Category Tabs --}}
        <div class="flex flex-wrap justify-center gap-3 mb-10">
            @foreach($event->categories as $index => $category)
            <button class="category-tab {{ $index === 0 ? 'active' : '' }}" data-category="{{ $category->slug }}">
                <div class="flex items-center gap-2">
                    <div class="flex items-center justify-center w-8 h-8 bg-gray-900 rounded-lg">
                        <span class="text-sm font-bold text-white">{{ explode(' ', $category->distance)[0] }}</span>
                    </div>
                    <span class="font-bold">{{ $category->name }}</span>
                </div>
            </button>
            @endforeach
        </div>

        {{-- Map Content --}}
        <div class="course-map-content">
            @foreach($event->categories as $index => $category)
            <div class="map-category {{ $index === 0 ? 'active' : '' }}" data-category="{{ $category->slug }}">
                <div class="p-6 border border-gray-200 bg-gray-50 rounded-2xl md:p-8">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="flex items-center justify-center w-12 h-12 bg-gray-900 rounded-xl">
                            <span class="text-2xl font-bold text-white">{{ explode(' ', $category->distance)[0] }}</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">{{ $category->name }} Course</h3>
                            <p class="font-medium text-gray-700">Jarak: {{ $category->distance }}</p>
                        </div>
                    </div>

                    @if($category->course_map_image)
                    <div class="mb-6 overflow-hidden bg-white border border-gray-200 shadow-lg rounded-xl">
                        <img src="{{ asset('storage/' . $category->course_map_image) }}"
                             alt="{{ $category->name }} Course Map"
                             class="w-full h-auto">
                    </div>
                    @endif

                    <div class="grid gap-4 md:grid-cols-3">
                        @if($category->elevation)
                        <div class="p-4 bg-white border border-gray-200 shadow-sm rounded-xl">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="flex items-center justify-center w-10 h-10 bg-green-100 rounded-lg">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500">Elevasi</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $category->elevation }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($category->terrain)
                        <div class="p-4 bg-white border border-gray-200 shadow-sm rounded-xl">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="flex items-center justify-center w-10 h-10 bg-gray-100 rounded-lg">
                                    <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500">Medan</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $category->terrain }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($category->cut_off_time)
                        <div class="p-4 bg-white border border-gray-200 shadow-sm rounded-xl">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="flex items-center justify-center w-10 h-10 bg-orange-100 rounded-lg">
                                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500">Cut Off</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $category->cut_off_time }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- REGISTRATION CTA --}}
<section id="register" class="py-16 bg-white md:py-20">
    <div class="max-w-5xl px-4 mx-auto">
        <div class="relative overflow-hidden shadow-xl bg-gradient-to-br from-gray-900 to-gray-800 rounded-2xl">
            <div class="absolute top-0 right-0 w-64 h-64 -mt-32 -mr-32 bg-white rounded-full opacity-10"></div>
            <div class="absolute bottom-0 left-0 -mb-48 -ml-48 bg-black rounded-full w-96 h-96 opacity-10"></div>

            <div class="relative px-6 py-12 text-center text-white md:px-16 md:py-16">
                <div class="inline-block px-4 py-2 mb-6 text-xs font-bold rounded-full bg-white/20 backdrop-blur-sm md:text-sm">
                    🎉 PENDAFTARAN DIBUKA
                </div>

                <h2 class="mb-6 text-3xl font-bold md:text-4xl">
                    Daftarkan Dirimu Sekarang!
                </h2>

                <p class="max-w-2xl mx-auto mb-8 text-lg leading-relaxed text-gray-200 md:text-xl md:mb-10">
                    Jangan lewatkan kesempatan untuk menjadi bagian dari <strong>{{ $event->name }}</strong>. Slot terbatas!
                </p>

                <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <a href="{{ route('event.register', $event->slug) }}"
                       class="w-full px-8 py-4 text-base font-bold tracking-wide text-gray-900 transition-all bg-white rounded-lg shadow-lg sm:w-auto md:px-10 md:py-5 md:text-lg hover:bg-gray-100">
                        DAFTAR SEKARANG
                    </a>
                    <a href="#about"
                       class="w-full px-8 py-4 text-base font-bold tracking-wide text-white transition-all bg-transparent border-2 border-white rounded-lg sm:w-auto md:px-10 md:py-5 md:text-lg hover:bg-white/10">
                        PELAJARI DULU
                    </a>
                </div>

                <div class="flex flex-col flex-wrap justify-center gap-4 mt-8 text-xs md:mt-12 sm:flex-row md:gap-8 md:text-sm">
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
    <div class="max-w-6xl px-4 mx-auto">
        <div class="mb-12 text-center">
            <span class="text-sm font-bold tracking-wider text-gray-600 uppercase">Temukan Kami</span>
            <h2 class="mt-3 mb-6 text-3xl font-bold text-gray-900 md:text-4xl">Lokasi Event</h2>
            <div class="w-20 h-1 mx-auto bg-gray-900 rounded-full"></div>
        </div>

        <div class="overflow-hidden bg-white border border-gray-200 shadow-lg rounded-2xl">
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
                    <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 bg-gray-900 md:w-12 md:h-12 rounded-xl">
                        <svg class="w-5 h-5 text-white md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="mb-2 text-lg font-bold text-gray-900 md:text-xl">{{ $event->location_name }}</h3>
                        <p class="text-sm text-gray-600 md:text-base">
                            Pastikan kamu tiba 30 menit sebelum waktu start untuk pengambilan race pack dan pemanasan.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="contact" class="py-16 bg-white md:py-20">
    <div class="max-w-6xl px-4 mx-auto">
        {{-- Header --}}
        <div class="mb-12 text-center">
            <span class="text-sm font-bold tracking-wider text-gray-600 uppercase">
                Kontak
            </span>
            <h2 class="mt-3 mb-6 text-3xl font-bold text-gray-900 md:text-4xl">
                Hubungi Panitia
            </h2>
            <div class="w-20 h-1 mx-auto bg-gray-900 rounded-full"></div>
        </div>

        {{-- Content --}}
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {{-- WhatsApp --}}
            <a href="https://wa.me/6281234567890"
               target="_blank"
               class="flex items-start gap-4 p-6 transition border border-gray-200 bg-gray-50 rounded-2xl hover:bg-white hover:shadow-lg hover:border-gray-900">
                <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 bg-green-500 rounded-xl">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20.52 3.48A11.78 11.78 0 0012.06 0C5.4 0 .02 5.38 0 12.02c0 2.12.56 4.2 1.62 6.03L0 24l6.13-1.6a12.01 12.01 0 005.93 1.51h.01c6.63 0 12.02-5.38 12.02-12.02a11.9 11.9 0 00-3.57-8.41z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">WhatsApp</h3>
                    <p class="text-sm text-gray-600">Admin Event</p>
                    <p class="mt-1 font-medium text-gray-800">+62 812-3456-7890</p>
                </div>
            </a>

            {{-- Email --}}
            <a href="mailto:event@yourdomain.com"
               class="flex items-start gap-4 p-6 transition border border-gray-200 bg-gray-50 rounded-2xl hover:bg-white hover:shadow-lg hover:border-gray-900">
                <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 bg-gray-900 rounded-xl">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M2 4h20v16H2V4zm18 2l-8 5-8-5v2l8 5 8-5V6z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Email</h3>
                    <p class="text-sm text-gray-600">Resmi Panitia</p>
                    <p class="mt-1 font-medium text-gray-800">event@yourdomain.com</p>
                </div>
            </a>

            {{-- Office / Info --}}
            <div class="flex items-start gap-4 p-6 border border-gray-200 bg-gray-50 rounded-2xl">
                <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 bg-red-600 rounded-xl">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Informasi</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        Jam operasional panitia:<br>
                        Senin – Jumat, 09.00 – 17.00 WIB
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Include styles and scripts from previous version --}}
@include('event.partials.styles')
@include('event.partials.scripts')

@endsection
