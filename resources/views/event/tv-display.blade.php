{{-- resources/views/event/tv-display.blade.php --}}
{{-- Route: GET /event/{event}/tv --}}
{{-- Controller: EventController@tvDisplay --}}
{{--
    Requires:
    - $event (Event model, dengan relasi categories)
    - Background image bisa di-set via ?bg=url atau default dari $event->tv_background_url
    - RFID scan lookup via AJAX ke route('event.tv.lookup', $event) + ?tag=EPC_HEX
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->name }} — Live TV Display</title>

    {{-- Google Fonts: Bebas Neue (display) + DM Sans (body) --}}
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
            --glass-bg:   rgba(8, 8, 8, 0.85);
            --glass-border: rgba(255,255,255,0.12);
            --glass-border-accent: rgba(192, 41, 42, 0.6);
        }

        html, body {
            width: 100%; height: 100%;
            overflow: hidden;
            background: #0a0a0a;
            font-family: 'DM Sans', sans-serif;
        }

        /* ── BACKGROUND ── */
        #bg {
            position: fixed; inset: 0; z-index: 0;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-image: url('{{ $event->tv_background_url ?? asset("images/tv-bg-default.jpg") }}');
        }
        #bg::after {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(ellipse at center, rgba(0,0,0,0.35) 0%, rgba(0,0,0,0.65) 100%);
        }

        /* ── CLOCK BAR (top) ── */
        #clock-bar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 10;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 56px;
            height: 72px;
            background: rgba(0,0,0,0.55);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        #clock-bar .event-name {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 32px;
            letter-spacing: 2px;
            color: var(--off-white);
        }
        #clock-bar .event-name span {
            color: var(--red-light);
        }
        #clock-bar .live-badge {
            display: flex; align-items: center; gap: 10px;
            font-size: 13px; font-weight: 500; letter-spacing: 1.5px;
            color: var(--off-white); text-transform: uppercase;
        }
        #clock-bar .live-dot {
            width: 10px; height: 10px; border-radius: 50%;
            background: var(--red-light);
            animation: pulse-red 1.4s ease-in-out infinite;
        }
        #clock-bar .clock {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 34px; letter-spacing: 3px;
            color: var(--off-white);
        }

        /* ── CENTER STAGE ── */
        #center-stage {
            position: fixed; inset: 0; z-index: 5;
            display: flex; align-items: center; justify-content: center;
            padding: 88px 24px 64px;
        }

        /* ── CARD ── */
        #card {
            width: min(1040px, 94vw);
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-top: 3px solid var(--glass-border-accent);
            border-radius: 24px;
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(0,0,0,0.5);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1),
                        opacity 0.25s ease;
        }

        /* ── CARD: IDLE STATE ── */
        #state-idle {
            padding: 80px 56px;
            text-align: center;
            display: flex; flex-direction: column; align-items: center; gap: 28px;
        }
        .scan-icon {
            width: 96px; height: 96px;
            border: 2px solid rgba(255,255,255,0.15);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            position: relative;
        }
        .scan-icon::before {
            content: '';
            position: absolute; inset: 8px;
            border: 1.5px dashed rgba(192,41,42,0.5);
            border-radius: 50%;
            animation: spin 6s linear infinite;
        }
        .scan-icon svg { width: 44px; height: 44px; color: rgba(255,255,255,0.4); }
        #state-idle .prompt-main {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 52px; letter-spacing: 2px;
            color: var(--off-white);
        }
        #state-idle .prompt-sub {
            font-size: 18px; color: rgba(255,255,255,0.45);
            line-height: 1.5; max-width: 460px;
        }
        .scan-line-anim {
            width: 260px; height: 2px;
            background: linear-gradient(90deg, transparent, var(--red-light), transparent);
            border-radius: 1px;
            animation: scan-slide 2.5s ease-in-out infinite;
        }

        /* ── CARD: RESULT STATE ── */
        #state-result { display: none; }

        .result-header {
            padding: 44px 52px 36px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            display: flex; align-items: center; gap: 32px;
        }
        .bib-badge {
            flex-shrink: 0;
            width: 130px; height: 130px;
            background: var(--red);
            border-radius: 18px;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 2px;
            box-shadow: 0 10px 30px rgba(192,41,42,0.35);
        }
        .bib-badge .bib-label {
            font-size: 12px; font-weight: 500; letter-spacing: 2px;
            color: rgba(255,255,255,0.7); text-transform: uppercase;
        }
        .bib-badge .bib-num {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 64px; letter-spacing: 1px;
            color: var(--white);
            line-height: 1;
        }
        .result-name-block { flex: 1; min-width: 0; }
        .result-name {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 88px; letter-spacing: 1.5px;
            color: var(--white);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            line-height: 0.95;
            text-shadow: 0 2px 16px rgba(0,0,0,0.4);
        }
        /* Auto-shrink kalau nama panjang banget */
        .result-name.long  { font-size: 72px; }
        .result-name.xlong { font-size: 60px; }
        .result-meta {
            margin-top: 14px;
            font-size: 18px; color: rgba(255,255,255,0.6);
            display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
        }
        .result-meta .dot { opacity: 0.3; }
        .result-category-pill {
            display: inline-flex; align-items: center;
            padding: 5px 14px;
            background: rgba(192,41,42,0.25);
            border: 1px solid rgba(192,41,42,0.4);
            border-radius: 24px;
            font-size: 14px; font-weight: 500; letter-spacing: 0.5px;
            color: #F59191;
        }

        /* STATUS BADGE */
        .status-pill {
            flex-shrink: 0;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 16px; font-weight: 600; letter-spacing: 1px;
            text-transform: uppercase;
        }
        .status-finish { background: rgba(212,160,23,0.22); color: var(--gold-light); border: 1px solid rgba(212,160,23,0.4); }
        .status-checkpoint { background: rgba(55,138,221,0.22); color: #8DC4F5; border: 1px solid rgba(55,138,221,0.4); }
        .status-start { background: rgba(99,153,34,0.22); color: #AAD36A; border: 1px solid rgba(99,153,34,0.4); }
        .status-not_started {
            background: rgba(140,140,140,0.2);
            color: #BFBFBF;
            border: 1px solid rgba(140,140,140,0.35);
        }

        /* STATS GRID — elapsed time jadi hero */
        .result-stats {
            display: grid;
            grid-template-columns: 1.6fr 1fr 1fr;
            border-top: 1px solid rgba(255,255,255,0.06);
        }
        .stat-cell {
            padding: 32px 36px;
            border-right: 1px solid rgba(255,255,255,0.06);
            display: flex; flex-direction: column; gap: 8px;
        }
        .stat-cell:last-child { border-right: none; }
        .stat-label {
            font-size: 12px; font-weight: 500;
            letter-spacing: 2px; text-transform: uppercase;
            color: rgba(255,255,255,0.4);
        }
        .stat-value {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 48px; letter-spacing: 1.5px;
            color: var(--white); line-height: 1;
        }
        .stat-value.hero {
            font-size: 76px;
            color: var(--gold-light);
            text-shadow: 0 2px 14px rgba(212,160,23,0.25);
        }
        .stat-sub {
            font-size: 13px; color: rgba(255,255,255,0.4);
            margin-top: 2px;
        }

        /* FINISH CELEBRATION BAR */
        #finish-bar {
            display: none;
            padding: 22px 28px;
            background: linear-gradient(135deg, rgba(212,160,23,0.18), rgba(192,41,42,0.18));
            border-top: 1px solid rgba(212,160,23,0.25);
            text-align: center;
        }
        #finish-bar .finish-text {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 32px; letter-spacing: 4px;
            color: var(--gold-light);
        }

        /* ── CARD: ERROR STATE ── */
        #state-error {
            padding: 64px 52px;
            text-align: center;
            display: none; flex-direction: column; align-items: center; gap: 20px;
        }
        #state-error .error-icon {
            width: 64px; height: 64px;
            background: rgba(192,41,42,0.15);
            border: 1px solid rgba(192,41,42,0.3);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
        }
        #state-error .error-icon svg { width: 28px; height: 28px; color: var(--red-light); }
        #state-error .error-msg { font-size: 22px; color: rgba(255,255,255,0.6); }
        #state-error .error-tag { font-size: 14px; color: rgba(255,255,255,0.3); font-family: monospace; letter-spacing: 1px; }

        /* ── LOADING OVERLAY ── */
        #loading-overlay {
            position: absolute; inset: 0;
            background: rgba(0,0,0,0.4);
            border-radius: 24px;
            display: none; align-items: center; justify-content: center;
        }
        .spinner {
            width: 40px; height: 40px;
            border: 2px solid rgba(255,255,255,0.1);
            border-top-color: var(--red-light);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        /* ── RFID INPUT (hidden, captures scan) ── */
        #rfid-capture {
            position: fixed;
            bottom: -200px; left: 0;
            width: 1px; height: 1px;
            opacity: 0;
        }

        /* ── BOTTOM BAR ── */
        #bottom-bar {
            position: fixed; bottom: 0; left: 0; right: 0; z-index: 10;
            height: 56px;
            background: rgba(0,0,0,0.55);
            border-top: 1px solid rgba(255,255,255,0.07);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            display: flex; align-items: center; justify-content: center;
            gap: 48px;
        }
        .bottom-stat { text-align: center; }
        .bottom-stat .bs-val {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 24px; letter-spacing: 1px;
            color: var(--off-white);
        }
        .bottom-stat .bs-label {
            font-size: 11px; letter-spacing: 1px; text-transform: uppercase;
            color: rgba(255,255,255,0.4);
        }
        .bottom-divider {
            width: 1px; height: 28px;
            background: rgba(255,255,255,0.1);
        }

        /* ── KEYFRAMES ── */
        @keyframes pulse-red {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.85); }
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        @keyframes scan-slide {
            0% { transform: translateX(-100px); opacity: 0; }
            20% { opacity: 1; }
            80% { opacity: 1; }
            100% { transform: translateX(100px); opacity: 0; }
        }
        @keyframes card-pop {
            0% { transform: scale(0.96); opacity: 0; }
            60% { transform: scale(1.01); }
            100% { transform: scale(1); opacity: 1; }
        }
        .card-animate {
            animation: card-pop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }
    </style>
