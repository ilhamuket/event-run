@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Back Button --}}
        <a href="/" class="inline-flex items-center gap-2 text-gray-600 hover:text-blue-600 mb-8 transition-colors group">
            <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Event
        </a>

        {{-- Header --}}
        <div class="text-center mb-10">
            <div class="inline-block px-4 py-2 bg-blue-600 text-white rounded-full text-sm font-bold mb-4">
                📝 FORM PENDAFTARAN
            </div>
            <h1 class="text-3xl md:text-4xl font-bold mb-3">Daftar Sekarang</h1>
            <p class="text-gray-600 text-lg">{{ $event->name }}</p>
        </div>

        {{-- Registration Form --}}
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden border">
            {{-- Form Header --}}
            <div class="bg-gray-50 p-6 border-b">
                <h2 class="text-xl font-bold text-gray-800 mb-2">Informasi Peserta</h2>
                <p class="text-sm text-gray-600">Pastikan semua data yang Anda masukkan sudah benar</p>
            </div>

            {{-- Form Body --}}
            <form method="POST" class="p-8 space-y-6">
                @csrf

                {{-- Full Name --}}
                <div class="space-y-2">
                    <label for="name" class="block text-sm font-bold text-gray-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Nama Lengkap
                        <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        required
                        placeholder="Masukkan nama lengkap sesuai KTP"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-600 focus:outline-none transition-colors"
                    >
                    <p class="text-xs text-gray-500">Nama akan tertera pada sertifikat dan race result</p>
                </div>

                {{-- Gender --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Jenis Kelamin
                        <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="gender" value="M" required class="peer hidden">
                            <div class="p-4 border-2 border-gray-200 rounded-lg text-center peer-checked:border-blue-600 peer-checked:bg-blue-50 transition-all hover:border-gray-300">
                                <div class="text-3xl mb-2">👨</div>
                                <div class="font-bold text-sm">Laki-laki</div>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="gender" value="F" required class="peer hidden">
                            <div class="p-4 border-2 border-gray-200 rounded-lg text-center peer-checked:border-pink-600 peer-checked:bg-pink-50 transition-all hover:border-gray-300">
                                <div class="text-3xl mb-2">👩</div>
                                <div class="font-bold text-sm">Perempuan</div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Category --}}
                <div class="space-y-2">
                    <label for="category" class="block text-sm font-bold text-gray-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                        Kategori Lomba
                        <span class="text-red-500">*</span>
                    </label>
                    <select
                        name="category"
                        id="category"
                        required
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-600 focus:outline-none transition-colors appearance-none bg-white cursor-pointer"
                        style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27currentColor%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3e%3cpolyline points=%276 9 12 15 18 9%27%3e%3c/polyline%3e%3c/svg%3e'); background-repeat: no-repeat; background-position: right 1rem center; background-size: 1.2em; padding-right: 3rem;"
                    >
                        <option value="">Pilih Kategori</option>
                        <option value="5K">🏃‍♂️ 5K Run - Pemula</option>
                        <option value="10K">🏃 10K Run - Intermediate</option>
                        <option value="21K">🏃‍♀️ Half Marathon - Advanced</option>
                        <option value="Umum">👥 Kategori Umum</option>
                    </select>
                    <p class="text-xs text-gray-500">Pilih kategori sesuai dengan kemampuan Anda</p>
                </div>

                {{-- City --}}
                <div class="space-y-2">
                    <label for="city" class="block text-sm font-bold text-gray-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Kota Asal
                    </label>
                    <input
                        type="text"
                        id="city"
                        name="city"
                        placeholder="Contoh: Jakarta, Bandung, Surabaya"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-600 focus:outline-none transition-colors"
                    >
                </div>

                {{-- Terms & Conditions --}}
                <div class="bg-gray-50 rounded-lg p-6 border-2 border-gray-200">
                    <label class="flex items-start gap-3 cursor-pointer group">
                        <input
                            type="checkbox"
                            required
                            class="mt-1 w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-600 cursor-pointer"
                        >
                        <span class="text-sm text-gray-700">
                            Saya menyatakan bahwa data yang saya berikan adalah benar dan saya telah membaca
                            <a href="#" class="text-blue-600 font-bold hover:underline">syarat dan ketentuan</a>
                            yang berlaku untuk event ini.
                        </span>
                    </label>
                </div>

                {{-- Submit Button --}}
                <div class="pt-4">
                    <button
                        type="submit"
                        class="w-full px-8 py-4 bg-blue-600 text-white rounded-lg font-bold text-lg tracking-wide hover:bg-blue-700 hover:shadow-lg transition-all flex items-center justify-center gap-3"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Daftar Sekarang
                    </button>
                </div>
            </form>
        </div>

        {{-- Info Box --}}
        <div class="mt-8 bg-blue-50 border-2 border-blue-200 rounded-lg p-6">
            <div class="flex gap-4">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-blue-900 mb-2">Informasi Penting</h3>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• Nomor BIB akan dikirimkan via email setelah pendaftaran</li>
                        <li>• Race pack dapat diambil H-1 atau pada hari event</li>
                        <li>• Pastikan datang 30 menit sebelum waktu start</li>
                        <li>• Sertifikat digital akan dikirim maksimal 7 hari setelah event</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    /* Custom radio button animation */
    input[type="radio"]:checked + div {
        transform: scale(1.02);
    }

    /* Form input focus effect */
    input:focus, select:focus {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* Checkbox custom style */
    input[type="checkbox"]:checked {
        background-color: #2563eb;
        border-color: #2563eb;
    }
</style>

@endsection
