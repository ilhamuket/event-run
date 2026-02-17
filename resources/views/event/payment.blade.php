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
        <div class="overflow-hidden bg-white shadow-lg rounded-2xl" style="border: 1px solid #e5e7eb;">
            {{-- Status Badge --}}
            <div class="px-6 py-4" style="border-bottom: 1px solid #e5e7eb;">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Status Pembayaran</span>
                    @if($transaction->isPaid())
                        <span class="px-3 py-1 text-sm font-medium rounded-full" style="background: rgba(153, 27, 27, 0.1); color: #991b1b;">
                            ✓ Lunas
                        </span>
                    @elseif($transaction->isExpired())
                        <span class="px-3 py-1 text-sm font-medium text-gray-700 bg-gray-100 rounded-full">
                            Kadaluarsa
                        </span>
                    @else
                        <span class="px-3 py-1 text-sm font-medium rounded-full" style="background: rgba(153, 27, 27, 0.08); color: #991b1b;">
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
                        <div class="inline-block p-4 bg-white rounded-xl" style="border: 2px solid #e5e7eb;">
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
                                <span class="px-2 py-1 text-xs rounded" style="background: rgba(153, 27, 27, 0.06); color: #7f1d1d;">GoPay</span>
                                <span class="px-2 py-1 text-xs rounded" style="background: rgba(153, 27, 27, 0.06); color: #7f1d1d;">OVO</span>
                                <span class="px-2 py-1 text-xs rounded" style="background: rgba(153, 27, 27, 0.06); color: #7f1d1d;">DANA</span>
                                <span class="px-2 py-1 text-xs rounded" style="background: rgba(153, 27, 27, 0.06); color: #7f1d1d;">ShopeePay</span>
                            </div>
                        </div>
                    </div>

                    {{-- Countdown Timer --}}
                    <div class="p-4 mt-6 rounded-xl" style="background: rgba(153, 27, 27, 0.05);">
                        <div class="text-center">
                            <p class="text-sm" style="color: #991b1b;">Bayar sebelum:</p>
                            <p id="countdown" class="mt-1 text-2xl font-bold" style="color: #7f1d1d;">
                                --:--:--
                            </p>
                            <p class="mt-1 text-xs" style="color: #991b1b; opacity: 0.7;">
                                {{ $transaction->expired_at->format('d M Y, H:i') }} WIB
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Order Summary --}}
            <div class="px-6 py-4 bg-gray-50" style="border-top: 1px solid #e5e7eb;">
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
                    <div class="flex justify-between pt-2 text-base font-semibold" style="border-top: 1px solid #e5e7eb;">
                        <span class="text-gray-900">Total Bayar</span>
                        <span style="color: #991b1b;">{{ $transaction->formatted_total_amount }}</span>
                    </div>
                </div>
            </div>

            {{-- Instructions --}}
            <div class="px-6 py-4" style="border-top: 1px solid #e5e7eb;">
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
                <div class="px-6 py-4" style="border-top: 1px solid #e5e7eb;">
                    <a href="{{ route('event.payment.success', ['event' => $event->slug, 'ref' => $transaction->merchant_ref]) }}"
                       class="block w-full px-4 py-3 font-medium text-center text-white transition rounded-xl"
                       style="background: #991b1b;"
                       onmouseover="this.style.background='#7f1d1d'"
                       onmouseout="this.style.background='#991b1b'">
                        Lihat E-Ticket
                    </a>
                </div>
            @elseif($transaction->checkout_url)
                <div class="px-6 py-4" style="border-top: 1px solid #e5e7eb;">
                    <a href="{{ $transaction->checkout_url }}"
                       target="_blank"
                       class="block w-full px-4 py-3 font-medium text-center text-white transition rounded-xl"
                       style="background: #991b1b;"
                       onmouseover="this.style.background='#7f1d1d'"
                       onmouseout="this.style.background='#991b1b'">
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

<script>
    // Countdown Timer
    var expiredTime = {{ $transaction->expired_at->timestamp * 1000 }};
    var countdownEl = document.getElementById('countdown');

    if (countdownEl) {
        function updateCountdown() {
            var now = new Date().getTime();
            var distance = expiredTime - now;

            if (distance < 0) {
                countdownEl.textContent = 'EXPIRED';
                countdownEl.style.color = '#6b7280';
                clearInterval(countdownInterval);
                location.reload();
                return;
            }

            var hours = Math.floor(distance / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            countdownEl.textContent =
                String(hours).padStart(2, '0') + ':' +
                String(minutes).padStart(2, '0') + ':' +
                String(seconds).padStart(2, '0');
        }

        updateCountdown();
        var countdownInterval = setInterval(updateCountdown, 1000);
    }

    // Poll payment status every 5 seconds
    @if($transaction->isUnpaid())
    var statusUrl = '{{ route("event.payment.status", ["event" => $event->slug, "ref" => $transaction->merchant_ref]) }}';

    function checkPaymentStatus() {
        fetch(statusUrl)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.is_paid) {
                    window.location.href = '{{ route("event.payment.success", ["event" => $event->slug, "ref" => $transaction->merchant_ref]) }}';
                }
            })
            .catch(function(error) { console.error('Error checking status:', error); });
    }

    setInterval(checkPaymentStatus, 5000);
    @endif
</script>
@endsection