</head>
<body>

{{-- Background --}}
<div id="bg"></div>

{{-- Clock Bar --}}
<div id="clock-bar">
    <div class="event-name">
        {{ Str::before($event->name, ' ') }} <span>{{ Str::after($event->name, ' ') }}</span>
    </div>
    <div class="live-badge">
        <span class="live-dot"></span>
        Live Tracking
    </div>
    <div class="clock" id="clock">00:00:00</div>
</div>

{{-- Center Card --}}
<div id="center-stage">
    <div id="card" style="position: relative;">

        {{-- Loading overlay --}}
        <div id="loading-overlay">
            <div class="spinner"></div>
        </div>

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
            <div class="result-header">
                <div class="bib-badge">
                    <span class="bib-label">BIB</span>
                    <span class="bib-num" id="r-bib">—</span>
                </div>
                <div class="result-name-block">
                    <div class="result-name" id="r-name">—</div>
                    <div class="result-meta">
                        <span id="r-gender">—</span>
                        <span class="dot">·</span>
                        <span id="r-age">—</span> th
                        <span class="dot">·</span>
                        <span id="r-city">—</span>
                        <span class="dot">·</span>
                        <span class="result-category-pill" id="r-category">—</span>
                    </div>
                </div>
                <span class="status-pill" id="r-status-pill">—</span>
            </div>

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
                    <span class="stat-label">Checkpoint</span>
                    <span class="stat-value" id="r-checkpoint-time">——:——</span>
                    <span class="stat-sub" id="r-checkpoint-name">—</span>
                </div>
            </div>

            <div id="finish-bar">
                <span class="finish-text">Selamat — Finish!</span>
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

    </div>{{-- #card --}}
</div>{{-- #center-stage --}}

{{-- Bottom Stats Bar --}}
<div id="bottom-bar">
    <div class="bottom-stat">
        <div class="bs-val" id="stat-finish">—</div>
        <div class="bs-label">Finish</div>
    </div>
    <div class="bottom-divider"></div>
    <div class="bottom-stat">
        <div class="bs-val" id="stat-oncourse">—</div>
        <div class="bs-label">On Course</div>
    </div>
    <div class="bottom-divider"></div>
    <div class="bottom-stat">
        <div class="bs-val" id="stat-started">—</div>
        <div class="bs-label">Sudah Start</div>
    </div>
    <div class="bottom-divider"></div>
    <div class="bottom-stat">
        <div class="bs-val" id="stat-total">{{ $totalParticipants ?? '—' }}</div>
        <div class="bs-label">Total Peserta</div>
    </div>
</div>

{{-- Hidden RFID capture input --}}
<input id="rfid-capture" type="text" autocomplete="off" style="position:absolute;opacity:0;" />

<script>
(function () {
    'use strict';

    const LOOKUP_URL    = '{{ route("event.tv.lookup", $event) }}';
    const STATS_URL     = '{{ route("event.tv.stats", $event) }}';
    const IDLE_TIMEOUT  = 12000;   // ms sampai kembali ke idle
    const STATS_INTERVAL = 20000;  // refresh stats bar
    const SCAN_IDLE_MS  = 80;      // jeda antar karakter > ini = scan dianggap selesai
    const SCAN_LOCKOUT  = 600;     // lockout setelah scan diproses (cegah double-trigger)

    // ── DOM refs ──────────────────────────────────────────────────────
    const $idle      = document.getElementById('state-idle');
    const $result    = document.getElementById('state-result');
    const $error     = document.getElementById('state-error');
    const $loading   = document.getElementById('loading-overlay');
    const $card      = document.getElementById('card');
    const $finishBar = document.getElementById('finish-bar');

    // Result fields
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

    // Stats bar
    const s = {
        finish:   document.getElementById('stat-finish'),
        oncourse: document.getElementById('stat-oncourse'),
        started:  document.getElementById('stat-started'),
        total:    document.getElementById('stat-total'),
    };

    // ── State ─────────────────────────────────────────────────────────
    let idleTimer  = null;
    let isLoading  = false;
    let scanLocked = false;

    // ── Clock ─────────────────────────────────────────────────────────
    function updateClock() {
        const now = new Date();
        document.getElementById('clock').textContent =
            String(now.getHours()).padStart(2,'0') + ':' +
            String(now.getMinutes()).padStart(2,'0') + ':' +
            String(now.getSeconds()).padStart(2,'0');
    }
    updateClock();
    setInterval(updateClock, 1000);

    // ── Auto-fit nama panjang ─────────────────────────────────────────
    function fitName(text) {
        f.name.classList.remove('long', 'xlong');
        f.name.textContent = text;
        const len = (text || '').length;
        if (len > 28)      f.name.classList.add('xlong');
        else if (len > 18) f.name.classList.add('long');
    }

    // ── Show states ───────────────────────────────────────────────────
    function showIdle() {
        $idle.style.display      = 'flex';
        $result.style.display    = 'none';
        $error.style.display     = 'none';
        $finishBar.style.display = 'none';
        $card.classList.remove('card-animate');
        void $card.offsetWidth;
        $card.classList.add('card-animate');
    }

    function showResult(data) {
        $idle.style.display   = 'none';
        $error.style.display  = 'none';
        $result.style.display = 'block';

        // Populate fields
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

        // Tentukan type DULU sebelum dipakai
        const type = data.last_checkpoint_type || 'not_started';

        // Override field kalau belum start
        if (type === 'not_started') {
            f.elapsed.textContent    = '—:——:——';
            f.posGeneral.textContent = '#—';
            f.cpTime.textContent     = '——:——';
            f.cpName.textContent     = 'Belum melewati checkpoint';
        }

        // Status pill
        f.statusPill.className = 'status-pill status-' + type;
        f.statusPill.textContent =
            type === 'finish'      ? 'Finish' :
            type === 'checkpoint'  ? 'On Course' :
            type === 'start'       ? 'Sudah Start' :
                                     'Belum Start';

        // Finish bar cuma muncul kalau benar-benar finish
        $finishBar.style.display = type === 'finish' ? 'block' : 'none';

        $card.classList.remove('card-animate');
        void $card.offsetWidth;
        $card.classList.add('card-animate');

        scheduleIdle();
    }

    function showError(msg, tag) {
        $idle.style.display      = 'none';
        $result.style.display    = 'none';
        $error.style.display     = 'flex';
        $finishBar.style.display = 'none';
        document.getElementById('error-msg').textContent = msg || 'Tag tidak dikenali';
        document.getElementById('error-tag').textContent = tag || '';

        $card.classList.remove('card-animate');
        void $card.offsetWidth;
        $card.classList.add('card-animate');

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

    // ── Lookup ────────────────────────────────────────────────────────
    async function lookup(tag) {
        if (isLoading) return;
        if (!tag || tag.length < 8) return;

        setLoading(true);
        try {
            const url = LOOKUP_URL + '?tag=' + encodeURIComponent(tag);
            const res = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            if (data.success && data.participant) {
                showResult(data.participant);
            } else {
                showError(data.message || 'Tag tidak terdaftar', tag);
            }
        } catch (e) {
            console.error('Lookup failed:', e);
            showError('Koneksi ke server gagal', tag);
        } finally {
            setLoading(false);
        }
    }

    // ── Stats refresh ─────────────────────────────────────────────────
    async function refreshStats() {
        try {
            const res  = await fetch(STATS_URL, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (data.success) {
                s.finish.textContent   = data.finished  ?? '—';
                s.oncourse.textContent = data.on_course ?? '—';
                s.started.textContent  = data.started   ?? '—';
                s.total.textContent    = data.total     ?? '—';
            }
        } catch (err) {
            console.error('Stats refresh failed:', err);
        }
    }
    refreshStats();
    setInterval(refreshStats, STATS_INTERVAL);

    // ── RFID Input capture ────────────────────────────────────────────
    // Strategi: debounce by idle gap.
    // RFID reader (HID keyboard wedge) ngirim karakter sangat cepat (< 20ms antar char).
    // Kalau ada jeda > SCAN_IDLE_MS, dianggap scan selesai → proses & clear buffer.
    // Setelah lookup, scanLocked aktif selama SCAN_LOCKOUT untuk cegah karakter sisa
    // (yg masih nyamber dari scanner) memicu lookup lagi.

    const rfidInput = document.getElementById('rfid-capture');

    // Fokus input terus-menerus
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

        // Buang karakter apapun yang masuk selama lockout
        setTimeout(() => {
            rfidInput.value = '';
            scanLocked = false;
        }, SCAN_LOCKOUT);
    }

    rfidInput.addEventListener('input', function () {
        if (scanLocked) {
            // Drop sisa karakter yang nyamber dari scan sebelumnya
            rfidInput.value = '';
            return;
        }
        clearTimeout(scanTimer);
        scanTimer = setTimeout(processBuffer, SCAN_IDLE_MS);
    });

    rfidInput.addEventListener('keydown', function (e) {
        // Beberapa scanner kirim Enter sebagai terminator → langsung proses
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(scanTimer);
            processBuffer();
        }
    });

    // ── Init ──────────────────────────────────────────────────────────
    showIdle();

    // Helper buat testing manual via DevTools
    window.testScan = (tag) => lookup(tag);

})();
</script>
</body>
</html>
