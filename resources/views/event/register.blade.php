@extends('layouts.app')

@section('content')

<div class="min-h-screen py-12 bg-gray-50">
    <div class="max-w-2xl px-4 mx-auto sm:px-6 lg:px-8">

        {{-- Back Button --}}
        <a href="/" class="inline-flex items-center gap-2 mb-8 text-gray-600 transition-colors hover:text-blue-600 group">
            <svg class="w-5 h-5 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Event
        </a>

        {{-- Header --}}
        <div class="mb-10 text-center">
            <div class="inline-block px-4 py-2 mb-4 text-sm font-bold text-white bg-blue-600 rounded-full">
                📝 FORM PENDAFTARAN
            </div>
            <h1 class="mb-3 text-3xl font-bold md:text-4xl">Daftar Sekarang</h1>
            <p class="text-lg text-gray-600">{{ $event->name }}</p>
        </div>

        {{-- Registration Form --}}
        <div class="overflow-hidden bg-white border shadow-lg rounded-2xl">
            {{-- Form Header --}}
            <div class="p-6 border-b bg-gray-50">
                <h2 class="mb-2 text-xl font-bold text-gray-800">Informasi Peserta</h2>
                <p class="text-sm text-gray-600">Pastikan semua data yang Anda masukkan sudah benar</p>
            </div>

            {{-- Form Body --}}
            <form method="POST" class="p-8 space-y-6">
                @csrf

                {{-- Full Name --}}
                <div class="space-y-2">
                    <label for="name" class="flex items-center block gap-2 text-sm font-bold text-gray-700">
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
                        class="w-full px-4 py-3 transition-colors border-2 border-gray-200 rounded-lg focus:border-blue-600 focus:outline-none"
                    >
                    <p class="text-xs text-gray-500">Nama akan tertera pada sertifikat dan race result</p>
                </div>

                {{-- Bib Name --}}
                <div class="space-y-2">
                    <label for="bib_name" class="flex items-center gap-2 text-sm font-bold text-gray-700">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7"/>
                        </svg>
                        Nama di BIB
                        <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="bib_name"
                        name="bib_name"
                        required
                        maxlength="12"
                        placeholder="Contoh: HARUN"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-600 focus:outline-none"
                        value="{{ old('bib_name') }}"
                    >
                    <p class="text-xs text-gray-500">
                        Maksimal 12 karakter, huruf besar disarankan
                    </p>
                </div>

                {{-- Gender --}}
                <div class="space-y-2">
                    <label class="flex items-center block gap-2 text-sm font-bold text-gray-700">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Jenis Kelamin
                        <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="gender" value="M" required class="hidden peer">
                            <div class="p-4 text-center transition-all border-2 border-gray-200 rounded-lg peer-checked:border-blue-600 peer-checked:bg-blue-50 hover:border-gray-300">
                                <div class="mb-2 text-3xl">👨</div>
                                <div class="text-sm font-bold">Laki-laki</div>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="gender" value="F" required class="hidden peer">
                            <div class="p-4 text-center transition-all border-2 border-gray-200 rounded-lg peer-checked:border-pink-600 peer-checked:bg-pink-50 hover:border-gray-300">
                                <div class="mb-2 text-3xl">👩</div>
                                <div class="text-sm font-bold">Perempuan</div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Category --}}
                <div class="space-y-2">
                    <label for="category" class="flex items-center block gap-2 text-sm font-bold text-gray-700">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                        Kategori Lomba
                        <span class="text-red-500">*</span>
                    </label>
                    <select
                        name="event_category_id"
                        id="category"
                        required
                        class="w-full px-4 py-3 transition-colors border-2 border-gray-200 rounded-lg focus:border-blue-600 focus:outline-none"
                    >
                        <option value="">Pilih Kategori</option>

                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">
                                🏃 {{ $category->name }}
                                ({{ $category->distance }} km • {{ $category->formatted_price }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500">Pilih kategori sesuai dengan kemampuan Anda</p>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-bold">Email *</label>
                    <input type="email" name="email" required
                        class="w-full px-4 py-3 border-2 rounded-lg">
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-bold">No. HP *</label>
                    <input type="text" name="phone" required
                        placeholder="08xxxxxxxxxx"
                        class="w-full px-4 py-3 border-2 rounded-lg">
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-bold">Umur *</label>
                    <input type="number" name="age" min="5" max="100" required
                        class="w-full px-4 py-3 border-2 rounded-lg">
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-bold">Ukuran Jersey *</label>
                    <select name="jersey_size" required
                        class="w-full px-4 py-3 border-2 rounded-lg">
                        <option value="">Pilih</option>
                        <option>S</option><option>M</option>
                        <option>L</option><option>XL</option><option>XXL</option>
                    </select>
                </div>

                {{-- City --}}
                <div class="space-y-2">
                    <label for="city" class="flex items-center block gap-2 text-sm font-bold text-gray-700">
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
                        class="w-full px-4 py-3 transition-colors border-2 border-gray-200 rounded-lg focus:border-blue-600 focus:outline-none"
                    >
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-bold">Alamat</label>
                    <textarea name="address"
                        class="w-full px-4 py-3 border-2 rounded-lg"></textarea>
                </div>

                {{-- Community --}}
                <div class="space-y-2">
                    <label for="community" class="flex items-center gap-2 text-sm font-bold text-gray-700">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M9 20H4v-2a3 3 0 015.356-1.857M15 11a3 3 0 110-6 3 3 0 010 6zM9 11a3 3 0 110-6 3 3 0 010 6z"/>
                        </svg>
                        Komunitas / Club Lari
                    </label>

                    <input
                        type="text"
                        id="community"
                        name="community"
                        value="{{ old('community') }}"
                        placeholder="Contoh: Jakarta Runners, Bandung Run Club"
                        class="w-full px-4 py-3 transition-colors border-2 border-gray-200 rounded-lg focus:border-blue-600 focus:outline-none"
                    >

                    <p class="text-xs text-gray-500">
                        Opsional. Kosongkan jika mendaftar secara individu.
                    </p>
                </div>


                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <input name="emergency_contact_name" placeholder="Nama Kontak Darurat" required
                        class="px-4 py-3 border-2 rounded-lg">
                    <input name="emergency_contact_phone" placeholder="No. HP Darurat" required
                        class="px-4 py-3 border-2 rounded-lg">
                </div>

                <div class="space-y-2">
                    <label class="font-bold">Riwayat Penyakit?</label>
                    <select name="has_comorbid" class="w-full px-4 py-3 border-2 rounded-lg">
                        <option value="0">Tidak</option>
                        <option value="1">Ya</option>
                    </select>
                </div>

                <div class="space-y-2">
                    <textarea name="comorbid_details"
                        placeholder="Jika ada, jelaskan"
                        class="w-full px-4 py-3 border-2 rounded-lg"></textarea>
                </div>

                {{-- Terms & Conditions --}}
                <div class="p-6 border-2 border-gray-200 rounded-lg bg-gray-50">
                    <label class="flex items-start gap-3 cursor-pointer group">
                        <input
                            type="checkbox"
                            required
                            class="w-5 h-5 mt-1 text-blue-600 border-gray-300 rounded cursor-pointer focus:ring-blue-600"
                        >
                        <span class="text-sm text-gray-700">
                            Saya menyatakan bahwa data yang saya berikan adalah benar dan saya telah membaca
                            <a href="#" class="font-bold text-blue-600 hover:underline">syarat dan ketentuan</a>
                            yang berlaku untuk event ini.
                        </span>
                    </label>
                </div>

                {{-- Submit Button --}}
                <div class="pt-4">
                    <button
                        type="submit"
                        class="flex items-center justify-center w-full gap-3 px-8 py-4 text-lg font-bold tracking-wide text-white transition-all bg-blue-600 rounded-lg hover:bg-blue-700 hover:shadow-lg"
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
        <div class="p-6 mt-8 border-2 border-blue-200 rounded-lg bg-blue-50">
            <div class="flex gap-4">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="mb-2 font-bold text-blue-900">Informasi Penting</h3>
                    <ul class="space-y-1 text-sm text-blue-800">
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
