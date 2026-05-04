@extends('layouts.app')

@section('content')

{{-- PIN Protection Overlay --}}
<div id="pinOverlay"
     style="position:fixed;inset:0;background:rgba(0,0,0,0.55);display:flex;align-items:center;justify-content:center;z-index:9999;">
    <div style="background:#fff;border-radius:12px;border:1px solid #e5e7eb;padding:2rem 2.5rem;width:320px;text-align:center;box-shadow:0 4px 24px rgba(0,0,0,0.12);">
        <div style="font-size:28px;margin-bottom:8px;">🔒</div>
        <h2 style="font-size:18px;font-weight:600;margin:0 0 6px;color:#111827;">Halaman Terkunci</h2>
        <p style="font-size:13px;color:#6b7280;margin:0 0 16px;">Masukkan PIN untuk melanjutkan</p>

        <div id="pinDots" style="display:flex;justify-content:center;gap:10px;margin-bottom:16px;">
            <div class="pdot" id="d0"></div>
            <div class="pdot" id="d1"></div>
            <div class="pdot" id="d2"></div>
            <div class="pdot" id="d3"></div>
        </div>

        <input id="pinInput" type="password" maxlength="4" placeholder="••••" autocomplete="off"
               style="width:100%;padding:10px;font-size:20px;letter-spacing:6px;text-align:center;
                      border:1px solid #d1d5db;border-radius:8px;outline:none;margin-bottom:10px;
                      box-sizing:border-box;">
        <button id="pinSubmit"
                style="width:100%;padding:10px;font-size:14px;font-weight:600;color:#fff;
                       background:#991b1b;border:none;border-radius:8px;cursor:pointer;">
            Buka Halaman
        </button>
        <div id="pinErr" style="font-size:13px;color:#dc2626;margin-top:8px;min-height:18px;"></div>
    </div>
</div>

