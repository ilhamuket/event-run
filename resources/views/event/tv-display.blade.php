{{-- resources/views/event/tv-display.blade.php --}}
{{-- Route: GET /event/{event}/tv --}}
{{-- Controller: EventController@tvDisplay --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->name }} — Live TV Display</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,500;0,9..40,700;1,9..40,300&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --red:       #C0292A;
            --red-light: #E8474A;
            --gold:      #B8860B;
            --gold-dark: #8A6100;
            --white:     #FFFFFF;
            --text-primary:   #1A1A1A;
            --text-secondary: #3D3D3D;
            --text-muted:     #6B6B6B;
        }

        html, body {
            width: 100%; height: 100%;
            overflow: hidden;
            background: #6b1010;
            font-family: 'DM Sans', sans-serif;
        }

        /* ── BACKGROUND ── */
        #bg {
            position: fixed; inset: 0; z-index: 0;
            background-image: url('{{ $event->tv_background_url ?? asset("assets/images/bg_rev2.jpeg") }}');
            background-size: contain;
            background-position: center center;
            background-repeat: no-repeat;
        }

        /* ── STAGE — 16:9 frame, selalu fit ke layar ── */
        #stage-wrapper {
            position: fixed; inset: 0; z-index: 5;
            display: flex; align-items: center; justify-content: center;
        }

        #frame {
            position: relative;
            /*
             * Lebar = min(100vw, tinggi×16/9)
             * Tinggi = min(100vh, lebar×9/16)
             * Ini memastikan frame 16:9 selalu pas di layar apapun.
             */
            width:  min(100vw, calc(100vh * 16 / 9));
            height: min(100vh, calc(100vw * 9 / 16));
        }

        /*
         * #content-box — overlay tepat di atas area kotak putih gambar.
         *
         * Kotak putih di bg_rev.png (diukur dari gambar):
         *   left  ≈ 6.5%   top  ≈ 18%   (di bawah logo Scout Run)
         *   right ≈ 96%    bottom ≈ 84%
         *
         * Sesuaikan nilai ini jika posisi kotak putih di gambar berbeda.
         */
        #content-box {
            position: absolute;
            left:   6.5%;
            top:    18%;
            width:  89.5%;   /* 96% - 6.5% */
            height: 66%;     /* 84% - 18% */

            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: transparent;
            padding: 2% 4%;
        }

        /* ── LOADING OVERLAY ── */
        #loading-overlay {
            position: absolute; inset: 0;
            background: rgba(255,255,255,0.6);
            display: none; align-items: center; justify-content: center;
            z-index: 20; border-radius: 8px;
        }
        .spinner {
            width: 40px; height: 40px;
            border: 3px solid rgba(192,41,42,0.15);
            border-top-color: var(--red);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        /* ══════════════════════════════
           STATE: IDLE
        ══════════════════════════════ */
        #state-idle {
            width: 100%; text-align: center;
            display: flex; flex-direction: column; align-items: center;
            gap: clamp(12px, 2vh, 28px);
        }

        .scan-icon {
            width:  clamp(60px, 8vw, 100px);
            height: clamp(60px, 8vw, 100px);
            border: 2px solid rgba(192,41,42,0.2);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            position: relative;
        }
        .scan-icon::before {
            content: '';
            position: absolute; inset: 8px;
            border: 1.5px dashed rgba(192,41,42,0.35);
            border-radius: 50%;
            animation: spin 6s linear infinite;
        }
        .scan-icon svg {
            width:  clamp(28px, 4vw, 50px);
            height: clamp(28px, 4vw, 50px);
            color: var(--red);
        }

        #state-idle .prompt-main {
            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(28px, 4.5vw, 64px);
            letter-spacing: 2px;
            color: var(--text-primary);
        }
        #state-idle .prompt-sub {
            font-size: clamp(13px, 1.4vw, 20px);
            color: var(--text-muted);
            line-height: 1.5;
            max-width: 65%;
        }
        .scan-line-anim {
            width: 220px; height: 2px;
            background: linear-gradient(90deg, transparent, var(--red-light), transparent);
            border-radius: 1px;
            animation: scan-slide 2.5s ease-in-out infinite;
        }

        /* ══════════════════════════════
           STATE: RESULT  (Nama · BIB · Kategori)
        ══════════════════════════════ */
        #state-result {
            display: none;
            width: 100%; height: 100%;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: clamp(8px, 1.5vh, 20px);
            text-align: center;
            overflow: hidden;
        }

        /* BIB */
        .bib-row {
            display: flex; align-items: center; justify-content: center;
            gap: clamp(10px, 1.5vw, 20px);
        }
        .bib-badge {
            display: inline-flex; flex-direction: column;
            align-items: center; justify-content: center;
            background: var(--red);
            border-radius: clamp(8px, 1vw, 16px);
            padding: clamp(6px, 0.8vw, 12px) clamp(16px, 2.5vw, 36px);
            box-shadow: 0 4px 20px rgba(192,41,42,0.35);
        }
        .bib-badge .bib-label {
            font-size: clamp(9px, 0.8vw, 13px);
            font-weight: 700; letter-spacing: 3px;
            color: rgba(255,255,255,0.8);
            text-transform: uppercase;
            line-height: 1;
        }
        .bib-badge .bib-num {
            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(60px, 11vw, 160px);
            letter-spacing: 2px;
            color: #FFFFFF;
            line-height: 1;
        }

        /* Nama */
        .result-name {
            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(36px, 7vw, 100px);
            letter-spacing: 3px;
            color: var(--text-primary);
            line-height: 1.05;
            max-width: 100%;
            /* Bungkus kata panjang ke baris baru, tidak overflow */
            overflow-wrap: break-word;
            word-break: break-word;
            white-space: normal;
            text-align: center;
        }
        .result-name.long  { font-size: clamp(28px, 5.5vw, 78px); }
        .result-name.xlong { font-size: clamp(22px, 4vw, 58px); }

        /* Divider tipis */
        .result-divider {
            width: 60%; height: 1.5px;
            background: linear-gradient(90deg, transparent, rgba(192,41,42,0.4), transparent);
            flex-shrink: 0;
        }

        /* Kategori */
        .result-category {
            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(28px, 5vw, 72px);
            letter-spacing: 4px;
            color: var(--red);
        }

        /* Status finish */
        #finish-bar {
            display: none;
        }
        #finish-bar .finish-text {
            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(22px, 3vw, 44px);
            letter-spacing: 6px;
            color: var(--gold-dark);
            animation: blink-gold 1.8s ease-in-out infinite;
        }

        /* ══════════════════════════════
           STATE: ERROR
        ══════════════════════════════ */
        #state-error {
            display: none;
            width: 100%; text-align: center;
            flex-direction: column; align-items: center;
            gap: clamp(10px, 1.5vh, 20px);
        }
        #state-error .error-icon {
            width:  clamp(44px, 5.5vw, 72px);
            height: clamp(44px, 5.5vw, 72px);
            background: rgba(192,41,42,0.07);
            border: 1px solid rgba(192,41,42,0.25);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
        }
        #state-error .error-icon svg {
            width:  clamp(20px, 2.8vw, 36px);
            height: clamp(20px, 2.8vw, 36px);
            color: var(--red);
        }
        #state-error .error-msg {
            font-size: clamp(16px, 2vw, 28px);
            color: var(--text-secondary);
        }
        #state-error .error-tag {
            font-size: clamp(12px, 1vw, 16px);
            color: var(--text-muted);
            font-family: monospace;
            letter-spacing: 1px;
        }

        /* ── Hidden RFID input ── */
        #rfid-capture {
            position: fixed; bottom: -200px; left: 0;
            width: 1px; height: 1px; opacity: 0;
        }

        /* ── Animations ── */
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes scan-slide {
            0%   { transform: translateX(-130px); opacity: 0; }
            20%  { opacity: 1; }
            80%  { opacity: 1; }
            100% { transform: translateX(130px); opacity: 0; }
        }
        @keyframes blink-gold {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.5; }
        }
        @keyframes card-pop {
            0%   { transform: scale(0.96); opacity: 0; }
            65%  { transform: scale(1.015); }
            100% { transform: scale(1); opacity: 1; }
        }
        .card-animate { animation: card-pop 0.45s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
    </style>
</head>
<body>

<div id="bg"></div>

<div id="stage-wrapper">
    <div id="frame">
        <div id="content-box">

            <div id="loading-overlay">
                <div class="spinner"></div>
            </div>

            {{-- ── STATE: IDLE ── --}}
            <div id="state-idle">
                <div class="scan-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"/>
                    </svg>
                </div>
                <div class="prompt-main">Scan RFID untuk melihat info peserta</div>
                <div class="prompt-sub">Dekatkan tag RFID ke reader untuk menampilkan data peserta di layar</div>
                <div class="scan-line-anim"></div>
            </div>

            {{-- ── STATE: RESULT ── --}}
            <div id="state-result">
                <div class="bib-row">
                    <div class="bib-badge">
                        <span class="bib-label"></span>
                        <span class="bib-num" id="r-bib">—</span>
                    </div>
                </div>

                <div class="result-name" id="r-name">—</div>

                <div class="result-divider"></div>

                <div class="result-category" id="r-category">—</div>

                <div id="finish-bar">
                    <span class="finish-text">✦ SELAMAT TELAH FINISH! ✦</span>
                </div>
            </div>

            {{-- ── STATE: ERROR ── --}}
            <div id="state-error">
                <div class="error-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                </div>
                <div class="error-msg" id="error-msg">Tag tidak dikenali</div>
                <div class="error-tag" id="error-tag"></div>
            </div>

        </div>
    </div>
</div>

<input id="rfid-capture" type="text" autocomplete="off" />

<script>
(function () {
    'use strict';

    const LOOKUP_URL   = '{{ route("event.tv.lookup", $event) }}';
    const IDLE_TIMEOUT = 20000;
    const SCAN_IDLE_MS = 80;
    const SCAN_LOCKOUT = 600;

    const $idle      = document.getElementById('state-idle');
    const $result    = document.getElementById('state-result');
    const $error     = document.getElementById('state-error');
    const $loading   = document.getElementById('loading-overlay');
    const $finishBar = document.getElementById('finish-bar');
    const $box       = document.getElementById('content-box');

    const f = {
        bib:      document.getElementById('r-bib'),
        name:     document.getElementById('r-name'),
        category: document.getElementById('r-category'),
    };

    let idleTimer  = null;
    let isLoading  = false;
    let scanLocked = false;

    /* Animasi masuk */
    function animateBox() {
        $box.classList.remove('card-animate');
        void $box.offsetWidth;
        $box.classList.add('card-animate');
    }

    /* Sesuaikan ukuran font nama berdasarkan panjang teks */
    function fitName(text) {
        f.name.classList.remove('long', 'xlong');
        f.name.textContent = text || '—';
        const len = (text || '').length;
        if (len > 24)      f.name.classList.add('xlong');
        else if (len > 16) f.name.classList.add('long');
    }

    function showIdle() {
        $idle.style.display      = 'flex';
        $result.style.display    = 'none';
        $error.style.display     = 'none';
        $finishBar.style.display = 'none';
        animateBox();
    }

    function showResult(data) {
        $idle.style.display   = 'none';
        $error.style.display  = 'none';
        $result.style.display = 'flex';

        f.bib.textContent      = data.bib || '—';
        fitName(data.name);
        f.category.textContent = data.category || '—';

        const type = data.last_checkpoint_type || 'not_started';
        $finishBar.style.display = type === 'finish' ? 'block' : 'none';

        animateBox();
        scheduleIdle();
    }

    function showError(msg, tag) {
        $idle.style.display      = 'none';
        $result.style.display    = 'none';
        $error.style.display     = 'flex';
        $finishBar.style.display = 'none';
        document.getElementById('error-msg').textContent = msg || 'Tag tidak dikenali';
        document.getElementById('error-tag').textContent = tag || '';
        animateBox();
        scheduleIdle();
    }

    function setLoading(on) {
        isLoading = on;
        $loading.style.display = on ? 'flex' : 'none';
    }

    function scheduleIdle() {
        clearTimeout(idleTimer);
        idleTimer = setTimeout(showIdle, IDLE_TIMEOUT);
    }

    async function lookup(tag) {
        if (isLoading) return;
        if (!tag || tag.length < 8) return;
        setLoading(true);
        try {
            const res  = await fetch(LOOKUP_URL + '?tag=' + encodeURIComponent(tag), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (data.success && data.participant) showResult(data.participant);
            else showError(data.message || 'Tag tidak terdaftar', tag);
        } catch (e) {
            showError('Koneksi ke server gagal', tag);
        } finally {
            setLoading(false);
        }
    }

    /* RFID input handling */
    const rfidInput = document.getElementById('rfid-capture');
    document.addEventListener('click', () => rfidInput.focus());
    rfidInput.focus();
    setInterval(() => rfidInput.focus(), 5000);

    let scanTimer = null;

    function processBuffer() {
        const tag = rfidInput.value.trim().toUpperCase();
        rfidInput.value = '';
        if (scanLocked) return;
        if (tag.length < 8) return;
        scanLocked = true;
        lookup(tag);
        setTimeout(() => { rfidInput.value = ''; scanLocked = false; }, SCAN_LOCKOUT);
    }

    rfidInput.addEventListener('input', function () {
        if (scanLocked) { rfidInput.value = ''; return; }
        clearTimeout(scanTimer);
        scanTimer = setTimeout(processBuffer, SCAN_IDLE_MS);
    });

    rfidInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); clearTimeout(scanTimer); processBuffer(); }
    });

    showIdle();

    /* Helper untuk testing di console browser */
    window.testScan = (tag) => lookup(tag);
})();
</script>
</body>
</html>
