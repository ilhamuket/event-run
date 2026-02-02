@extends('layouts.app')

@section('title', 'Pembayaran Berhasil - ' . $event->name)

@section('content')
<div class="min-h-screen py-12 bg-gradient-to-br from-green-50 to-emerald-100">
    <div class="max-w-lg px-4 mx-auto">
        {{-- Success Icon --}}
        <div class="mb-8 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 mb-4 bg-green-500 rounded-full">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Pembayaran Berhasil!</h1>
            <p class="mt-2 text-gray-600">Terima kasih telah mendaftar</p>
        </div>

        {{-- E-Ticket Card --}}
        <div class="overflow-hidden bg-white shadow-lg rounded-2xl">
            {{-- Event Header --}}
            <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600">
                <p class="text-sm text-indigo-200">{{ $event->date?->format('d M Y') }}</p>
                <h2 class="text-xl font-bold text-white">{{ $event->name }}</h2>
            </div>

            {{-- BIB Number --}}
            <div class="px-6 py-8 text-center border-b border-gray-100">
                <p class="mb-2 text-sm text-gray-500">Nomor BIB Anda</p>
                <div class="inline-block px-8 py-4 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-xl">
                    <span class="text-4xl font-black tracking-wider text-white">
                        {{ str_pad($transaction->participant->bib, 4, '0', STR_PAD_LEFT) }}
                    </span>
                </div>
            </div>

            {{-- Participant Info --}}
            <div class="px-6 py-4">
                <h3 class="mb-4 font-semibold text-gray-900">Data Peserta</h3>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Nama</p>
                        <p class="font-medium text-gray-900">{{ $transaction->participant->name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Email</p>
                        <p class="font-medium text-gray-900">{{ $transaction->participant->email }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">No. HP</p>
                        <p class="font-medium text-gray-900">{{ $transaction->participant->phone }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Gender</p>
                        <p class="font-medium text-gray-900">{{ $transaction->participant->gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Ukuran Jersey</p>
                        <p class="font-medium text-gray-900">{{ $transaction->participant->jersey_size }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Kota</p>
                        <p class="font-medium text-gray-900">{{ $transaction->participant->city ?? '-' }}</p>
                    </div>
                    @if($transaction->participant->community)
                    <div class="col-span-2">
                        <p class="text-gray-500">Komunitas</p>
                        <p class="font-medium text-gray-900">{{ $transaction->participant->community }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Category Badge --}}
            <div class="px-6 py-4 border-t border-gray-100">
                <div class="flex items-center justify-between">
                    <span class="text-gray-500">Kategori</span>
                    <span class="px-4 py-2 font-semibold text-white rounded-lg bg-gradient-to-r from-indigo-500 to-purple-500">
                        {{ $transaction->eventCategory->name }}
                    </span>
                </div>
            </div>

            {{-- Payment Info --}}
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                <h3 class="mb-3 font-semibold text-gray-900">Detail Pembayaran</h3>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">No. Invoice</span>
                        <span class="font-mono text-gray-900">{{ $transaction->merchant_ref }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Metode</span>
                        <span class="text-gray-900">{{ $transaction->payment_name ?? 'QRIS' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Status</span>
                        <span class="font-medium text-green-600">✓ Lunas</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Tanggal Bayar</span>
                        <span class="text-gray-900">{{ $transaction->paid_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="flex justify-between pt-2 font-semibold border-t border-gray-200">
                        <span class="text-gray-900">Total</span>
                        <span class="text-indigo-600">{{ $transaction->formatted_total_amount }}</span>
                    </div>
                </div>
            </div>

            {{-- Important Notes --}}
            <div class="px-6 py-4 border-t border-gray-100">
                <h3 class="mb-3 font-semibold text-gray-900">Informasi Penting</h3>

                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-start">
                        <svg class="flex-shrink-0 w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Email konfirmasi telah dikirim ke <strong>{{ $transaction->participant->email }}</strong></span>
                    </li>
                    <li class="flex items-start">
                        <svg class="flex-shrink-0 w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Simpan screenshot halaman ini sebagai bukti pendaftaran</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="flex-shrink-0 w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Race pack dapat diambil sesuai jadwal yang akan diinformasikan</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="flex-shrink-0 w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Bawa KTP/identitas saat pengambilan race pack</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Actions --}}
        <div class="mt-6 space-y-3">
            <button onclick="window.print()"
                    class="block w-full px-4 py-3 font-medium text-center text-gray-700 transition bg-white border border-gray-300 hover:bg-gray-50 rounded-xl">
                🖨️ Cetak E-Ticket
            </button>

            <a href="{{ route('home') }}"
               class="block w-full px-4 py-3 font-medium text-center text-white transition bg-indigo-600 hover:bg-indigo-700 rounded-xl">
                Kembali ke Halaman Event
            </a>
        </div>

        {{-- Footer --}}
        <div class="mt-8 text-sm text-center text-gray-500">
            <p>Butuh bantuan? Hubungi kami di</p>
            <p class="font-medium text-gray-700">support@example.com</p>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .max-w-lg, .max-w-lg * {
            visibility: visible;
        }
        .max-w-lg {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        button, a {
            display: none !important;
        }
    }
</style>
@endpush
@endsection