<style>
    .pdot { width:12px;height:12px;border-radius:50%;background:#e5e7eb;transition:background .2s; }
    .pdot.filled { background:#991b1b; }
    .pdot.shake  { background:#dc2626; }
</style>

<script>
(function () {
    const PIN    = 'uket';
    const input  = document.getElementById('pinInput');
    const btn    = document.getElementById('pinSubmit');
    const errEl  = document.getElementById('pinErr');
    const overlay= document.getElementById('pinOverlay');
    const dots   = [0,1,2,3].map(i => document.getElementById('d'+i));

    function updateDots(val, error) {
        dots.forEach((d, i) => {
            d.className = 'pdot';
            if (error)          d.classList.add('shake');
            else if (i<val.length) d.classList.add('filled');
        });
    }

    input.addEventListener('input', () => { errEl.textContent=''; updateDots(input.value,false); });
    input.addEventListener('keydown', e => { if(e.key==='Enter') check(); });
    btn.addEventListener('click', check);

    function check() {
        if (input.value.trim().toLowerCase() === PIN) {
            overlay.style.transition = 'opacity .3s';
            overlay.style.opacity    = '0';
            setTimeout(() => overlay.remove(), 300);
        } else {
            errEl.textContent = 'PIN salah. Coba lagi.';
            updateDots(input.value, true);
            input.value = '';
            setTimeout(() => updateDots('', false), 800);
            input.focus();
        }
    }

    input.focus();
})();
</script>

<div class="min-h-screen py-14 bg-gray-50">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">

        {{-- Back --}}
        <a href="{{ route('home') }}#about"
           class="inline-flex items-center gap-2 mb-10 text-sm font-medium text-gray-600 hover:text-red-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke halaman event
        </a>

        {{-- Header --}}
        <div class="mb-12 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 mb-4 text-xs font-semibold tracking-wider text-red-800 uppercase bg-red-100 rounded-full">
                <span class="relative flex w-2 h-2">
                    <span class="absolute inline-flex w-full h-full bg-red-500 rounded-full opacity-75 animate-ping"></span>
                    <span class="relative inline-flex w-2 h-2 bg-red-700 rounded-full"></span>
                </span>
                Live Tracking
            </div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 md:text-4xl">
                Live Race Tracking
            </h1>
            <p class="mt-3 text-base text-gray-600">{{ $event->name }}</p>
            <p class="mt-2 text-sm text-gray-500">
                Total peserta terdaftar: {{ $totalParticipants }}
            </p>
        </div>

        {{-- Summary Stats — data-stat attributes diupdate via JS --}}
        <div id="live-summary" class="grid grid-cols-2 gap-4 mb-10 md:grid-cols-4">
            <div class="p-5 bg-white border border-gray-200 shadow-sm rounded-2xl">
                <div class="text-xs font-semibold tracking-wide text-gray-500 uppercase">Belum Start</div>
                <div data-stat="not_started" class="mt-1 text-2xl font-bold text-gray-900">{{ $summary['not_started'] }}</div>
            </div>
            <div class="p-5 bg-white border border-gray-200 shadow-sm rounded-2xl">
                <div class="text-xs font-semibold tracking-wide text-gray-500 uppercase">Sedang Berlari</div>
                <div data-stat="on_course" class="mt-1 text-2xl font-bold text-red-700">{{ $summary['on_course'] }}</div>
            </div>
            <div class="p-5 bg-white border border-gray-200 shadow-sm rounded-2xl">
                <div class="text-xs font-semibold tracking-wide text-red-800 uppercase">Finish</div>
                <div data-stat="finished" class="mt-1 text-2xl font-bold text-red-800">{{ $summary['finished'] }}</div>
            </div>
            <div class="p-5 bg-white border border-gray-200 shadow-sm rounded-2xl">
                <div class="text-xs font-semibold tracking-wide text-gray-500 uppercase">Total Start</div>
                <div data-stat="started" class="mt-1 text-2xl font-bold text-gray-900">{{ $summary['started'] }}</div>
            </div>
        </div>

        {{-- Category Filter Pills --}}
        @if($categories->count() > 1)
        <div class="flex flex-wrap gap-2 mb-6">
            <a href="{{ route('event.live', array_filter(['event' => $event->slug, 'gender' => request('gender'), 'q' => request('q')])) }}"
               class="px-4 py-2 text-sm font-medium rounded-lg transition
                   {{ !$selectedCategory ? 'bg-red-800 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
                Semua Kategori
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('event.live', array_filter(['event' => $event->slug, 'category' => $cat->slug, 'gender' => request('gender'), 'q' => request('q')])) }}"
                   class="px-4 py-2 text-sm font-medium rounded-lg transition
                       {{ $selectedCategory == $cat->slug ? 'bg-red-800 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>
        @endif

        {{-- Search & Gender Filter --}}
        <div class="mb-10 overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl">
            <form method="GET" class="p-6 space-y-4">
                @if($selectedCategory)
                    <input type="hidden" name="category" value="{{ $selectedCategory }}">
                @endif
                <div class="flex flex-col gap-4 md:flex-row">
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Cari BIB atau nama peserta..."
                        class="flex-1 px-4 py-3 text-sm border border-gray-300 rounded-lg focus:border-red-800 focus:ring-2 focus:ring-red-800/10"
                    >
                    <select name="gender"
                            class="px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-800/10 focus:border-red-800 md:w-40">
                        <option value="">Semua Gender</option>
                        <option value="M" {{ request('gender') == 'M' ? 'selected' : '' }}>Pria</option>
                        <option value="F" {{ request('gender') == 'F' ? 'selected' : '' }}>Wanita</option>
                    </select>
                    <button type="submit"
                            class="px-6 py-3 text-sm font-semibold text-white bg-red-800 rounded-lg hover:bg-red-700">
                        Cari
                    </button>
                    @if($activeFilters > 0)
                        <a href="{{ route('event.live', array_filter(['event' => $event->slug, 'category' => $selectedCategory])) }}"
                           class="px-6 py-3 text-sm font-medium text-center text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Active Filter Tags --}}
        @if($activeFilters > 0)
        <div class="flex flex-wrap items-center gap-2 mb-6">
            <span class="text-xs font-semibold text-gray-500">Filter aktif:</span>
            @if($selectedCategory)
                <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-red-800 bg-red-100 rounded-full">
                    Kategori: {{ $categories->firstWhere('slug', $selectedCategory)?->name ?? $selectedCategory }}
                    <a href="{{ route('event.live', array_filter(['event' => $event->slug, 'gender' => request('gender'), 'q' => request('q')])) }}" class="ml-1 hover:text-red-600">&times;</a>
                </span>
            @endif
            @if(request('gender'))
                <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-red-800 bg-red-100 rounded-full">
                    Gender: {{ request('gender') == 'M' ? 'Pria' : 'Wanita' }}
                    <a href="{{ route('event.live', array_filter(['event' => $event->slug, 'category' => $selectedCategory, 'q' => request('q')])) }}" class="ml-1 hover:text-red-600">&times;</a>
                </span>
            @endif
            @if(request('q'))
                <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium text-red-800 bg-red-100 rounded-full">
                    Cari: "{{ request('q') }}"
                    <a href="{{ route('event.live', array_filter(['event' => $event->slug, 'category' => $selectedCategory, 'gender' => request('gender')])) }}" class="ml-1 hover:text-red-600">&times;</a>
                </span>
            @endif
        </div>
        @endif

        {{-- Live Indicator & Manual Refresh --}}
        <div class="flex items-center justify-between p-4 mb-8 bg-white border border-gray-200 shadow-sm rounded-xl">
            <div class="flex items-center gap-3 text-sm text-gray-600">
                <span id="live-indicator" class="relative flex w-2 h-2">
                    <span class="absolute inline-flex w-full h-full bg-red-500 rounded-full opacity-75 animate-ping"></span>
                    <span class="relative inline-flex w-2 h-2 bg-red-700 rounded-full"></span>
                </span>
                Live update · Terakhir: <span id="updated-at">{{ now()->format('H:i:s') }}</span>
            </div>
            <button type="button" id="manual-refresh"
                    class="px-4 py-2 text-xs font-semibold text-red-800 border border-red-300 rounded-lg hover:bg-red-50">
                Refresh Sekarang
            </button>
        </div>

        {{-- Dynamic content container — diisi ulang oleh polling JS --}}
        <div id="live-content">
            @include('event.live-content')
        </div>

    </div>
</div>

{{-- ============================================================ --}}
{{-- AJAX Polling Script                                          --}}
{{-- ============================================================ --}}
<script>
(function () {
    const liveDataUrl = @json(route('event.live.partial', $event));
    const filters = {
        category: @json($selectedCategory ?? ''),
        gender:   @json(request('gender') ?? ''),
        q:        @json(request('q') ?? ''),
    };

    const POLL_INTERVAL     = 10000; // 10 s
    const JITTER_MAX        = 2000;  // ±2 s jitter to spread server load
    const FAILURE_THRESHOLD = 3;

    let isUpdating   = false;
    let failureCount = 0;
    let pollTimer    = null;

    // ── Core fetch ───────────────────────────────────────────
    async function refreshLiveData(showLoading = false) {
        if (isUpdating)       return;
        if (document.hidden)  return;

        isUpdating = true;
        const indicator = document.getElementById('live-indicator');
        if (showLoading && indicator) indicator.style.opacity = '0.3';

        try {
            const params = new URLSearchParams(
                Object.entries(filters).filter(([, v]) => v && v.length > 0)
            );
            const url = params.toString() ? `${liveDataUrl}?${params}` : liveDataUrl;

            const res = await fetch(url, {
                headers: {
                    'Accept':            'application/json',
                    'X-Requested-With':  'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();

            // Update summary cards
            updateSummary(data.summary);

            // Swap HTML content — preserve scroll position
            const scrollY = window.scrollY;
            document.getElementById('live-content').innerHTML = data.html;
            window.scrollTo(0, scrollY);

            // Update timestamp
            document.getElementById('updated-at').textContent = data.updatedAt;

            failureCount = 0;
            clearConnectionWarning();
        } catch (err) {
            failureCount++;
            console.warn('Live update failed:', err);
            if (failureCount >= FAILURE_THRESHOLD) showConnectionWarning();
        } finally {
            isUpdating = false;
            if (indicator) indicator.style.opacity = '1';
        }
    }

    // ── Summary flash update ─────────────────────────────────
    function updateSummary(summary) {
        const el = document.getElementById('live-summary');
        if (!el) return;
        Object.entries(summary).forEach(([key, value]) => {
            const target = el.querySelector(`[data-stat="${key}"]`);
            if (target && target.textContent != value) {
                target.textContent = value;
                target.style.transition       = 'background-color 0.5s';
                target.style.backgroundColor  = '#fef2f2';
                setTimeout(() => (target.style.backgroundColor = ''), 600);
            }
        });
    }

    // ── Connection warning banner ────────────────────────────
    function showConnectionWarning() {
        if (document.getElementById('connection-warning')) return;
        const warn       = document.createElement('div');
        warn.id          = 'connection-warning';
        warn.className   = 'fixed bottom-4 right-4 px-4 py-3 text-sm text-yellow-900 bg-yellow-100 border border-yellow-300 rounded-lg shadow-lg z-50';
        warn.textContent = '⚠️ Koneksi tidak stabil — data mungkin tidak ter-update';
        document.body.appendChild(warn);
    }

    function clearConnectionWarning() {
        document.getElementById('connection-warning')?.remove();
    }

    // ── Polling scheduler with jitter ───────────────────────
    function scheduleNextPoll() {
        const jitter = Math.random() * JITTER_MAX;
        pollTimer = setTimeout(async () => {
            await refreshLiveData();
            scheduleNextPoll();
        }, POLL_INTERVAL + jitter);
    }

    // ── Event listeners ──────────────────────────────────────
    document.getElementById('manual-refresh')?.addEventListener('click', () => {
        refreshLiveData(true);
    });

    // Refresh immediately when tab becomes visible again
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) refreshLiveData();
    });

    // Cleanup on navigate away
    window.addEventListener('beforeunload', () => {
        if (pollTimer) clearTimeout(pollTimer);
    });

    // Kick off polling
    scheduleNextPoll();
})();
</script>

@endsection
