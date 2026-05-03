{{--
    certificate/index.blade.php
    Halaman pengambilan sertifikat finisher Scoutrun 2026.
    User input No. BIB → data ditarik via AJAX → overlay sertifikat → tombol Print PDF
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Finisher — Scoutrun 2026</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ─── Reset & Base ─────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --maroon:      #6B1118;
            --maroon-dark: #4A0B10;
            --maroon-light:#8B1A23;
            --gold:        #C9A84C;
            --white:       #FFFFFF;
            --gray-50:     #F9FAFB;
            --gray-100:    #F3F4F6;
            --gray-400:    #9CA3AF;
            --gray-600:    #4B5563;
            --gray-900:    #111827;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--gray-50);
            min-height: 100vh;
            color: var(--gray-900);
        }

        /* ─── Search Screen ─────────────────────────────────────────── */
        #search-screen {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background:
                radial-gradient(ellipse 80% 60% at 50% -10%, rgba(107,17,24,0.18) 0%, transparent 70%),
                var(--gray-50);
        }

        .search-card {
            width: 100%;
            max-width: 480px;
            background: var(--white);
            border-radius: 20px;
            padding: 2.5rem 2rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,.07), 0 20px 60px -10px rgba(107,17,24,.12);
        }

        .brand-badge {
            display: flex;
            align-items: center;
            gap: .6rem;
            margin-bottom: 2rem;
        }

        .brand-badge-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
            background: var(--maroon);
        }

        .brand-badge span {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.1rem;
            letter-spacing: .1em;
            color: var(--maroon);
        }

        .search-title {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 2.4rem;
            line-height: 1.05;
            color: var(--maroon-dark);
            margin-bottom: .5rem;
        }

        .search-subtitle {
            font-size: .875rem;
            color: var(--gray-600);
            margin-bottom: 2rem;
        }

        .input-group {
            display: flex;
            gap: .75rem;
            margin-bottom: 1rem;
        }

        .bib-input {
            flex: 1;
            padding: .875rem 1.25rem;
            font-size: 1.1rem;
            font-weight: 600;
            border: 2px solid var(--gray-100);
            border-radius: 12px;
            outline: none;
            transition: border-color .2s;
            letter-spacing: .05em;
        }

        .bib-input::placeholder { font-weight: 400; letter-spacing: 0; }
        .bib-input:focus { border-color: var(--maroon); }

        .btn-search {
            padding: .875rem 1.5rem;
            background: var(--maroon);
            color: var(--white);
            border: none;
            border-radius: 12px;
            font-size: .9rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s, transform .1s;
            white-space: nowrap;
        }

        .btn-search:hover  { background: var(--maroon-dark); }
        .btn-search:active { transform: scale(.97); }
        .btn-search:disabled { opacity: .6; cursor: not-allowed; }

        .error-msg {
            display: none;
            font-size: .8rem;
            color: #DC2626;
            margin-top: .5rem;
            padding: .6rem 1rem;
            background: #FEF2F2;
            border-radius: 8px;
        }

        .info-text {
            margin-top: 1.5rem;
            font-size: .78rem;
            color: var(--gray-400);
            text-align: center;
            line-height: 1.6;
        }

        /* ─── Certificate Screen ─────────────────────────────────────── */
        #cert-screen {
            display: none;
            min-height: 100vh;
            padding: 2rem 1rem 4rem;
            background:
                radial-gradient(ellipse 80% 60% at 50% -10%, rgba(107,17,24,.18) 0%, transparent 70%),
                var(--gray-50);
        }

        .cert-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 900px;
            margin: 0 auto 1.5rem;
            gap: 1rem;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .55rem 1.1rem;
            border: 2px solid var(--maroon);
            color: var(--maroon);
            background: transparent;
            border-radius: 10px;
            font-size: .85rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s, color .2s;
            text-decoration: none;
        }

        .btn-back:hover { background: var(--maroon); color: var(--white); }

        .btn-print {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .65rem 1.4rem;
            background: var(--maroon);
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-size: .9rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s, transform .1s;
        }

        .btn-print:hover  { background: var(--maroon-dark); }
        .btn-print:active { transform: scale(.97); }

        /* ─── Certificate Wrapper ────────────────────────────────────── */
        .cert-wrapper {
            max-width: 900px;
            margin: 0 auto;
            position: relative;
        }

        /* Container yang menjaga rasio gambar asli (portrait ~794x1123 — A4 ish) */
        .cert-container {
            position: relative;
            width: 100%;
            /* Rasio sertifikat kamu: tinggi ÷ lebar × 100 — sesuaikan kalau perlu */
            padding-bottom: 141.5%;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,.25);
        }

        .cert-bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: top;
            display: block;
        }

        /* ─── Overlay Data ───────────────────────────────────────────── */
        /* Semua posisi dalam % relatif terhadap cert-container */

        /* Nama peserta — area merah gelap di tengah */
        .cert-name {
            position: absolute;
            top: 30%;           /* ✅ sudah pas — dikalibrasi user */
            left: 5%;
            width: 90%;
            height: 10%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;

            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(1rem, 5vw, 2.8rem);
            color: var(--white);
            letter-spacing: .08em;
            line-height: 1.1;

            overflow: hidden;
            word-break: break-word;
            padding: 0 1rem;
        }

        /*
         * ─── 4 Field data — masing-masing absolute ───────────────────
         * Tiap field punya top sendiri sehingga tidak bergantung satu sama lain.
         * Jarak antar field di sertifikat ≈ 6.7%
         * → Kalau mau geser semua sekaligus, cukup tambah/kurangi semua top sama rata.
         */
        .cert-fields {
            position: absolute;
            inset: 0;
            pointer-events: none;
        }

        .cert-field {
            position: absolute;
            left: 47%;
            width: 47%;
            height: 5.5%;

            display: flex;
            align-items: center;
            justify-content: center;

            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(.75rem, 2.8vw, 1.55rem);
            color: var(--white);
            letter-spacing: .06em;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            padding: 0 .75rem;
        }

        /* ── Top tiap field (% dari tinggi cert-container) ── */
        #cert-bib      { top: 63%;   }   /* No. BIB      */
        #cert-category { top: 69.7%; }   /* Kategori     */
        #cert-time     { top: 76.4%; }   /* Waktu Finish */
        #cert-position { top: 83.1%; }   /* Posisi       */

        /* ─── Loading Spinner ────────────────────────────────────────── */
        .spinner {
            display: inline-block;
            width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,.4);
            border-top-color: var(--white);
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* ─── PRINT styles ───────────────────────────────────────────── */
        @media print {
            @page {
                size: A4 portrait;
                margin: 0;
            }

            body * { visibility: hidden; }

            #cert-screen,
            #cert-screen * { visibility: visible; }

            #cert-screen {
                position: fixed;
                inset: 0;
                padding: 0;
                background: none;
                display: block !important;
            }

            .cert-topbar   { display: none !important; }
            .cert-wrapper  { max-width: 100%; }
            .cert-container {
                border-radius: 0;
                box-shadow: none;
                /* Paksa full page untuk print */
                padding-bottom: 0;
                height: 100vh;
            }

            .cert-bg {
                position: absolute;
                inset: 0;
                width: 100%;
                height: 100%;
            }
        }
    </style>
