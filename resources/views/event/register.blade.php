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

        {{-- Error Alert --}}
        @if (session('error'))
            <div class="p-4 mb-6 text-sm text-red-700 bg-red-100 border border-red-200 rounded-xl">
            {{ session('error') }}
            </div>
        @endif

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
            id="registration-form"
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
                value="{{ old('name') }}"
                required
                placeholder="Nama lengkap sesuai identitas"
                class="w-full px-4 py-3 mt-2 text-sm border border-gray-300 rounded-lg focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10"
                >
                @error('name')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Bib Name --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">
                Nama di BIB <span class="text-red-500">*</span>
                </label>
                <input
                type="text"
                name="bib_name"
                value="{{ old('bib_name') }}"
                maxlength="12"
                required
                placeholder="Contoh: HARUN"
                class="w-full px-4 py-3 mt-2 text-sm uppercase border border-gray-300 rounded-lg focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10"
                >
                <p class="mt-1 text-xs text-gray-500">
                Maksimal 12 karakter (huruf besar disarankan)
                </p>
                @error('bib_name')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Gender --}}
            <div>
                <label class="block mb-3 text-sm font-medium text-gray-700">
                Jenis Kelamin <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-6">
                <label class="flex items-center gap-2 text-sm">
                    <input type="radio" name="gender" value="M" {{ old('gender') == 'M' ? 'checked' : '' }} required>
                    Laki-laki
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="radio" name="gender" value="F" {{ old('gender') == 'F' ? 'checked' : '' }} required>
                    Perempuan
                </label>
                </div>
                @error('gender')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Category --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">
                Kategori Lomba <span class="text-red-500">*</span>
                </label>
                <select
                name="event_category_id"
                id="category-select"
                required
                class="w-full px-4 py-3 mt-2 text-sm border border-gray-300 rounded-lg focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10"
                >
                <option value="">Pilih kategori</option>
                @foreach ($categories as $category)
                    <option
                    value="{{ $category->id }}"
                    data-price="{{ $category->price }}"
                    data-fee="{{ $category->fee }}"
                    data-total="{{ $category->total_price }}"
                    {{ old('event_category_id') == $category->id ? 'selected' : '' }}
                    >
                    {{ $category->name }} — {{ $category->distance }} km
                    </option>
                @endforeach
                </select>
                @error('event_category_id')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror

                {{-- Price Display --}}
                <div id="price-display" class="hidden p-4 mt-4 border border-gray-200 rounded-lg bg-gray-50">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                    <span class="text-gray-600">Biaya Pendaftaran</span>
                    <span id="base-price" class="font-medium">Rp 0</span>
                    </div>
                    <div class="flex justify-between">
                    <span class="text-gray-600">Biaya Admin (QRIS)</span>
                    <span id="fee-price" class="font-medium">Rp 0</span>
                    </div>
                    <div class="pt-2 border-t border-gray-200">
                    <div class="flex justify-between">
                        <span class="font-semibold text-gray-900">Total Pembayaran</span>
                        <span id="total-price" class="font-bold text-gray-900">Rp 0</span>
                    </div>
                    </div>
                </div>
                </div>
            </div>

            {{-- Grid --}}
            <div class="grid gap-6 md:grid-cols-2">
                <div>
                <label class="block text-sm font-medium text-gray-700">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="Contoh: email@example.com"
                    class="w-full px-4 py-3 mt-2 text-sm border border-gray-300 rounded-lg focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10">
                @error('email')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
                </div>

                <div>
                <label class="block text-sm font-medium text-gray-700">
                    No. HP <span class="text-red-500">*</span>
                </label>
                <input type="text" name="phone" value="{{ old('phone') }}" required
                    placeholder="Contoh: 081234567890"
                    class="w-full px-4 py-3 mt-2 text-sm border border-gray-300 rounded-lg focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10">
                @error('phone')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                <label class="block text-sm font-medium text-gray-700">
                    Umur <span class="text-red-500">*</span>
                </label>
                <input type="number" name="age" value="{{ old('age') }}" min="5" max="100" required placeholder="Contoh: 25"
                    class="w-full px-4 py-3 mt-2 text-sm border border-gray-300 rounded-lg focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10">
                @error('age')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
                </div>

                <div>
                <label class="block text-sm font-medium text-gray-700">
                    Ukuran Jersey <span class="text-red-500">*</span>
                </label>
                <select name="jersey_size" required
                    class="w-full px-4 py-3 mt-2 text-sm border border-gray-300 rounded-lg focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10">
                    <option value="">Pilih</option>
                    <option {{ old('jersey_size') == 'S' ? 'selected' : '' }}>S</option>
                    <option {{ old('jersey_size') == 'M' ? 'selected' : '' }}>M</option>
                    <option {{ old('jersey_size') == 'L' ? 'selected' : '' }}>L</option>
                    <option {{ old('jersey_size') == 'XL' ? 'selected' : '' }}>XL</option>
                    <option {{ old('jersey_size') == 'XXL' ? 'selected' : '' }}>XXL</option>
                </select>
                @error('jersey_size')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
                </div>
            </div>

            {{-- City & Community --}}
            <div class="grid gap-6 md:grid-cols-2">
                <div>
                <label class="block text-sm font-medium text-gray-700">
                    Kota Asal <span class="text-red-500">*</span>
                </label>
                <input type="text" name="city" value="{{ old('city') }}" required placeholder="Contoh: Jakarta"
                    class="w-full px-4 py-3 mt-2 text-sm border border-gray-300 rounded-lg focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10">
                @error('city')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
                </div>

                <div>
                <label class="block text-sm font-medium text-gray-700">
                    Komunitas / Club Lari
                </label>
                <input type="text" name="community" value="{{ old('community') }}"
                    placeholder="Opsional"
                    class="w-full px-4 py-3 mt-2 text-sm border border-gray-300 rounded-lg focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10">
                </div>
            </div>

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
                <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" required
                    placeholder="Nama lengkap"
                    class="w-full px-4 py-3 mt-2 text-sm border border-gray-300 rounded-lg focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10">
                @error('emergency_contact_name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
                </div>

                <div>
                <label class="block text-sm font-medium text-gray-700">
                    No. HP Darurat <span class="text-red-500">*</span>
                </label>
                <input type="text" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" required
                    placeholder="08xxxxxxxxxx"
                    class="w-full px-4 py-3 mt-2 text-sm border border-gray-300 rounded-lg focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10">
                @error('emergency_contact_phone')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
                </div>
            </div>

                {{-- Terms --}}
                <div class="p-5 border border-gray-200 rounded-xl bg-gray-50">
                    <label class="flex gap-3 text-sm text-gray-700">
                        <input type="checkbox" name="agreement" class="mt-1" {{ old('agreement') ? 'checked' : '' }}>
                        Saya menyatakan data yang saya isi adalah benar dan bersedia
                        mengikuti syarat & ketentuan event.
                    </label>

                    @error('agreement')
                        <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Payment Info --}}
                <div class="p-5 border border-blue-200 rounded-xl bg-blue-50">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-blue-900">Pembayaran via QRIS</p>
                            <p class="mt-1 text-sm text-blue-700">
                                Setelah mengisi formulir, Anda akan diarahkan ke halaman pembayaran QRIS.
                                Pembayaran harus diselesaikan dalam waktu 24 jam.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    id="submit-btn"
                    class="w-full px-8 py-4 text-base font-semibold text-white bg-gray-900 rounded-xl hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span id="btn-text">Lanjut ke Pembayaran</span>
                    <span id="btn-loading" class="hidden">
                        <svg class="inline w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memproses...
                    </span>
                </button>

            </form>
        </div>

        {{-- Info --}}
        <div class="p-6 mt-10 text-sm text-gray-600 bg-white border border-gray-200 rounded-xl">
            <ul class="space-y-1">
                <li>• Nomor BIB akan diinformasikan setelah pembayaran berhasil</li>
                <li>• Race pack diambil sesuai jadwal panitia</li>
                <li>• Sertifikat digital dikirim setelah event selesai</li>
                <li>• Pembayaran via QRIS mendukung semua e-wallet dan mobile banking</li>
            </ul>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category-select');
    const priceDisplay = document.getElementById('price-display');
    const basePrice = document.getElementById('base-price');
    const feePrice = document.getElementById('fee-price');
    const totalPrice = document.getElementById('total-price');
    const form = document.getElementById('registration-form');
    const submitBtn = document.getElementById('submit-btn');
    const btnText = document.getElementById('btn-text');
    const btnLoading = document.getElementById('btn-loading');

    function formatRupiah(number) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
    }

    function updatePriceDisplay() {
        const selected = categorySelect.options[categorySelect.selectedIndex];

        if (selected.value) {
            const price = parseInt(selected.dataset.price) || 0;
            const fee = parseInt(selected.dataset.fee) || 0;
            const total = parseInt(selected.dataset.total) || 0;

            basePrice.textContent = formatRupiah(price);
            feePrice.textContent = formatRupiah(fee);
            totalPrice.textContent = formatRupiah(total);
            priceDisplay.classList.remove('hidden');
        } else {
            priceDisplay.classList.add('hidden');
        }
    }

    categorySelect.addEventListener('change', updatePriceDisplay);

    // Initial check
    updatePriceDisplay();

    // Form submission
    form.addEventListener('submit', function () {
        if (!form.checkValidity()) {
            return;
        }

        submitBtn.disabled = true;
        btnText.classList.add('hidden');
        btnLoading.classList.remove('hidden');
    });

});
</script>
@endpush
