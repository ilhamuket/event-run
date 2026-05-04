{{--
    certificate/index.blade.php
    Halaman pengambilan sertifikat finisher Scoutrun 2026.
    User input No. BIB → data ditarik via AJAX → overlay sertifikat → tombol Print PDF

    FIXES:
    - Mobile PDF/Print: gunakan html2canvas → download PNG (bukan window.print yang kepotong)
    - Story download: export JPEG (bukan PNG) + scale max 1080px + Blob URL supaya tidak crash di mobile
    - Added: cert-cat-position field (CATEGORY POSITION) sesuai desain sertifikat
    - Format posisi: "1 / 200" (overall & category position)
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

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
            flex-wrap: wrap;
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
        .btn-print:disabled { opacity: .6; cursor: not-allowed; }

        /* ─── Story Button ───────────────────────────────────────── */
        .btn-story {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .65rem 1.4rem;
            background: var(--gold);
            color: var(--maroon-dark);
            border: none;
            border-radius: 10px;
            font-size: .9rem;
            font-weight: 700;
            cursor: pointer;
            transition: opacity .2s, transform .1s;
        }
        .btn-story:hover  { opacity: .85; }
        .btn-story:active { transform: scale(.97); }
        .btn-story:disabled { opacity: .5; cursor: not-allowed; }

        /* ─── Certificate Wrapper ────────────────────────────────────── */
        .cert-wrapper {
            max-width: 900px;
            margin: 0 auto;
            position: relative;
        }

        .cert-container {
            position: relative;
            width: 100%;
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
        .cert-name {
            position: absolute;
            top: 39%;
            left: 5%;
            width: 90%;
            height: 10%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;

            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(1rem, 5vw, 4rem);
            color: var(--white);
            letter-spacing: .08em;
            line-height: 1.1;

            overflow: hidden;
            word-break: break-word;
            padding: 0 1rem;
        }

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
            /* Sedikit diperkecil agar "1 / 200" muat tanpa ellipsis */
            font-size: clamp(.65rem, 2.4vw, 1.35rem);
            color: var(--white);
            letter-spacing: .04em;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            padding: 0 .75rem;
        }

        /* 4 field sesuai desain: COURSE, FINISH TIME, OVERALL POSITION, CATEGORY POSITION */
        #cert-category     { top: 61.7%; }
        #cert-time         { top: 67%;   }
        #cert-position     { top: 72%;   }
        #cert-cat-position { top: 77.3%; }

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

        /* ─── Story Modal ────────────────────────────────────────────── */
        #story-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.75);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        #story-modal.open { display: flex; }

        .story-modal-box {
            background: var(--white);
            border-radius: 20px;
            padding: 2rem;
            width: 100%;
            max-width: 420px;
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
            max-height: 92vh;
            overflow-y: auto;
        }

        .story-modal-title {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.6rem;
            color: var(--maroon-dark);
            line-height: 1;
        }

        .story-modal-sub {
            font-size: .8rem;
            color: var(--gray-600);
            margin-top: .3rem;
        }

        .story-upload-zone {
            border: 2px dashed var(--gray-400);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: border-color .2s, background .2s;
            position: relative;
        }
        .story-upload-zone:hover { border-color: var(--maroon); background: #fdf5f5; }
        .story-upload-zone input[type="file"] {
            position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
        }
        .story-upload-icon { font-size: 2rem; margin-bottom: .4rem; }
        .story-upload-text { font-size: .82rem; color: var(--gray-600); line-height: 1.5; }
        .story-upload-text strong { color: var(--maroon); }

        /* Preview wrapper — rasio 9:16 */
        #story-preview-wrap {
            display: none;
            position: relative;
            width: 100%;
            padding-bottom: 177.78%;
            border-radius: 12px;
            overflow: hidden;
            background: #111;
        }
        #story-preview-wrap canvas {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
        }

        .story-modal-actions {
            display: flex;
            gap: .75rem;
        }
        .btn-story-cancel {
            flex: 1;
            padding: .7rem;
            border: 2px solid var(--maroon);
            color: var(--maroon);
            background: transparent;
            border-radius: 10px;
            font-size: .85rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s;
        }
        .btn-story-cancel:hover { background: #fdf5f5; }
        .btn-story-dl {
            flex: 2;
            padding: .7rem;
            background: var(--maroon);
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-size: .85rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            transition: background .2s;
        }
        .btn-story-dl:hover { background: var(--maroon-dark); }
        .btn-story-dl:disabled { opacity: .5; cursor: not-allowed; }

        /* ─── PRINT styles (desktop only) ───────────────────────────── */
        @media print {
            @page {
                size: A4 portrait;
                margin: 0;
            }

            body * { visibility: hidden; }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            .cert-name,
            .cert-field { color: #FFFFFF !important; }

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
        <button class="btn-story" id="btn-story">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Buat Story IG
        </button>
        <button class="btn-print" id="btn-print">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            <span id="btn-print-label">Download / Print</span>
        </button>
    </div>

    <div class="cert-wrapper">
        <div class="cert-container" id="cert-container">
            {{-- Background gambar sertifikat --}}
            <img
                class="cert-bg"
                src="{{ asset('assets/images/serti2.png') }}"
                alt="Sertifikat Finisher Scoutrun 2026"
                crossorigin="anonymous"
            >

            {{-- Overlay: Nama peserta --}}
            <div class="cert-name" id="cert-name">-</div>

            {{-- Overlay: 4 field data sesuai desain sertifikat --}}
            {{-- COURSE | FINISH TIME | OVERALL POSITION | CATEGORY POSITION --}}
            <div class="cert-fields">
                <div class="cert-field" id="cert-category">-</div>
                <div class="cert-field" id="cert-time">-</div>
                <div class="cert-field" id="cert-position">-</div>
                <div class="cert-field" id="cert-cat-position">-</div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════ STORY MODAL ══════════════════ --}}
<div id="story-modal">
    <div class="story-modal-box">
        <div>
            <div class="story-modal-title">Buat Story IG</div>
            <div class="story-modal-sub">Upload foto larimu — logo &amp; data finisher otomatis di-overlay di bawah</div>
        </div>

        <div class="story-upload-zone" id="story-upload-zone">
            <input type="file" id="story-file-input" accept="image/*">
            <div class="story-upload-icon">📷</div>
            <div class="story-upload-text"><strong>Pilih foto</strong> atau drag &amp; drop<br>JPG, PNG, HEIC</div>
        </div>

        <div id="story-preview-wrap">
            <canvas id="story-canvas"></canvas>
        </div>

        <div class="story-modal-actions">
            <button class="btn-story-cancel" id="btn-story-cancel">Batal</button>
            <button class="btn-story-dl" id="btn-story-dl" disabled>
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    /* ─── Deteksi mobile ─────────────────────────────────── */
    const isMobile = /Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent);

    const searchScreen    = document.getElementById('search-screen');
    const certScreen      = document.getElementById('cert-screen');
    const bibInput        = document.getElementById('bib-input');
    const btnSearch       = document.getElementById('btn-search');
    const btnBack         = document.getElementById('btn-back');
    const btnPrint        = document.getElementById('btn-print');
    const btnPrintLabel   = document.getElementById('btn-print-label');
    const errorMsg        = document.getElementById('error-msg');

    const certName        = document.getElementById('cert-name');
    const certCategory    = document.getElementById('cert-category');
    const certTime        = document.getElementById('cert-time');
    const certPosition    = document.getElementById('cert-position');
    const certCatPosition = document.getElementById('cert-cat-position');

    /* Update label tombol print sesuai device */
    if (btnPrintLabel) {
        btnPrintLabel.textContent = isMobile ? 'Download PNG' : 'Download / Print PDF';
    }

    /* ── Helpers ── */
    function showError(msg) {
        errorMsg.textContent   = msg;
        errorMsg.style.display = 'block';
    }

    function clearError() {
        errorMsg.style.display = 'none';
        errorMsg.textContent   = '';
    }

    function setLoading(loading) {
        if (loading) {
            btnSearch.disabled  = true;
            btnSearch.innerHTML = '<span class="spinner"></span>';
        } else {
            btnSearch.disabled    = false;
            btnSearch.textContent = 'Cari';
        }
    }

    /* safeText: untuk posisi tidak perlu maxLen ketat karena format "1 / 200" sudah pendek */
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

            /* Isi overlay sertifikat — 4 field sesuai desain
               general_position & category_position sudah berformat "1 / 200" dari server */
            certName.textContent        = p.display_name     || '-';
            certCategory.textContent    = safeText(p.category, 25);
            certTime.textContent        = p.finish_time       || '-';
            certPosition.textContent    = p.general_position  || '-';
            certCatPosition.textContent = p.category_position || '-';

            /* Simpan untuk story */
            currentParticipant = p;

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
        const el  = certName;
        const len = el.textContent.length;
        if (len > 30) {
            el.style.fontSize = 'clamp(.8rem, 3.2vw, 1.8rem)';
        } else if (len > 20) {
            el.style.fontSize = 'clamp(.9rem, 3.8vw, 2.2rem)';
        } else {
            el.style.fontSize = '';
        }
    }

    /* ── Print / Download ── */
    async function doPrint() {
        if (isMobile) {
            btnPrint.disabled  = true;
            btnPrint.innerHTML = '<span class="spinner"></span> Menyiapkan…';

            try {
                const container = document.getElementById('cert-container');
                const canvas = await html2canvas(container, {
                    scale: 2,
                    useCORS: true,
                    allowTaint: false,
                    backgroundColor: null,
                    logging: false,
                });

                const dataUrl = canvas.toDataURL('image/jpeg', 0.92);

                const byteStr = atob(dataUrl.split(',')[1]);
                const arr     = new Uint8Array(byteStr.length);
                for (let i = 0; i < byteStr.length; i++) arr[i] = byteStr.charCodeAt(i);
                const blob  = new Blob([arr], { type: 'image/jpeg' });
                const url   = URL.createObjectURL(blob);

                const name  = ((currentParticipant && currentParticipant.display_name) || 'finisher')
                                .replace(/\s+/g, '_');
                const link  = document.createElement('a');
                link.download = 'sertifikat_scoutrun2026_' + name + '.jpg';
                link.href   = url;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                setTimeout(() => URL.revokeObjectURL(url), 5000);

            } catch (err) {
                alert('Gagal membuat gambar. Silakan screenshot manual.');
                console.error(err);
            } finally {
                btnPrint.disabled  = false;
                btnPrint.innerHTML = `
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span id="btn-print-label">Download PNG</span>`;
            }
        } else {
            window.print();
        }
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

    bibInput.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    /* ════════════════════════════════════════════════
       STORY IG
    ════════════════════════════════════════════════ */

    let currentParticipant = null;
    let renderedCanvas     = null;

    const storyModal     = document.getElementById('story-modal');
    const storyFileInput = document.getElementById('story-file-input');
    const previewWrap    = document.getElementById('story-preview-wrap');
    const storyCanvas    = document.getElementById('story-canvas');
    const btnStory       = document.getElementById('btn-story');
    const btnStoryCancel = document.getElementById('btn-story-cancel');
    const btnStoryDl     = document.getElementById('btn-story-dl');

    btnStory.addEventListener('click', function () {
        storyModal.classList.add('open');
        storyFileInput.value      = '';
        previewWrap.style.display = 'none';
        btnStoryDl.disabled       = true;
        renderedCanvas            = null;
    });

    btnStoryCancel.addEventListener('click', closeModal);
    storyModal.addEventListener('click', function (e) {
        if (e.target === storyModal) closeModal();
    });
    function closeModal() { storyModal.classList.remove('open'); }

    storyFileInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        compressInput(file, function(dataUrl) {
            renderStory(dataUrl);
        });
    });

    function compressInput(file, callback) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const img = new Image();
            img.onload = function () {
                const MAX_INPUT = 2160;
                if (img.width <= MAX_INPUT && img.height <= MAX_INPUT) {
                    callback(e.target.result);
                    return;
                }
                const scale  = MAX_INPUT / Math.max(img.width, img.height);
                const tmpC   = document.createElement('canvas');
                tmpC.width   = Math.round(img.width  * scale);
                tmpC.height  = Math.round(img.height * scale);
                tmpC.getContext('2d').drawImage(img, 0, 0, tmpC.width, tmpC.height);
                callback(tmpC.toDataURL('image/jpeg', 0.88));
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    async function renderStory(photoDataUrl) {
        if (!currentParticipant) return;

        const W = 1080, H = 1920;
        const p = currentParticipant;

        const offCanvas  = document.createElement('canvas');
        offCanvas.width  = W;
        offCanvas.height = H;
        const ctx = offCanvas.getContext('2d');

        const photo = await loadImage(photoDataUrl);

        const photoRatio  = photo.width / photo.height;
        const canvasRatio = W / H;
        let sx, sy, sw, sh;
        if (photoRatio > canvasRatio) {
            sh = photo.height;
            sw = sh * canvasRatio;
            sx = (photo.width - sw) / 2;
            sy = 0;
        } else {
            sw = photo.width;
            sh = sw / canvasRatio;
            sx = 0;
            sy = (photo.height - sh) / 2;
        }
        ctx.drawImage(photo, sx, sy, sw, sh, 0, 0, W, H);

        const grad = ctx.createLinearGradient(0, H * 0.45, 0, H);
        grad.addColorStop(0,   'rgba(0,0,0,0)');
        grad.addColorStop(0.4, 'rgba(0,0,0,0.55)');
        grad.addColorStop(1,   'rgba(0,0,0,0.82)');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, W, H);

        try {
            const logoUrl = "{{ asset('assets/images/logo/logo.png') }}";
            const logo = await loadImage(logoUrl);
            const tmpC = document.createElement('canvas');
            tmpC.width  = logo.width;
            tmpC.height = logo.height;
            const tmpX  = tmpC.getContext('2d');
            tmpX.drawImage(logo, 0, 0);
            tmpX.globalCompositeOperation = 'source-in';
            tmpX.fillStyle = '#ffffff';
            tmpX.fillRect(0, 0, tmpC.width, tmpC.height);

            const logoW = 320;
            const logoH = Math.round(logoW * logo.height / logo.width);
            const logoX = 80;
            const logoY = H - 420 - logoH;
            ctx.globalAlpha = 0.88;
            ctx.drawImage(tmpC, logoX, logoY, logoW, logoH);
            ctx.globalAlpha = 1;
        } catch (_) {}

        const badgeY = H - 390;
        ctx.font      = 'bold 38px "Inter", sans-serif';
        ctx.fillStyle = '#C9A84C';
        const badgeLabel = 'FINISHER';
        const badgeTw    = ctx.measureText(badgeLabel).width;
        const padX = 32, padY = 12;
        const bx = 80, bw = badgeTw + padX * 2, bh = 38 + padY * 2, br = bh / 2;
        roundRect(ctx, bx, badgeY, bw, bh, br);
        ctx.fillStyle = '#C9A84C';
        ctx.fill();
        ctx.fillStyle = '#2a0508';
        ctx.font = 'bold 36px "Inter", sans-serif';
        ctx.textBaseline = 'middle';
        ctx.fillText(badgeLabel, bx + padX, badgeY + bh / 2);

        const name = (p.display_name || '-').toUpperCase();
        ctx.fillStyle    = '#FFFFFF';
        ctx.textBaseline = 'top';
        const nameSize   = name.length > 18 ? 72 : name.length > 12 ? 88 : 104;
        ctx.font         = `900 ${nameSize}px "Inter", sans-serif`;
        ctx.fillText(truncateCanvas(ctx, name, W - 160), 80, badgeY + bh + 28);

        const sepY = badgeY + bh + 28 + nameSize + 32;
        ctx.strokeStyle = 'rgba(255,255,255,0.25)';
        ctx.lineWidth   = 1.5;
        ctx.beginPath();
        ctx.moveTo(80, sepY);
        ctx.lineTo(W - 80, sepY);
        ctx.stroke();

        /* Stats — general_position & category_position sudah "1 / 200" dari server */
        const stats = [
            { label: 'KATEGORI',    value: shortenCategory(p.category  || '-') },
            { label: 'FINISH TIME', value: p.finish_time                || '-'  },
            { label: 'OVERALL',     value: p.general_position           || '-'  },
            { label: 'CAT. POS',    value: p.category_position          || '-'  },
        ];

        const statY = sepY + 28;
        const colW  = (W - 160) / stats.length;

        stats.forEach(function (s, i) {
            const cx = 80 + i * colW;

            ctx.font         = '400 28px "Inter", sans-serif';
            ctx.fillStyle    = 'rgba(255,255,255,0.48)';
            ctx.textBaseline = 'top';
            ctx.fillText(s.label, cx, statY);

            ctx.fillStyle = '#FFFFFF';
            let fontSize = 44;
            ctx.font = `700 ${fontSize}px "Inter", sans-serif`;
            /* Auto shrink — penting untuk format "1 / 200" yang lebih lebar */
            while (fontSize > 20 && ctx.measureText(s.value).width > colW - 20) {
                fontSize -= 2;
                ctx.font = `700 ${fontSize}px "Inter", sans-serif`;
            }
            ctx.fillText(s.value, cx, statY + 36);

            if (i > 0) {
                ctx.strokeStyle = 'rgba(255,255,255,0.15)';
                ctx.lineWidth   = 1;
                ctx.beginPath();
                ctx.moveTo(cx - 12, statY);
                ctx.lineTo(cx - 12, statY + 110);
                ctx.stroke();
            }
        });

        renderedCanvas = offCanvas;

        storyCanvas.width  = W;
        storyCanvas.height = H;
        storyCanvas.getContext('2d').drawImage(offCanvas, 0, 0);

        previewWrap.style.display = 'block';
        btnStoryDl.disabled       = false;
    }

    btnStoryDl.addEventListener('click', async function () {
        if (!renderedCanvas) return;

        btnStoryDl.disabled  = true;
        btnStoryDl.innerHTML = '<span class="spinner"></span> Menyiapkan…';

        try {
            const MAX_W      = 1080;
            let exportCanvas = renderedCanvas;

            if (renderedCanvas.width > MAX_W) {
                const scale  = MAX_W / renderedCanvas.width;
                const scaled = document.createElement('canvas');
                scaled.width  = MAX_W;
                scaled.height = Math.round(renderedCanvas.height * scale);
                scaled.getContext('2d').drawImage(renderedCanvas, 0, 0, scaled.width, scaled.height);
                exportCanvas = scaled;
            }

            const dataUrl = exportCanvas.toDataURL('image/jpeg', 0.82);

            const byteStr = atob(dataUrl.split(',')[1]);
            const arr     = new Uint8Array(byteStr.length);
            for (let i = 0; i < byteStr.length; i++) arr[i] = byteStr.charCodeAt(i);
            const blob = new Blob([arr], { type: 'image/jpeg' });
            const url  = URL.createObjectURL(blob);

            const participantName = ((currentParticipant && currentParticipant.display_name) || 'finisher')
                                       .replace(/\s+/g, '_');
            const link    = document.createElement('a');
            link.download = 'scoutrun2026_story_' + participantName + '.jpg';
            link.href     = url;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            setTimeout(() => URL.revokeObjectURL(url), 8000);

        } catch (err) {
            alert('Gagal mengunduh. Coba screenshot layar secara manual.');
            console.error(err);
        } finally {
            btnStoryDl.disabled  = false;
            btnStoryDl.innerHTML = `
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download`;
        }
    });

    /* ── Utilities ── */
    function loadImage(src) {
        return new Promise(function (resolve, reject) {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload  = function () { resolve(img); };
            img.onerror = reject;
            img.src     = src;
        });
    }

    function roundRect(ctx, x, y, w, h, r) {
        ctx.beginPath();
        ctx.moveTo(x + r, y);
        ctx.lineTo(x + w - r, y);
        ctx.quadraticCurveTo(x + w, y, x + w, y + r);
        ctx.lineTo(x + w, y + h - r);
        ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
        ctx.lineTo(x + r, y + h);
        ctx.quadraticCurveTo(x, y + h, x, y + h - r);
        ctx.lineTo(x, y + r);
        ctx.quadraticCurveTo(x, y, x + r, y);
        ctx.closePath();
    }

    function truncateCanvas(ctx, text, maxW) {
        if (ctx.measureText(text).width <= maxW) return text;
        while (text.length > 1 && ctx.measureText(text + '…').width > maxW) {
            text = text.slice(0, -1);
        }
        return text + '…';
    }

    function shortenCategory(cat) {
        cat = cat.replace(/\s*\(.*?\)/g, '').trim();
        cat = cat.replace(/\bUMUR\b/gi, '').trim();
        return cat;
    }

})();
</script>
</body>
</html>
