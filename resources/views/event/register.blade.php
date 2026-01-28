@extends('layouts.app')

@section('content')

<div class="min-h-screen py-14 bg-gray-50">
    <div class="max-w-3xl px-4 mx-auto sm:px-6 lg:px-8">

        {{-- Back --}}
        <a href="/" class="inline-flex items-center gap-2 mb-10 text-sm font-medium text-gray-600 hover:text-gray-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke halaman event
        </a>

        {{-- Header --}}
        <div class="mb-12 text-center">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 md:text-4xl">
                Pendaftaran Peserta
            </h1>
            <p class="mt-3 text-base text-gray-600">
                {{ $event->name }}
            </p>
        </div>

        {{-- Card --}}
        <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl">

            {{-- Section Header --}}
            <div class="px-8 py-6 border-b bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">
                    Informasi Peserta
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Mohon isi data dengan benar dan sesuai identitas
                </p>
            </div>

            {{-- Form --}}
            <form
                method="POST"
                action="{{ route('event.register.store', $event->slug) }}"
                class="px-8 py-10 space-y-8"
            >
                @csrf

                {{-- Nama --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        required
                        placeholder="Nama lengkap sesuai identitas"
                        class="w-full px-4 py-3 mt-2 text-sm border border-gray-300 rounded-lg focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10"
                    >
                </div>

                {{-- Bib Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Nama di BIB <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="bib_name"
                        maxlength="12"
                        required
                        placeholder="Contoh: HARUN"
                        class="w-full px-4 py-3 mt-2 text-sm uppercase border border-gray-300 rounded-lg focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10"
                    >
                    <p class="mt-1 text-xs text-gray-500">
                        Maksimal 12 karakter (huruf besar disarankan)
                    </p>
                </div>

                {{-- Gender --}}
                <div>
                    <label class="block mb-3 text-sm font-medium text-gray-700">
                        Jenis Kelamin <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-6">
                        <label class="flex items-center gap-2 text-sm">
                            <input type="radio" name="gender" value="M" required>
                            Laki-laki
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="radio" name="gender" value="F" required>
                            Perempuan
                        </label>
                    </div>
                </div>

                {{-- Category --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Kategori Lomba <span class="text-red-500">*</span>
                    </label>
                    <select
                        name="event_category_id"
                        required
                        class="w-full px-4 py-3 mt-2 text-sm border border-gray-300 rounded-lg focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10"
                    >
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->name }} — {{ $category->distance }} km
                                ({{ $category->formatted_price }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Grid --}}
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" required placeholder="Contoh: email@example.com"
                            class="w-full px-4 py-3 mt-2 text-sm border border-gray-300 rounded-lg focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            No. HP <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="phone" required
                            placeholder="Contoh: 081234567890"
                            class="w-full px-4 py-3 mt-2 text-sm border border-gray-300 rounded-lg focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10">
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Umur <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="age" min="5" max="100" required placeholder="Contoh: 25"
                            class="w-full px-4 py-3 mt-2 text-sm border border-gray-300 rounded-lg focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Ukuran Jersey <span class="text-red-500">*</span>
                        </label>
                        <select name="jersey_size" required
                            class="w-full px-4 py-3 mt-2 text-sm border border-gray-300 rounded-lg focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10">
                            <option value="">Pilih</option>
                            <option>S</option><option>M</option><option>L</option>
                            <option>XL</option><option>XXL</option>
                        </select>
                    </div>
                </div>

                {{-- City & Community --}}
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Kota Asal <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="city" required placeholder="Contoh: Jakarta"
                            class="w-full px-4 py-3 mt-2 text-sm border border-gray-300 rounded-lg focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Komunitas / Club Lari
                        </label>
                        <input type="text" name="community"
                            placeholder="Opsional"
                            class="w-full px-4 py-3 mt-2 text-sm border border-gray-300 rounded-lg focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10">
                    </div>
                </div>

                {{-- Emergency --}}
                {{-- Emergency Contact --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Kontak Darurat <span class="text-red-500">*</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500">
                        Nama dan nomor HP yang dapat dihubungi dalam keadaan darurat
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Nama Kontak Darurat <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="emergency_contact_name" required
                            placeholder="Nama lengkap"
                            class="w-full px-4 py-3 mt-2 text-sm border border-gray-300 rounded-lg focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            No. HP Darurat <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="emergency_contact_phone" required
                            placeholder="08xxxxxxxxxx"
                            class="w-full px-4 py-3 mt-2 text-sm border border-gray-300 rounded-lg focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10">
                    </div>
                </div>

                {{-- Terms --}}
                <div class="p-5 border border-gray-200 rounded-xl bg-gray-50">
                    <label class="flex gap-3 text-sm text-gray-700">
                        <input type="checkbox" name="agreement" required class="mt-1">
                        Saya menyatakan data yang saya isi adalah benar dan bersedia
                        mengikuti syarat & ketentuan event.
                    </label>
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full px-8 py-4 text-base font-semibold text-white bg-gray-900 rounded-xl hover:bg-gray-800"
                >
                    Daftar Sekarang
                </button>

            </form>
        </div>

        {{-- Info --}}
        <div class="p-6 mt-10 text-sm text-gray-600 bg-white border border-gray-200 rounded-xl">
            <ul class="space-y-1">
                <li>• Nomor BIB akan diinformasikan setelah pendaftaran</li>
                <li>• Race pack diambil sesuai jadwal panitia</li>
                <li>• Sertifikat digital dikirim setelah event selesai</li>
            </ul>
        </div>

    </div>
</div>

@endsection
