@extends('layouts.app')

@section('title', 'Pembayaran - ' . $event->name)

@section('content')
<div class="min-h-screen py-12 bg-gray-100">
    <div class="max-w-lg px-4 mx-auto">
        {{-- Header --}}
        <div class="mb-8 text-center">
            <h1 class="text-2xl font-bold text-gray-900">Pembayaran</h1>
            <p class="mt-2 text-gray-600">{{ $event->name }}</p>
        </div>

        {{-- Payment Card --}}
        <div class="overflow-hidden bg-white shadow-lg rounded-2xl">
            {{-- Status Badge --}}
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Status Pembayaran</span>
                    @if($transaction->isPaid())
                        <span class="px-3 py-1 text-sm font-medium text-green-700 bg-green-100 rounded-full">
                            ✓ Lunas
                        </span>
                    @elseif($transaction->isExpired())
                        <span class="px-3 py-1 text-sm font-medium text-gray-700 bg-gray-100 rounded-full">
                            Kadaluarsa
                        </span>
                    @else
                        <span class="px-3 py-1 text-sm font-medium text-yellow-700 bg-yellow-100 rounded-full">
                            Menunggu Pembayaran
                        </span>
                    @endif
                </div>
            </div>

            @if($transaction->isUnpaid() && $transaction->canBePaid())
                {{-- QR Code Section --}}
                <div class="p-6">
                    <div class="text-center">
                        <p class="mb-4 text-sm text-gray-500">Scan QR code dengan aplikasi e-wallet</p>

                        {{-- QR Code Image --}}
                        <div class="inline-block p-4 bg-white border-2 border-gray-200 rounded-xl">
                            @if($transaction->qr_url)
                                <img src="{{ $transaction->qr_url }}"
                                     alt="QRIS QR Code"
                                     class="w-48 h-48 mx-auto">
                            @else
                                <div class="flex items-center justify-center w-48 h-48 bg-gray-200">
                                    <span class="text-gray-400">QR tidak tersedia</span>
                                </div>
                            @endif
                        </div>

                        {{-- Supported E-Wallets --}}
                        <div class="flex items-center justify-center mt-4 space-x-2">
                            <span class="text-xs text-gray-400">Didukung oleh:</span>
                            <div class="flex space-x-2">
                                <span class="px-2 py-1 text-xs text-gray-600 bg-gray-100 rounded">GoPay</span>
                                <span class="px-2 py-1 text-xs text-gray-600 bg-gray-100 rounded">OVO</span>
                                <span class="px-2 py-1 text-xs text-gray-600 bg-gray-100 rounded">DANA</span>
                                <span class="px-2 py-1 text-xs text-gray-600 bg-gray-100 rounded">ShopeePay</span>
                            </div>
                        </div>
                    </div>

                    {{-- Countdown Timer --}}
                    <div class="p-4 mt-6 bg-red-50 rounded-xl">
                        <div class="text-center">
                            <p class="text-sm text-red-600">Bayar sebelum:</p>
                            <p id="countdown" class="mt-1 text-2xl font-bold text-red-700">
                                --:--:--
                            </p>
                            <p class="mt-1 text-xs text-red-500">
                                {{ $transaction->expired_at->format('d M Y, H:i') }} WIB
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Order Summary --}}
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                <h3 class="mb-3 font-semibold text-gray-900">Ringkasan Pesanan</h3>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">No. Invoice</span>
                        <span class="font-mono text-gray-900">{{ $transaction->merchant_ref }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Nama</span>
                        <span class="text-gray-900">{{ $transaction->participant->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Kategori</span>
                        <span class="text-gray-900">{{ $transaction->eventCategory->name }}</span>
                    </div>
                </div>

                <hr class="my-4 border-gray-200">

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Harga Tiket</span>
                        <span class="text-gray-900">{{ $transaction->formatted_amount }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Biaya Admin</span>
                        <span class="text-gray-900">{{ $transaction->formatted_fee }}</span>
                    </div>
                    <div class="flex justify-between pt-2 text-base font-semibold border-t border-gray-200">
                        <span class="text-gray-900">Total Bayar</span>
                        <span class="text-indigo-600">{{ $transaction->formatted_total_amount }}</span>
                    </div>
                </div>
            </div>

            {{-- Instructions --}}
            <div class="px-6 py-4 border-t border-gray-100">
                <h3 class="mb-3 font-semibold text-gray-900">Cara Pembayaran</h3>
                <ol class="space-y-2 text-sm text-gray-600 list-decimal list-inside">
                    <li>Buka aplikasi e-wallet (GoPay, OVO, DANA, ShopeePay, dll)</li>
                    <li>Pilih menu "Scan" atau "Bayar dengan QR"</li>
                    <li>Arahkan kamera ke QR code di atas</li>
                    <li>Periksa detail pembayaran dan konfirmasi</li>
                    <li>Pembayaran berhasil, halaman akan otomatis terupdate</li>
                </ol>
            </div>

            {{-- Actions --}}
            @if($transaction->isPaid())
                <div class="px-6 py-4 border-t border-gray-100">
                    <a href="{{ route('event.payment.success', ['event' => $event->slug, 'ref' => $transaction->merchant_ref]) }}"
                       class="block w-full px-4 py-3 font-medium text-center text-white transition bg-green-600 hover:bg-green-700 rounded-xl">
                        Lihat E-Ticket
                    </a>
                </div>
            @elseif($transaction->checkout_url)
                <div class="px-6 py-4 border-t border-gray-100">
                    <a href="{{ $transaction->checkout_url }}"
                       target="_blank"
                       class="block w-full px-4 py-3 font-medium text-center text-white transition bg-indigo-600 hover:bg-indigo-700 rounded-xl">
                        Buka Halaman Pembayaran Tripay
                    </a>
                </div>
            @endif
        </div>

        {{-- Back Link --}}
        <div class="mt-6 text-center">
            <a href="{{ route('home') }}" class="text-sm text-gray-500 hover:text-gray-700">
                ← Kembali ke halaman event
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Countdown Timer
    const expiredTime = {{ $transaction->expired_at->timestamp * 1000 }};
    const countdownEl = document.getElementById('countdown');

    function updateCountdown() {
        const now = new Date().getTime();
        const distance = expiredTime - now;

        if (distance < 0) {
            countdownEl.textContent = 'EXPIRED';
            countdownEl.classList.add('text-gray-500');
            clearInterval(countdownInterval);
            location.reload();
            return;
        }

        const hours = Math.floor(distance / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        countdownEl.textContent =
            String(hours).padStart(2, '0') + ':' +
            String(minutes).padStart(2, '0') + ':' +
            String(seconds).padStart(2, '0');
    }

    updateCountdown();
    const countdownInterval = setInterval(updateCountdown, 1000);

    // Poll payment status every 5 seconds
    @if($transaction->isUnpaid())
    const statusUrl = '{{ route("event.payment.status", ["event" => $event->slug, "ref" => $transaction->merchant_ref]) }}';

    function checkPaymentStatus() {
        fetch(statusUrl)
            .then(response => response.json())
            .then(data => {
                if (data.is_paid) {
                    // Redirect to success page
                    window.location.href = '{{ route("event.payment.success", ["event" => $event->slug, "ref" => $transaction->merchant_ref]) }}';
                }
            })
            .catch(error => console.error('Error checking status:', error));
    }

    // Check every 5 seconds
    setInterval(checkPaymentStatus, 5000);
    @endif
</script>
@endpush
@endsection
