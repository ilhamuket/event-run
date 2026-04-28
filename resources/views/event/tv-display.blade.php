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
            --red:        #C0292A;
            --red-light:  #E8474A;
            --red-dark:   #8B1A1A;
            --gold:       #D4A017;
            --gold-light: #F2C43A;
            --white:      #FFFFFF;
            --off-white:  #F5F0E8;
            /* Content box bg: semi-transparent dark maroon so bg image shows through */
            --box-bg:     rgba(20, 4, 4, 0.55);
        }

        html, body {
            width: 100%; height: 100%;
            overflow: hidden;
            background: #6b1010;
            font-family: 'DM Sans', sans-serif;
        }

        #bg {
            position: fixed; inset: 0; z-index: 0;
            background-image: url('{{ $event->tv_background_url ?? asset("assets/images/bg_tv.png") }}');
            background-size: contain;
            background-position: center center;
            background-repeat: no-repeat;
        }

        #stage-wrapper {
            position: fixed; inset: 0; z-index: 5;
            display: flex; align-items: center; justify-content: center;
        }

        #frame {
            position: relative;
            width: min(100vw, calc(100vh * 16 / 9));
            height: min(100vh, calc(100vw * 9 / 16));
        }

        /*
         * Kotak konten — posisi sama dengan sebelumnya.
         * Sekarang kita beri background semi-transparan gelap agar teks terbaca
         * tapi gambar background di belakangnya tetap terasa.
         * Tanpa border-radius besar agar menyatu dengan kotak putih di gambar.
         */
        #content-box {
            position: absolute;
            left:   14.6%;
            top:    20.6%;
            width:  70.7%;
            height: 44.1%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            /* Slight dark overlay so text is always readable */
            background: rgba(10, 2, 2, 0.50);
            border-radius: 4px;
        }

        /* ── CLOCK BAR ── */
        #clock-bar {
            flex-shrink: 0;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 4%;
            height: 48px;
            border-bottom: 1px solid rgba(192, 41, 42, 0.35);
            background: rgba(0,0,0,0.25);
        }
        #clock-bar .live-badge {
            display: flex; align-items: center; gap: 8px;
            font-size: clamp(10px, 1.1vw, 14px);
            font-weight: 500; letter-spacing: 1.5px;
            color: rgba(255,255,255,0.75);
            text-transform: uppercase;
        }
        #clock-bar .live-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: var(--red-light);
            animation: pulse-red 1.4s ease-in-out infinite;
        }
        #clock-bar .clock {
            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(18px, 2.8vw, 38px);
            letter-spacing: 3px; color: var(--off-white);
        }
        #clock-bar .stat-mini {
            display: flex; gap: clamp(12px, 2vw, 28px);
        }
        #clock-bar .stat-mini-item {
            text-align: center;
        }
        #clock-bar .stat-mini-item .smv {
            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(14px, 1.8vw, 24px);
            letter-spacing: 1px; color: var(--off-white);
            display: block;
        }
        #clock-bar .stat-mini-item .sml {
            font-size: clamp(7px, 0.7vw, 10px);
            letter-spacing: 1px; text-transform: uppercase;
            color: rgba(255,255,255,0.5);
            display: block;
        }

        /* ── INNER CENTER ── */
        #inner-center {
            flex: 1;
            display: flex; align-items: center; justify-content: center;
            position: relative;
            overflow: hidden;
        }
        #inner-center:has(#state-result[style*="flex"]) {
            align-items: stretch;
        }

        /* ── STATE: IDLE ── */
        #state-idle {
            width: 100%;
            text-align: center;
            display: flex; flex-direction: column; align-items: center;
            gap: clamp(8px, 1.5vh, 20px);
        }
        .scan-icon {
            width: clamp(50px, 7vw, 88px);
            height: clamp(50px, 7vw, 88px);
            border: 2px solid rgba(255,255,255,0.15);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            position: relative;
        }
        .scan-icon::before {
            content: '';
            position: absolute; inset: 7px;
            border: 1.5px dashed rgba(232, 71, 74, 0.6);
            border-radius: 50%;
            animation: spin 6s linear infinite;
        }
        .scan-icon svg {
            width: clamp(24px, 3.5vw, 44px);
            height: clamp(24px, 3.5vw, 44px);
            color: rgba(255,255,255,0.55);
        }
        #state-idle .prompt-main {
            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(22px, 3.8vw, 52px);
            letter-spacing: 2px;
            /* Bright white so it pops on dark background */
            color: var(--off-white);
            text-shadow: 0 2px 12px rgba(0,0,0,0.6);
        }
        #state-idle .prompt-sub {
            font-size: clamp(11px, 1.2vw, 16px);
            /* More visible than before */
            color: rgba(255,255,255,0.55);
            line-height: 1.5; max-width: 70%;
        }
        .scan-line-anim {
            width: 200px; height: 2px;
            background: linear-gradient(90deg, transparent, var(--red-light), transparent);
            border-radius: 1px;
            animation: scan-slide 2.5s ease-in-out infinite;
        }

        /* ── STATE: RESULT ── */
        #state-result {
            display: none;
            width: 100%; height: 100%;
            flex-direction: column;
            justify-content: space-between;
            padding: 3% 5% 4%;
        }

        /* ── TOP: nama hero area ── */
        .result-top {
            display: flex;
            align-items: flex-start;
            gap: 3%;
        }

        /* BIB badge */
        .bib-badge {
            flex-shrink: 0;
            width:  clamp(56px, 7vw, 96px);
            height: clamp(56px, 7vw, 96px);
            background: var(--red);
            border-radius: 12px;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 0px;
            box-shadow: 0 6px 20px rgba(192,41,42,0.6), inset 0 1px 0 rgba(255,255,255,0.2);
            margin-top: 4px;
        }
        .bib-badge .bib-label {
            font-size: clamp(7px, 0.6vw, 10px);
            font-weight: 700; letter-spacing: 3px;
            /* White so it's readable on red badge */
            color: rgba(255,255,255,0.8);
            text-transform: uppercase;
        }
        .bib-badge .bib-num {
            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(26px, 4.2vw, 58px);
            letter-spacing: 1px; color: var(--white);
            line-height: 1;
        }

        /* NAME BLOCK */
        .result-name-block {
            flex: 1; min-width: 0;
        }

        /* THE BIG NAME */
        .result-name {
            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(48px, 8.5vw, 120px);
            letter-spacing: 2px;
            /* Pure white, strong shadow for legibility on dark bg */
            color: var(--white);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            line-height: 0.92;
            text-shadow: 0 2px 16px rgba(0,0,0,0.7), 0 0 40px rgba(255,255,255,0.06);
        }
        .result-name.long  { font-size: clamp(36px, 6.5vw, 92px); }
        .result-name.xlong { font-size: clamp(28px, 5vw,  70px); }

        /* Meta row below name */
        .result-meta {
            margin-top: clamp(4px, 0.6vh, 10px);
            font-size: clamp(11px, 1.1vw, 16px);
            /* More visible meta text */
            color: rgba(255,255,255,0.65);
            display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
            line-height: 1;
        }
        .result-meta .dot { opacity: 0.35; font-size: 1.2em; }
        .result-category-pill {
            display: inline-flex; align-items: center;
            padding: 3px 12px;
            background: rgba(192,41,42,0.4);
            border: 1px solid rgba(232, 71, 74, 0.6);
            border-radius: 20px;
            font-size: clamp(10px, 0.9vw, 13px);
            font-weight: 600; letter-spacing: 0.5px;
            /* Bright pink-white for category text */
            color: #FFAEAE;
        }

        /* Status pill */
        .status-pill {
            flex-shrink: 0;
            align-self: flex-start;
            margin-top: 6px;
            padding: clamp(6px, 0.8vh, 10px) clamp(12px, 1.4vw, 22px);
            border-radius: 8px;
            font-size: clamp(10px, 1vw, 15px);
            font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase;
        }
        .status-finish {
            background: rgba(212,160,23,0.25);
            color: #FFD875;
            border: 1px solid rgba(212,160,23,0.6);
            box-shadow: 0 0 20px rgba(212,160,23,0.25);
        }
        .status-checkpoint {
            background: rgba(55,138,221,0.25);
            color: #A8D8FF;
            border: 1px solid rgba(55,138,221,0.5);
        }
        .status-start {
            background: rgba(99,153,34,0.25);
            color: #C2E880;
            border: 1px solid rgba(99,153,34,0.5);
        }
        .status-not_started {
            background: rgba(180,180,180,0.15);
            color: #D8D8D8;
            border: 1px solid rgba(200,200,200,0.35);
        }

        /* ── DIVIDER ── */
        .result-divider {
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, rgba(192,41,42,0.7), rgba(255,255,255,0.12), rgba(192,41,42,0.7));
            flex-shrink: 0;
            margin: 2% 0;
        }

        /* ── BOTTOM: Stats 3-column grid ── */
        .result-stats {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            flex-shrink: 0;
        }
        .stat-cell {
            padding: 0 clamp(12px, 2vw, 32px);
            border-right: 1px solid rgba(255,255,255,0.10);
            display: flex; flex-direction: column; gap: 2px;
        }
        .stat-cell:first-child { padding-left: 0; }
        .stat-cell:last-child  { border-right: none; }

        .stat-label {
            font-size: clamp(8px, 0.7vw, 11px);
            font-weight: 600; letter-spacing: 2.5px; text-transform: uppercase;
            /* Brighter label text — was 0.3 opacity, now 0.55 */
            color: rgba(255,255,255,0.55);
            margin-bottom: 2px;
        }
        /* Elapsed time — gold gradient */
        .stat-value {
            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(28px, 4vw, 56px);
            letter-spacing: 2px;
            /* Pure white so numbers are legible */
            color: var(--white);
            line-height: 1;
            text-shadow: 0 2px 8px rgba(0,0,0,0.5);
        }
        .stat-value.hero {
            font-size: clamp(36px, 5.5vw, 76px);
            background: linear-gradient(135deg, var(--gold-light) 0%, #FFD875 50%, var(--gold) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            filter: drop-shadow(0 2px 8px rgba(212,160,23,0.4));
        }
        .stat-sub {
            font-size: clamp(9px, 0.75vw, 12px);
            /* Slightly brighter sub-text */
            color: rgba(255,255,255,0.45);
            margin-top: 1px;
        }

        /* ── FINISH CELEBRATION ── */
        #finish-bar {
            display: none;
            width: 100%;
            padding: clamp(6px, 1vh, 12px) 0 0;
            text-align: center;
        }
        #finish-bar .finish-text {
            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(20px, 2.8vw, 38px);
            letter-spacing: 6px;
            background: linear-gradient(90deg, var(--gold), var(--gold-light), var(--gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: shimmer 2s ease-in-out infinite;
            background-size: 200% 100%;
        }
        @keyframes shimmer {
            0%   { background-position: 100% 0; }
            100% { background-position: -100% 0; }
        }

        /* ── STATE: ERROR ── */
        #state-error {
            width: 100%; text-align: center;
            display: none; flex-direction: column; align-items: center;
            gap: clamp(10px, 1.5vh, 20px);
        }
        #state-error .error-icon {
            width: clamp(40px, 5vw, 64px); height: clamp(40px, 5vw, 64px);
            background: rgba(192,41,42,0.2);
            border: 1px solid rgba(232, 71, 74, 0.45);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
        }
        #state-error .error-icon svg {
            width: clamp(18px, 2.5vw, 30px); height: clamp(18px, 2.5vw, 30px);
            color: var(--red-light);
        }
        /* Error message — bright white, was 0.6 opacity */
        #state-error .error-msg  {
            font-size: clamp(14px, 1.6vw, 22px);
            color: rgba(255,255,255,0.85);
        }
        #state-error .error-tag  {
            font-size: clamp(10px, 0.9vw, 14px);
            color: rgba(255,255,255,0.45);
            font-family: monospace; letter-spacing: 1px;
        }

        /* ── LOADING OVERLAY ── */
        #loading-overlay {
            position: absolute; inset: 0;
            background: rgba(0,0,0,0.55);
            display: none; align-items: center; justify-content: center;
            z-index: 20;
        }
        .spinner {
            width: 36px; height: 36px;
            border: 2px solid rgba(255,255,255,0.1);
            border-top-color: var(--red-light);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        /* ── RFID INPUT ── */
        #rfid-capture {
            position: fixed; bottom: -200px; left: 0;
            width: 1px; height: 1px; opacity: 0;
        }

        /* ── KEYFRAMES ── */
        @keyframes pulse-red {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.4; transform: scale(0.85); }
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes scan-slide {
            0%   { transform: translateX(-120px); opacity: 0; }
            20%  { opacity: 1; }
            80%  { opacity: 1; }
            100% { transform: translateX(120px); opacity: 0; }
        }
        @keyframes card-pop {
            0%   { transform: scale(0.97); opacity: 0; }
            60%  { transform: scale(1.01); }
            100% { transform: scale(1); opacity: 1; }
        }
        .card-animate { animation: card-pop 0.45s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
    </style>
</head>
<body>

{{-- Background --}}
<div id="bg"></div>

{{-- Stage: content overlaid on image --}}
<div id="stage-wrapper">
    <div id="frame">

        {{-- Content box: dark semi-transparent overlay over the white card area in background image --}}
        <div id="content-box">

            {{-- Loading overlay --}}
            <div id="loading-overlay">
                <div class="spinner"></div>
            </div>

            {{-- Clock Bar --}}
            <div id="clock-bar">
                <div class="live-badge">
                    <span class="live-dot"></span>
                    Live Tracking
                </div>
                <div class="clock" id="clock">00:00:00</div>
                <div class="stat-mini">
                    <div class="stat-mini-item">
                        <span class="smv" id="stat-finish">—</span>
                        <span class="sml">Finish</span>
                    </div>
                    <div class="stat-mini-item">
                        <span class="smv" id="stat-oncourse">—</span>
                        <span class="sml">On Course</span>
                    </div>
                    <div class="stat-mini-item">
                        <span class="smv" id="stat-total">{{ $totalParticipants ?? '—' }}</span>
                        <span class="sml">Total</span>
                    </div>
                </div>
            </div>

            {{-- State area --}}
            <div id="inner-center">

                {{-- STATE: IDLE --}}
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
                    <div class="prompt-sub">Dekatkan tag RFID ke reader untuk menampilkan waktu dan posisi peserta</div>
                    <div class="scan-line-anim"></div>
                </div>

                {{-- STATE: RESULT --}}
                <div id="state-result">

                    {{-- TOP: BIB + NAME + STATUS --}}
                    <div class="result-top">
                        <div class="bib-badge">
                            <span class="bib-label">BIB</span>
                            <span class="bib-num" id="r-bib">—</span>
                        </div>
                        <div class="result-name-block">
                            <div class="result-name" id="r-name">—</div>
                            <div class="result-meta">
                                <span id="r-gender">—</span>
                                <span class="dot">·</span>
                                <span id="r-age">—</span>&nbsp;th
                                <span class="dot">·</span>
                                <span id="r-city">—</span>
                                <span class="dot">·</span>
                                <span class="result-category-pill" id="r-category">—</span>
                            </div>
                        </div>
                        <span class="status-pill" id="r-status-pill">—</span>
                    </div>

                    <div class="result-divider"></div>

                    {{-- BOTTOM: 3 stats --}}
                    <div class="result-stats">
                        <div class="stat-cell">
                            <span class="stat-label">Elapsed Time</span>
                            <span class="stat-value hero" id="r-elapsed">—:——:——</span>
                            <span class="stat-sub">sejak start</span>
                        </div>
                        <div class="stat-cell">
                            <span class="stat-label">Posisi Umum</span>
                            <span class="stat-value" id="r-pos-general">#—</span>
                            <span class="stat-sub">dari <span id="r-total-finishers">—</span> finisher</span>
                        </div>
                        <div class="stat-cell">
                            <span class="stat-label">Checkpoint Terakhir</span>
                            <span class="stat-value" id="r-checkpoint-time">——:——</span>
                            <span class="stat-sub" id="r-checkpoint-name">—</span>
                        </div>
                    </div>

                    {{-- Finish celebration (only shown on type=finish) --}}
                    <div id="finish-bar">
                        <span class="finish-text">✦ SELAMAT TELAH FINISH! ✦</span>
                    </div>

                </div>

                {{-- STATE: ERROR --}}
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

            </div>{{-- #inner-center --}}
        </div>{{-- #content-box --}}

    </div>{{-- #frame --}}
</div>{{-- #stage-wrapper --}}

{{-- Hidden RFID capture --}}
<input id="rfid-capture" type="text" autocomplete="off" />

<script>
(function () {
    'use strict';

    const LOOKUP_URL     = '{{ route("event.tv.lookup", $event) }}';
    const STATS_URL      = '{{ route("event.tv.stats", $event) }}';
    const IDLE_TIMEOUT   = 12000;
    const STATS_INTERVAL = 20000;
    const SCAN_IDLE_MS   = 80;
    const SCAN_LOCKOUT   = 600;

    const $idle      = document.getElementById('state-idle');
    const $result    = document.getElementById('state-result');
    const $error     = document.getElementById('state-error');
    const $loading   = document.getElementById('loading-overlay');
    const $finishBar = document.getElementById('finish-bar');
    const $box       = document.getElementById('content-box');

    const f = {
        bib:        document.getElementById('r-bib'),
        name:       document.getElementById('r-name'),
        gender:     document.getElementById('r-gender'),
        age:        document.getElementById('r-age'),
        city:       document.getElementById('r-city'),
        category:   document.getElementById('r-category'),
        statusPill: document.getElementById('r-status-pill'),
        elapsed:    document.getElementById('r-elapsed'),
        posGeneral: document.getElementById('r-pos-general'),
        totalFin:   document.getElementById('r-total-finishers'),
        cpTime:     document.getElementById('r-checkpoint-time'),
        cpName:     document.getElementById('r-checkpoint-name'),
    };

    const s = {
        finish:   document.getElementById('stat-finish'),
        oncourse: document.getElementById('stat-oncourse'),
        total:    document.getElementById('stat-total'),
    };

    let idleTimer  = null;
    let isLoading  = false;
    let scanLocked = false;

    // Clock
    function updateClock() {
        const now = new Date();
        document.getElementById('clock').textContent =
            String(now.getHours()).padStart(2,'0') + ':' +
            String(now.getMinutes()).padStart(2,'0') + ':' +
            String(now.getSeconds()).padStart(2,'0');
    }
    updateClock();
    setInterval(updateClock, 1000);

    function fitName(text) {
        f.name.classList.remove('long', 'xlong');
        f.name.textContent = text;
        const len = (text || '').length;
        if (len > 28)      f.name.classList.add('xlong');
        else if (len > 18) f.name.classList.add('long');
    }

    function animateBox() {
        $box.classList.remove('card-animate');
        void $box.offsetWidth;
        $box.classList.add('card-animate');
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

        f.bib.textContent        = data.bib || '—';
        fitName(data.name || '—');
        f.gender.textContent     = data.gender === 'M' ? 'Pria' : (data.gender === 'F' ? 'Wanita' : '—');
        f.age.textContent        = data.age || '—';
        f.city.textContent       = data.city || '—';
        f.category.textContent   = data.category || '—';
        f.elapsed.textContent    = data.elapsed_time || '—:——:——';
        f.posGeneral.textContent = data.general_position ? '#' + data.general_position : '#—';
        f.totalFin.textContent   = data.total_finishers || '—';
        f.cpTime.textContent     = data.last_checkpoint_time || '——:——';
        f.cpName.textContent     = data.last_checkpoint_name || '—';

        const type = data.last_checkpoint_type || 'not_started';

        if (type === 'not_started') {
            f.elapsed.textContent    = '—:——:——';
            f.posGeneral.textContent = '#—';
            f.cpTime.textContent     = '——:——';
            f.cpName.textContent     = 'Belum melewati checkpoint';
        }

        f.statusPill.className    = 'status-pill status-' + type;
        f.statusPill.textContent  =
            type === 'finish'     ? 'Finish' :
            type === 'checkpoint' ? 'On Course' :
            type === 'start'      ? 'Sudah Start' : 'Belum Start';

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

    async function refreshStats() {
        try {
            const res  = await fetch(STATS_URL, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (data.success) {
                s.finish.textContent   = data.finished  ?? '—';
                s.oncourse.textContent = data.on_course ?? '—';
                s.total.textContent    = data.total     ?? '—';
            }
        } catch (e) { /* silent */ }
    }
    refreshStats();
    setInterval(refreshStats, STATS_INTERVAL);

    // RFID capture
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

    window.testScan = (tag) => lookup(tag);
})();
</script>
</body>
</html>
