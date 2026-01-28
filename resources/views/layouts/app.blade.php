<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name', 'Event') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Tailwind --}}
    @vite('resources/css/app.css')

    <style>
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body class="text-gray-900 bg-gray-50">

    {{-- HEADER --}}
    <header class="sticky top-0 z-50 bg-white">
        <div class="flex items-center justify-between max-w-6xl px-4 py-4 mx-auto">
            <a href="/" class="text-lg font-bold">
                {{ config('app.name', 'Event') }}
            </a>

            {{-- Desktop Menu --}}
            <nav class="hidden gap-6 text-sm md:flex">
                <a href="/" class="transition hover:text-blue-600">Beranda</a>
                <a href="#about" class="transition hover:text-blue-600">Tentang</a>
                <a href="#categories" class="transition hover:text-blue-600">Kategori</a>
                <a href="#schedule" class="transition hover:text-blue-600">Jadwal</a>
                <a href="#register" class="transition hover:text-blue-600">Daftar</a>
                <a href="#contact" class="transition hover:text-blue-600">Kontak</a>
            </nav>

            {{-- Mobile Menu Button --}}
            <button id="menuBtn" class="md:hidden">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        {{-- Mobile Menu --}}
        <div id="mobileMenu" class="hidden border-t md:hidden">
            <nav class="px-4 py-2 space-y-1">
                <a href="/" class="block py-2 transition hover:text-blue-600">Beranda</a>
                <a href="#about" class="block py-2 transition hover:text-blue-600">Tentang</a>
                <a href="#categories" class="block py-2 transition hover:text-blue-600">Kategori</a>
                <a href="#schedule" class="block py-2 transition hover:text-blue-600">Jadwal</a>
                <a href="#register" class="block py-2 transition hover:text-blue-600">Daftar</a>
                <a href="#contact" class="block py-2 transition hover:text-blue-600">Kontak</a>
            </nav>
        </div>
    </header>

    {{-- CONTENT --}}
    <main>
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="mt-20 bg-white border-t">
        <div class="max-w-6xl px-4 py-6 mx-auto text-sm text-center text-gray-500">
            © {{ date('Y') }} {{ config('app.name', 'Event') }}. All rights reserved.
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        const menuBtn = document.getElementById('menuBtn');
        const mobileMenu = document.getElementById('mobileMenu');

        menuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // Close mobile menu when clicking a link
        mobileMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.add('hidden');
            });
        });
    </script>

</body>
</html>
