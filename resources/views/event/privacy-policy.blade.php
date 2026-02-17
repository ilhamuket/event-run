@extends('layouts.app')

@section('content')
<div class="min-h-screen py-14 bg-red-50">
    <div class="max-w-3xl px-4 mx-auto sm:px-6 lg:px-8">

        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 mb-10 text-sm font-medium text-gray-600 hover:text-red-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali
        </a>

        <div class="p-8 bg-white border border-gray-200 shadow-sm rounded-2xl">
            <h1 class="text-2xl font-bold text-red-900">Kebijakan Perlindungan Data Pribadi</h1>
            <p class="mt-2 text-sm text-gray-500">Terakhir diperbarui: {{ now()->translatedFormat('d F Y') }}</p>

            <div class="mt-8 space-y-6 text-sm leading-relaxed text-gray-700">
                <section>
                    <h2 class="mb-2 text-base font-semibold text-gray-900">1. Data yang Kami Kumpulkan</h2>
                    <p>Kami mengumpulkan data pribadi yang Anda berikan saat mendaftar event, meliputi: nama lengkap, email, nomor telepon, jenis kelamin, umur, kota asal, ukuran jersey, komunitas, dan kontak darurat.</p>
                </section>

                <section>
                    <h2 class="mb-2 text-base font-semibold text-gray-900">2. Tujuan Penggunaan Data</h2>
                    <p>Data pribadi Anda digunakan untuk keperluan pendaftaran event, pembuatan BIB, pengelolaan race pack, pengiriman sertifikat digital, komunikasi terkait event, serta keperluan keselamatan dan keadaan darurat selama event berlangsung.</p>
                </section>

                <section>
                    <h2 class="mb-2 text-base font-semibold text-gray-900">3. Penyimpanan dan Keamanan</h2>
                    <p>Data Anda disimpan secara aman di server kami dan hanya diakses oleh pihak yang berwenang. Kami menerapkan langkah-langkah teknis dan organisasi yang wajar untuk melindungi data pribadi Anda dari akses tidak sah, kehilangan, atau penyalahgunaan.</p>
                </section>

                <section>
                    <h2 class="mb-2 text-base font-semibold text-gray-900">4. Pembagian Data kepada Pihak Ketiga</h2>
                    <p>Kami tidak menjual atau menyewakan data pribadi Anda. Data hanya dibagikan kepada pihak ketiga yang diperlukan untuk penyelenggaraan event, seperti penyedia layanan pembayaran (payment gateway) dan mitra penyelenggara.</p>
                </section>

                <section>
                    <h2 class="mb-2 text-base font-semibold text-gray-900">5. Hak Anda</h2>
                    <p>Anda berhak untuk meminta akses, perbaikan, atau penghapusan data pribadi Anda. Untuk permintaan terkait data pribadi, silakan hubungi panitia melalui kontak yang tersedia di halaman event.</p>
                </section>

                <section>
                    <h2 class="mb-2 text-base font-semibold text-gray-900">6. Perubahan Kebijakan</h2>
                    <p>Kami dapat memperbarui kebijakan ini sewaktu-waktu. Perubahan akan diinformasikan melalui halaman ini.</p>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