</head>
<body>

{{-- ══════════════════ SEARCH SCREEN ══════════════════ --}}
<div id="search-screen">
    <div class="search-card">
        <div class="brand-badge">
            <div class="brand-badge-dot"></div>
            <span>SCOUTRUN 2026 · KWARCAB BERAU</span>
        </div>

        <h1 class="search-title">Finisher<br>Certificate</h1>
        <p class="search-subtitle">
            Masukkan nomor BIB kamu untuk mengunduh sertifikat finisher.
        </p>

        <div class="input-group">
            <input
                id="bib-input"
                class="bib-input"
                type="text"
                inputmode="numeric"
                placeholder="No. BIB (contoh: 1234)"
                maxlength="10"
                autofocus
            >
            <button id="btn-search" class="btn-search">
                Cari
            </button>
        </div>

        <div id="error-msg" class="error-msg"></div>

        <p class="info-text">
            Sertifikat tersedia untuk peserta yang sudah menyelesaikan lomba<br>
            dan pembayaran terverifikasi. Hubungi panitia jika ada kendala.
        </p>
    </div>
</div>

{{-- ══════════════════ CERTIFICATE SCREEN ══════════════════ --}}
<div id="cert-screen">
    <div class="cert-topbar">
        <button class="btn-back" id="btn-back">
            ← Cari BIB lain
        </button>
        <button class="btn-print" id="btn-print">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Download / Print PDF
        </button>
    </div>

    <div class="cert-wrapper">
        <div class="cert-container">
            {{-- Background gambar sertifikat --}}
            <img
                class="cert-bg"
                src="{{ asset('assets/images/serti.png') }}"
                alt="Sertifikat Finisher Scoutrun 2026"
            >

            {{-- Overlay: Nama peserta --}}
            <div class="cert-name" id="cert-name">-</div>

            {{-- Overlay: 4 field data --}}
            <div class="cert-fields">
                <div class="cert-field" id="cert-bib">-</div>
                <div class="cert-field" id="cert-category">-</div>
                <div class="cert-field" id="cert-time">-</div>
                <div class="cert-field" id="cert-position">-</div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const searchScreen = document.getElementById('search-screen');
    const certScreen   = document.getElementById('cert-screen');
    const bibInput     = document.getElementById('bib-input');
    const btnSearch    = document.getElementById('btn-search');
    const btnBack      = document.getElementById('btn-back');
    const btnPrint     = document.getElementById('btn-print');
    const errorMsg     = document.getElementById('error-msg');

    const certName     = document.getElementById('cert-name');
    const certBib      = document.getElementById('cert-bib');
    const certCategory = document.getElementById('cert-category');
    const certTime     = document.getElementById('cert-time');
    const certPosition = document.getElementById('cert-position');

    /* ── Helpers ── */
    function showError(msg) {
        errorMsg.textContent = msg;
        errorMsg.style.display = 'block';
    }

    function clearError() {
        errorMsg.style.display = 'none';
        errorMsg.textContent   = '';
    }

    function setLoading(loading) {
        if (loading) {
            btnSearch.disabled    = true;
            btnSearch.innerHTML   = '<span class="spinner"></span>';
        } else {
            btnSearch.disabled    = false;
            btnSearch.textContent = 'Cari';
        }
    }

    /* Truncate teks yang terlalu panjang untuk field sempit */
    function safeText(text, maxLen = 30) {
        if (!text || text === '-') return '-';
        text = String(text).trim();
        return text.length > maxLen ? text.substring(0, maxLen) + '…' : text;
    }

    /* ── Search ── */
    async function doSearch() {
        const bib = bibInput.value.trim();
        if (!bib) {
            showError('Silakan masukkan nomor BIB terlebih dahulu.');
            bibInput.focus();
            return;
        }

        clearError();
        setLoading(true);

        try {
            const res  = await fetch(`{{ route('certificate.lookup') }}?bib=${encodeURIComponent(bib)}`);
            const json = await res.json();

            if (!json.success) {
                showError(json.message || 'BIB tidak ditemukan.');
                setLoading(false);
                return;
            }

            const p = json.participant;

            /* Isi overlay sertifikat */
            certName.textContent     = p.display_name || '-';
            certBib.textContent      = p.bib           || '-';
            certCategory.textContent = safeText(p.category, 25);
            certTime.textContent     = p.finish_time   || '-';
            certPosition.textContent = p.general_position || '-';

            /* Auto-fit font size nama kalau terlalu panjang */
            autoFitName();

            /* Tampilkan layar sertifikat */
            searchScreen.style.display = 'none';
            certScreen.style.display   = 'block';
            window.scrollTo(0, 0);

        } catch (err) {
            showError('Terjadi kesalahan koneksi. Silakan coba lagi.');
            console.error(err);
        } finally {
            setLoading(false);
        }
    }

    /* ── Auto-fit nama ── */
    function autoFitName() {
        const el = certName;
        const name = el.textContent;
        const len  = name.length;

        // Kurangi font-size secara proporsional untuk nama panjang
        // clamp sudah di CSS, tapi kita bantu dengan scaling
        if (len > 30) {
            el.style.fontSize = 'clamp(.8rem, 3.2vw, 1.8rem)';
        } else if (len > 20) {
            el.style.fontSize = 'clamp(.9rem, 3.8vw, 2.2rem)';
        } else {
            el.style.fontSize = ''; // reset ke CSS default
        }
    }

    /* ── Print ── */
    function doPrint() {
        window.print();
    }

    /* ── Back ── */
    function doBack() {
        certScreen.style.display   = 'none';
        searchScreen.style.display = 'flex';
        bibInput.value = '';
        clearError();
        bibInput.focus();
    }

    /* ── Events ── */
    btnSearch.addEventListener('click', doSearch);
    btnBack.addEventListener('click', doBack);
    btnPrint.addEventListener('click', doPrint);

    bibInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') doSearch();
    });

    // Hanya terima angka di input BIB
    bibInput.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
})();
</script>
</body>
</html>
