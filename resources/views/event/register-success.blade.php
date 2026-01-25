@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-gradient-to-br from-green-50 to-blue-50 py-20 flex items-center justify-center">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">

        {{-- Success Animation --}}
        <div class="mb-8 relative">
            <div class="w-32 h-32 mx-auto bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center shadow-2xl animate-bounce-slow">
                <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            {{-- Confetti Effect --}}
            <div class="absolute inset-0 pointer-events-none">
                <div class="confetti"></div>
                <div class="confetti"></div>
                <div class="confetti"></div>
                <div class="confetti"></div>
                <div class="confetti"></div>
            </div>
        </div>

        {{-- Success Message --}}
        <div class="bg-white rounded-2xl shadow-2xl p-10 border-4 border-green-100">
            <div class="mb-6">
                <span class="inline-block px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-bold mb-4">
                    ✅ PENDAFTARAN BERHASIL
                </span>
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    Selamat! 🎉
                </h1>
                <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                    Anda telah berhasil mendaftar untuk<br>
                    <strong class="text-blue-600">{{ $event->name }}</strong>
                </p>
            </div>

            {{-- Event Details Card --}}
            <div class="bg-gray-50 rounded-lg p-6 mb-8 border">
                <h3 class="font-bold text-gray-800 mb-4 text-lg">Detail Event</h3>
                <div class="space-y-3 text-left">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600">Tanggal & Waktu</div>
                            <div class="font-bold text-gray-900">
                                {{ \Carbon\Carbon::parse($event->start_time)->translatedFormat('d F Y, H:i') }} WIB
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600">Lokasi</div>
                            <div class="font-bold text-gray-900">{{ $event->location_name }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Next Steps --}}
            <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-6 mb-8 text-left">
                <h3 class="font-bold text-blue-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Langkah Selanjutnya
                </h3>
                <ol class="space-y-3 text-sm text-blue-900">
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold">1</span>
                        <span>Cek email Anda untuk konfirmasi pendaftaran dan nomor BIB</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
                        <span>Ambil race pack H-1 atau pada hari event di lokasi yang ditentukan</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold">3</span>
                        <span>Pastikan tiba 30 menit sebelum waktu start untuk pemanasan</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold">4</span>
                        <span>Ikuti media sosial kami untuk update terbaru tentang event</span>
                    </li>
                </ol>
            </div>

            {{-- Action Buttons --}}
            <div class="space-y-4">
                <a href="/"
                   class="block w-full px-8 py-4 bg-blue-600 text-white rounded-lg font-bold text-lg hover:bg-blue-700 hover:shadow-lg transition-all">
                    Kembali ke Beranda
                </a>
                <a href="{{ route('event.participants', $event->slug) }}"
                   class="block w-full px-8 py-4 bg-white border-2 border-gray-300 text-gray-700 rounded-lg font-bold hover:bg-gray-50 transition-all">
                    Lihat Daftar Peserta
                </a>
            </div>

            {{-- Social Share --}}
            <div class="mt-8 pt-8 border-t border-gray-200">
                <p class="text-sm text-gray-600 mb-4">Bagikan keikutsertaan Anda:</p>
                <div class="flex justify-center gap-3">
                    <button class="w-12 h-12 bg-blue-600 hover:bg-blue-700 text-white rounded-full flex items-center justify-center transition-all hover:scale-110">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </button>
                    <button class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white rounded-full flex items-center justify-center transition-all hover:scale-110">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                    </button>
                    <button class="w-12 h-12 bg-green-500 hover:bg-green-600 text-white rounded-full flex items-center justify-center transition-all hover:scale-110">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    /* Bounce Animation */
    @keyframes bounce-slow {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-20px);
        }
    }

    .animate-bounce-slow {
        animation: bounce-slow 2s ease-in-out infinite;
    }

    /* Confetti Animation */
    .confetti {
        position: absolute;
        width: 10px;
        height: 10px;
        background: #2563eb;
        animation: confetti-fall 3s ease-in-out infinite;
    }

    .confetti:nth-child(1) {
        left: 10%;
        animation-delay: 0s;
        background: #FFD700;
    }

    .confetti:nth-child(2) {
        left: 30%;
        animation-delay: 0.5s;
        background: #2563eb;
    }

    .confetti:nth-child(3) {
        left: 50%;
        animation-delay: 1s;
        background: #4CAF50;
    }

    .confetti:nth-child(4) {
        left: 70%;
        animation-delay: 1.5s;
        background: #2196F3;
    }

    .confetti:nth-child(5) {
        left: 90%;
        animation-delay: 2s;
        background: #f59e0b;
    }

    @keyframes confetti-fall {
        0% {
            top: -10%;
            opacity: 1;
            transform: rotate(0deg);
        }
        100% {
            top: 100%;
            opacity: 0;
            transform: rotate(360deg);
        }
    }
</style>

@endsection
